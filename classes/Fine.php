<?php
// classes/Fine.php
require_once __DIR__ . '/../config/db.php';

class Fine {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    // ─── CRUD ────────────────────────────────────────────────

    public function getFineById(int $fineId): ?array {
        $stmt = $this->db->prepare("
            SELECT f.*, u.full_name AS driver_name, u.email AS driver_email,
                   s.title AS spot_title, s.address AS spot_address
            FROM fines f
            JOIN users u ON f.driver_id = u.user_id
            JOIN parking_spots s ON f.spot_id = s.spot_id
            WHERE f.fine_id = ?
        ");
        $stmt->execute([$fineId]);
        return $stmt->fetch() ?: null;
    }

    public function listDriverFines(int $driverId, string $status = ''): array {
        $sql    = "SELECT f.*, s.title AS spot_title FROM fines f JOIN parking_spots s ON f.spot_id = s.spot_id WHERE f.driver_id = ?";
        $params = [$driverId];
        if ($status) { $sql .= " AND f.status = ?"; $params[] = $status; }
        $sql .= " ORDER BY f.issued_at DESC";
        $stmt   = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function listAllFines(string $status = '', int $limit = 100): array {
        $sql    = "SELECT f.*, u.full_name AS driver_name, s.title AS spot_title FROM fines f JOIN users u ON f.driver_id = u.user_id JOIN parking_spots s ON f.spot_id = s.spot_id WHERE 1=1";
        $params = [];
        if ($status) { $sql .= " AND f.status = ?"; $params[] = $status; }
        $sql .= " ORDER BY f.issued_at DESC LIMIT ?";
        $params[] = $limit;
        $stmt   = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function markFinePaid(int $fineId, int $driverId): bool {
        $stmt = $this->db->prepare("UPDATE fines SET status = 'paid', paid_at = NOW() WHERE fine_id = ? AND driver_id = ?");
        $result = $stmt->execute([$fineId, $driverId]);
        if ($result) {
            $stmt = $this->db->prepare("UPDATE users SET unpaid_fines_count = GREATEST(0, unpaid_fines_count - 1) WHERE user_id = ?");
            $stmt->execute([$driverId]);
        }
        return $result;
    }

    // ─── NON-CRUD: Automated Fine Generation ─────────────────

    /**
     * Issues digital fine when a vehicle is detected without active reservation.
     * Called by sensor/admin trigger.
     */
    public function generateAutomatedFine(int $driverId, int $spotId, ?int $officerId = null): array {
        // Check if driver already has an active reservation for this spot
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM reservations
            WHERE driver_id = ? AND spot_id = ? AND status = 'active'
        ");
        $stmt->execute([$driverId, $spotId]);
        if ((int)$stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Driver has an active reservation — no fine issued.'];
        }

        // Get spot base fine amount (2x hourly rate)
        $stmt = $this->db->prepare("SELECT price_per_hour FROM parking_spots WHERE spot_id = ?");
        $stmt->execute([$spotId]);
        $hourly = (float)$stmt->fetchColumn();
        $fineAmount = round($hourly * 2, 2);

        $stmt = $this->db->prepare("
            INSERT INTO fines (driver_id, spot_id, fine_type, amount, officer_id)
            VALUES (?, ?, 'no_reservation', ?, ?)
        ");
        $stmt->execute([$driverId, $spotId, $fineAmount, $officerId]);
        $fineId = (int)$this->db->lastInsertId();

        // Update driver unpaid count
        $stmt = $this->db->prepare("UPDATE users SET unpaid_fines_count = unpaid_fines_count + 1 WHERE user_id = ?");
        $stmt->execute([$driverId]);

        // Check blacklist threshold
        require_once __DIR__ . '/User.php';
        $user = new User();
        $blacklisted = $user->checkAndApplyBlacklist($driverId);

        return [
            'success'     => true,
            'fine_id'     => $fineId,
            'amount'      => $fineAmount,
            'blacklisted' => $blacklisted,
            'message'     => "Fine of {$fineAmount} EGP issued.",
        ];
    }

    // ─── NON-CRUD: Evidence-Based Appeal Workflow ─────────────

    public function submitAppeal(int $fineId, int $driverId, string $description, ?string $evidencePath = null): array {
        // Check fine belongs to driver and is unpaid
        $stmt = $this->db->prepare("SELECT status FROM fines WHERE fine_id = ? AND driver_id = ?");
        $stmt->execute([$fineId, $driverId]);
        $fine = $stmt->fetch();

        if (!$fine) return ['success' => false, 'message' => 'Fine not found.'];
        if ($fine['status'] === 'paid') return ['success' => false, 'message' => 'Cannot appeal a paid fine.'];
        if ($fine['status'] === 'appealed') return ['success' => false, 'message' => 'Appeal already submitted.'];

        $stmt = $this->db->prepare("
            INSERT INTO fine_appeals (fine_id, driver_id, description, evidence_path)
            VALUES (?,?,?,?)
        ");
        $stmt->execute([$fineId, $driverId, $description, $evidencePath]);
        $appealId = $this->db->lastInsertId();

        // Update fine status to appealed
        $stmt = $this->db->prepare("UPDATE fines SET status = 'appealed' WHERE fine_id = ?");
        $stmt->execute([$fineId]);

        return ['success' => true, 'appeal_id' => $appealId, 'message' => 'Appeal submitted successfully.'];
    }

    public function reviewAppeal(int $appealId, int $adminId, string $decision, string $response): array {
        $stmt = $this->db->prepare("SELECT * FROM fine_appeals WHERE appeal_id = ?");
        $stmt->execute([$appealId]);
        $appeal = $stmt->fetch();
        if (!$appeal) return ['success' => false, 'message' => 'Appeal not found.'];

        $stmt = $this->db->prepare("
            UPDATE fine_appeals 
            SET status = ?, admin_response = ?, reviewed_by = ?, reviewed_at = NOW()
            WHERE appeal_id = ?
        ");
        $stmt->execute([$decision, $response, $adminId, $appealId]);

        if ($decision === 'approved') {
            $stmt = $this->db->prepare("UPDATE fines SET status = 'waived' WHERE fine_id = ?");
            $stmt->execute([$appeal['fine_id']]);
            $stmt = $this->db->prepare("UPDATE users SET unpaid_fines_count = GREATEST(0, unpaid_fines_count - 1) WHERE user_id = ?");
            $stmt->execute([$appeal['driver_id']]);
        } else {
            $stmt = $this->db->prepare("UPDATE fines SET status = 'unpaid' WHERE fine_id = ?");
            $stmt->execute([$appeal['fine_id']]);
        }
        return ['success' => true, 'message' => "Appeal {$decision}."];
    }

    // ─── NON-CRUD: Emergency Vehicle Override ─────────────────

    /**
     * Instantly cancels all non-essential reservations for a spot on emergency.
     */
    public function emergencyOverride(int $spotId, int $adminId, string $reason): array {
        // Get all active/confirmed reservations
        $stmt = $this->db->prepare("
            SELECT reservation_id, driver_id, total_amount 
            FROM reservations 
            WHERE spot_id = ? AND status IN ('confirmed','active')
        ");
        $stmt->execute([$spotId]);
        $reservations = $stmt->fetchAll();

        $cancelledCount = 0;
        foreach ($reservations as $res) {
            $stmt = $this->db->prepare("UPDATE reservations SET status = 'cancelled', cancellation_reason = ? WHERE reservation_id = ?");
            $stmt->execute(["Emergency override by admin: $reason", $res['reservation_id']]);
            // Full refund for emergency cancellations
            $stmt = $this->db->prepare("UPDATE transactions SET payment_status = 'refunded' WHERE reservation_id = ?");
            $stmt->execute([$res['reservation_id']]);
            $cancelledCount++;
        }

        // Set spot to unavailable
        $stmt = $this->db->prepare("UPDATE parking_spots SET status = 'unavailable' WHERE spot_id = ?");
        $stmt->execute([$spotId]);

        // Audit log
        $stmt = $this->db->prepare("INSERT INTO audit_log (user_id, action, target_table, target_id, new_value) VALUES (?,?,?,?,?)");
        $stmt->execute([$adminId, 'EMERGENCY_OVERRIDE', 'parking_spots', $spotId, $reason]);

        return [
            'success'         => true,
            'cancelled_count' => $cancelledCount,
            'message'         => "Emergency override applied. {$cancelledCount} reservation(s) cancelled with full refund.",
        ];
    }

    // ─── NON-CRUD: Event-Zone Locking ────────────────────────

    public function createEventZone(array $data, int $adminId): array {
        $stmt = $this->db->prepare("
            INSERT INTO event_zones (admin_id, zone_name, center_lat, center_lng, radius_km, reason, active_from, active_until)
            VALUES (?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            $adminId, $data['zone_name'], $data['lat'], $data['lng'],
            $data['radius_km'], $data['reason'], $data['from'], $data['until']
        ]);
        $zoneId = $this->db->lastInsertId();

        // Disable all spots within radius (simple bounding box simulation)
        $stmt = $this->db->prepare("
            UPDATE parking_spots 
            SET status = 'unavailable'
            WHERE ABS(latitude - ?) < ? AND ABS(longitude - ?) < ?
        ");
        $approxDeg = $data['radius_km'] / 111;
        $stmt->execute([$data['lat'], $approxDeg, $data['lng'], $approxDeg]);

        return ['success' => true, 'zone_id' => $zoneId];
    }

    // ─── NON-CRUD: Audit Trail ────────────────────────────────

    public function getAuditLog(int $limit = 100, ?string $action = null): array {
        $sql    = "SELECT al.*, u.full_name FROM audit_log al LEFT JOIN users u ON al.user_id = u.user_id WHERE 1=1";
        $params = [];
        if ($action) { $sql .= " AND al.action = ?"; $params[] = $action; }
        $sql .= " ORDER BY al.logged_at DESC LIMIT ?";
        $params[] = $limit;
        $stmt   = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
