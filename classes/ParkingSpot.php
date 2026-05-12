<?php
// classes/ParkingSpot.php
require_once __DIR__ . '/../config/db.php';

class ParkingSpot {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    // ─── CRUD ────────────────────────────────────────────────

    public function createSpot(array $data): array {
        $stmt = $this->db->prepare("
            INSERT INTO parking_spots 
            (owner_id, garage_id, spot_number, title, description, address, spot_type, price_per_hour, base_price, has_ev_charger, city_zone)
            VALUES (?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            $data['owner_id'],
            $data['garage_id'] ?? null,
            $data['spot_number'] ?? null,
            $data['title'], $data['description'] ?? '',
            $data['address'],
            $data['spot_type'] ?? 'driveway',
            $data['price_per_hour'], $data['price_per_hour'],
            $data['has_ev_charger'] ?? 0, $data['city_zone'] ?? null
        ]);
        return ['success' => true, 'spot_id' => $this->db->lastInsertId()];
    }

    public function getSpotById(int $spotId): ?array {
        $stmt = $this->db->prepare("
            SELECT s.*, u.full_name AS owner_name, u.phone AS owner_phone,
                   g.name AS garage_name, g.garage_id,
                   s.trust_score, s.total_reviews, s.difficulty_score
            FROM parking_spots s
            JOIN users u ON s.owner_id = u.user_id
            LEFT JOIN garages g ON s.garage_id = g.garage_id
            WHERE s.spot_id = ?
        ");
        $stmt->execute([$spotId]);
        return $stmt->fetch() ?: null;
    }

    public function updateSpot(int $spotId, array $data, int $ownerId): bool {
        $spot = $this->getSpotById($spotId);
        if (!$spot || $spot['owner_id'] != $ownerId) return false;

        $allowed = ['title','description','address','price_per_hour','has_ev_charger','city_zone','garage_id','spot_number'];
        $fields = []; $values = [];
        foreach ($allowed as $f) {
            if (isset($data[$f])) { $fields[] = "$f = ?"; $values[] = $data[$f]; }
        }
        if (empty($fields)) return false;
        $values[] = $spotId;
        $stmt = $this->db->prepare("UPDATE parking_spots SET " . implode(', ', $fields) . " WHERE spot_id = ?");
        return $stmt->execute($values);
    }

    public function deleteSpot(int $spotId, int $ownerId): array {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM reservations WHERE spot_id = ? AND status IN ('pending','confirmed','active')");
        $stmt->execute([$spotId]);
        if ((int)$stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Cannot delete spot with active reservations.'];
        }
        $stmt = $this->db->prepare("DELETE FROM parking_spots WHERE spot_id = ? AND owner_id = ?");
        $stmt->execute([$spotId, $ownerId]);
        return ['success' => true];
    }

    public function listOwnerSpots(int $ownerId): array {
        $stmt = $this->db->prepare("SELECT * FROM parking_spots WHERE owner_id = ? ORDER BY created_at DESC");
        $stmt->execute([$ownerId]);
        return $stmt->fetchAll();
    }

    // ─── NON-CRUD: Search & Filter Engine ────────────────────

    public function searchSpots(array $filters): array {
        $bufferMinutes = 10;
        $searchStart   = $filters['start_time'] ?? date('Y-m-d H:i:s');
        $searchEnd     = $filters['end_time']   ?? date('Y-m-d H:i:s', strtotime('+1 hour'));

        $sql = "
            SELECT s.*, u.full_name AS owner_name,
                   ROUND(s.trust_score, 1) AS trust_score,
                   g.name AS garage_name,
                   s.spot_number,
                   CASE
                     WHEN EXISTS (
                       SELECT 1 FROM reservations r
                       WHERE r.spot_id = s.spot_id
                         AND r.status IN ('confirmed','active','pending')
                         AND r.start_time < DATE_ADD(?, INTERVAL ? MINUTE)
                         AND DATE_ADD(r.end_time, INTERVAL ? MINUTE) > ?
                     ) THEN 'occupied'
                     ELSE 'free'
                   END AS real_time_status
            FROM parking_spots s
            JOIN users u ON s.owner_id = u.user_id
            LEFT JOIN garages g ON s.garage_id = g.garage_id
            WHERE s.status = 'available'
              AND s.is_verified = 1
        ";
        $params = [$searchEnd, $bufferMinutes, $bufferMinutes, $searchStart];

        if (!empty($filters['needs_ev'])) {
            $sql .= " AND s.has_ev_charger = 1";
        }
        if (!empty($filters['spot_type'])) {
            $sql .= " AND s.spot_type = ?";
            $params[] = $filters['spot_type'];
        }
        if (!empty($filters['max_price'])) {
            $sql .= " AND s.price_per_hour <= ?";
            $params[] = $filters['max_price'];
        }
        if (!empty($filters['zone'])) {
            $sql .= " AND s.city_zone LIKE ?";
            $params[] = '%' . $filters['zone'] . '%';
        }
        $sql .= " ORDER BY s.trust_score DESC, s.price_per_hour ASC LIMIT 50";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function isVisibleInSearch(int $spotId): bool {
        $stmt = $this->db->prepare("SELECT status FROM parking_spots WHERE spot_id = ?");
        $stmt->execute([$spotId]);
        $status = $stmt->fetchColumn();
        return !in_array($status, ['maintenance', 'owner_use', 'pending_verification', 'unavailable']);
    }

    public function setSpotStatus(int $spotId, string $status, int $ownerId): array {
        $allowed = ['available','unavailable','maintenance','owner_use'];
        if (!in_array($status, $allowed)) {
            return ['success' => false, 'message' => 'Invalid status.'];
        }
        if (in_array($status, ['maintenance', 'owner_use'])) {
            $result = $this->checkOverlapWithExistingReservations($spotId, date('Y-m-d H:i:s'), date('Y-m-d H:i:s', strtotime('+30 days')));
            if ($result) {
                return ['success' => false, 'message' => 'Cannot change status: spot has upcoming reservations.'];
            }
        }
        $stmt = $this->db->prepare("UPDATE parking_spots SET status = ? WHERE spot_id = ? AND owner_id = ?");
        $stmt->execute([$status, $spotId, $ownerId]);
        return ['success' => true];
    }

    // ─── NON-CRUD: Trust Score Calculator ────────────────────

    public function recalculateTrustScore(int $spotId): float {
        $stmt = $this->db->prepare("
            SELECT rating,
                   DATEDIFF(NOW(), created_at) AS days_old
            FROM reviews
            WHERE spot_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$spotId]);
        $reviews = $stmt->fetchAll();

        if (empty($reviews)) return 0.0;

        $weightedSum = 0;
        $totalWeight = 0;
        foreach ($reviews as $r) {
            $weight = 1 / (1 + ($r['days_old'] / 30));
            $weightedSum += $r['rating'] * $weight;
            $totalWeight += $weight;
        }
        $score = $totalWeight > 0 ? round($weightedSum / $totalWeight, 2) : 0;

        $stmt = $this->db->prepare("UPDATE parking_spots SET trust_score = ?, total_reviews = ? WHERE spot_id = ?");
        $stmt->execute([$score, count($reviews), $spotId]);
        return $score;
    }

    // ─── NON-CRUD: Difficulty Score ───────────────────────────

    public function recalculateDifficultyScore(int $spotId): float {
        $stmt = $this->db->prepare("SELECT AVG(difficulty_rating) as avg_diff FROM reviews WHERE spot_id = ? AND difficulty_rating IS NOT NULL");
        $stmt->execute([$spotId]);
        $avg = (float)$stmt->fetchColumn();
        $stmt = $this->db->prepare("UPDATE parking_spots SET difficulty_score = ? WHERE spot_id = ?");
        $stmt->execute([round($avg, 2), $spotId]);
        return $avg;
    }

    // ─── NON-CRUD: Nearby Alternative Suggestion ─────────────

    public function getNearbyAlternatives(int $blockedSpotId, int $limit = 5): array {
        // لا يمكن استخدامها لعدم وجود إحداثيات
        return [];
    }

    // ─── Owner Verification ──────────────────────────────────
    public function submitVerification(int $ownerId, int $spotId, string $idPath, string $billPath): bool {
        $stmt = $this->db->prepare("INSERT INTO owner_verifications (owner_id, spot_id, id_document, utility_bill) VALUES (?,?,?,?)");
        return $stmt->execute([$ownerId, $spotId, $idPath, $billPath]);
    }

    public function approveVerification(int $verificationId, int $adminId): bool {
        $stmt = $this->db->prepare("SELECT spot_id FROM owner_verifications WHERE verification_id = ?");
        $stmt->execute([$verificationId]);
        $row = $stmt->fetch();
        if (!$row) return false;

        $stmt = $this->db->prepare("UPDATE owner_verifications SET status = 'approved', reviewed_by = ?, reviewed_at = NOW() WHERE verification_id = ?");
        $stmt->execute([$adminId, $verificationId]);
        $stmt = $this->db->prepare("UPDATE parking_spots SET is_verified = 1, status = 'available' WHERE spot_id = ?");
        return $stmt->execute([$row['spot_id']]);
    }

    public function checkOverlapWithExistingReservations(int $spotId, string $start, string $end): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM reservations
            WHERE spot_id = ? AND status IN ('confirmed','active','pending')
              AND start_time < ? AND end_time > ?
        ");
        $stmt->execute([$spotId, $end, $start]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // ─── Market Rate (مبسطة) ─────────────────────────────────
    public function suggestMarketRate(int $spotId): array {
        return ['suggested_price' => 0, 'nearby_avg' => 0, 'count' => 0];
    }

    // ─── Owner Dashboard Stats ───────────────────────────────
    public function getOwnerDashboardStats(int $ownerId): array {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(DISTINCT r.reservation_id) AS total_bookings,
                SUM(t.owner_earnings) AS total_earned,
                AVG(s.trust_score) AS avg_trust_score,
                COUNT(DISTINCT s.spot_id) AS total_spots
            FROM parking_spots s
            LEFT JOIN reservations r ON s.spot_id = r.spot_id AND r.status = 'completed'
            LEFT JOIN transactions t ON r.reservation_id = t.reservation_id
            WHERE s.owner_id = ?
        ");
        $stmt->execute([$ownerId]);
        $stats = $stmt->fetch();

        $stmt = $this->db->prepare("
            SELECT HOUR(r.start_time) AS hour_slot, COUNT(*) AS bookings
            FROM reservations r
            JOIN parking_spots s ON r.spot_id = s.spot_id
            WHERE s.owner_id = ? AND r.status = 'completed'
            GROUP BY HOUR(r.start_time)
            ORDER BY bookings DESC
            LIMIT 5
        ");
        $stmt->execute([$ownerId]);
        $stats['top_hours'] = $stmt->fetchAll();

        return $stats;
    }
}