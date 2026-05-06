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
            (owner_id, title, description, address, latitude, longitude, spot_type, price_per_hour, base_price, max_height_cm, max_width_cm, has_ev_charger, city_zone)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            $data['owner_id'], $data['title'], $data['description'] ?? '',
            $data['address'], $data['latitude'] ?? null, $data['longitude'] ?? null,
            $data['spot_type'] ?? 'driveway',
            $data['price_per_hour'], $data['price_per_hour'], // base = initial price
            $data['max_height_cm'] ?? null, $data['max_width_cm'] ?? null,
            $data['has_ev_charger'] ?? 0, $data['city_zone'] ?? null
        ]);
        return ['success' => true, 'spot_id' => $this->db->lastInsertId()];
    }

    public function getSpotById(int $spotId): ?array {
        $stmt = $this->db->prepare("
            SELECT s.*, u.full_name AS owner_name, u.phone AS owner_phone
            FROM parking_spots s
            JOIN users u ON s.owner_id = u.user_id
            WHERE s.spot_id = ?
        ");
        $stmt->execute([$spotId]);
        return $stmt->fetch() ?: null;
    }

    public function updateSpot(int $spotId, array $data, int $ownerId): bool {
        // First check ownership
        $spot = $this->getSpotById($spotId);
        if (!$spot || $spot['owner_id'] != $ownerId) return false;

        $allowed = ['title','description','address','price_per_hour','max_height_cm','max_width_cm','has_ev_charger','city_zone'];
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
        // Cannot delete spot with active reservations
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

    /**
     * Advanced filter engine: matches driver vehicle to spot attributes.
     * Shows occupied spots with badge but prevents double-booking.
     * Buffer time = 10 minutes after end_time.
     */
    public function searchSpots(array $filters): array {
        $bufferMinutes = 10;
        $searchStart   = $filters['start_time'] ?? date('Y-m-d H:i:s');
        $searchEnd     = $filters['end_time']   ?? date('Y-m-d H:i:s', strtotime('+1 hour'));

        $sql = "
            SELECT s.*, u.full_name AS owner_name,
                   ROUND(s.trust_score, 1) AS trust_score,
                   CASE
                     WHEN EXISTS (
                       SELECT 1 FROM reservations r
                       WHERE r.spot_id = s.spot_id
                         AND r.status IN ('confirmed','active','pending')
                         AND r.start_time < DATE_ADD(?, INTERVAL ? MINUTE)
                         AND DATE_ADD(r.end_time, INTERVAL ? MINUTE) > ?
                     ) THEN 'occupied'
                     ELSE 'free'
                   END AS real_time_status,
                   (
                     SELECT DATE_ADD(MAX(r2.end_time), INTERVAL ? MINUTE)
                     FROM reservations r2
                     WHERE r2.spot_id = s.spot_id
                       AND r2.status IN ('confirmed','active','pending')
                       AND r2.end_time > NOW()
                   ) AS next_available_at
            FROM parking_spots s
            JOIN users u ON s.owner_id = u.user_id
            WHERE s.status = 'available'
              AND s.is_verified = 1
        ";
        $params = [$searchEnd, $bufferMinutes, $bufferMinutes, $searchStart, $bufferMinutes];

        if (!empty($filters['vehicle_height'])) {
            $sql .= " AND (s.max_height_cm IS NULL OR s.max_height_cm >= ?)";
            $params[] = $filters['vehicle_height'];
        }
        if (!empty($filters['vehicle_width'])) {
            $sql .= " AND (s.max_width_cm IS NULL OR s.max_width_cm >= ?)";
            $params[] = $filters['vehicle_width'];
        }
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
        if (!empty($filters['hide_occupied'])) {
            $sql .= " AND NOT EXISTS (
                SELECT 1 FROM reservations r
                WHERE r.spot_id = s.spot_id
                  AND r.status IN ('confirmed','active','pending')
                  AND r.start_time < DATE_ADD(?, INTERVAL ? MINUTE)
                  AND DATE_ADD(r.end_time, INTERVAL ? MINUTE) > ?
            )";
            $params[] = $searchEnd;
            $params[] = $bufferMinutes;
            $params[] = $bufferMinutes;
            $params[] = $searchStart;
        }
        $sql .= " ORDER BY real_time_status ASC, s.trust_score DESC, s.price_per_hour ASC LIMIT 50";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ─── NON-CRUD: Blind-Spot Prevention ─────────────────────

    /**
     * Hides spot from search if in Maintenance or Owner-Use mode.
     * Returns true if spot should be visible in search.
     */
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
        // Check no active reservations before maintenance/owner_use
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

    /**
     * Weighted trust score from driver ratings (1-5).
     * Recent reviews have higher weight.
     */
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
            $weight = 1 / (1 + ($r['days_old'] / 30)); // newer = higher weight
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

    /**
     * Suggests nearest available spots when a reserved spot becomes blocked.
     * Uses simple distance formula (Haversine simulation).
     */
    public function getNearbyAlternatives(int $blockedSpotId, int $limit = 5): array {
        $spot = $this->getSpotById($blockedSpotId);
        if (!$spot || !$spot['latitude']) return [];

        $lat = $spot['latitude'];
        $lng = $spot['longitude'];

        $stmt = $this->db->prepare("
            SELECT *,
                   ROUND(
                     6371 * ACOS(
                       COS(RADIANS(?)) * COS(RADIANS(latitude)) *
                       COS(RADIANS(longitude) - RADIANS(?)) +
                       SIN(RADIANS(?)) * SIN(RADIANS(latitude))
                     ), 2
                   ) AS distance_km
            FROM parking_spots
            WHERE status = 'available'
              AND is_verified = 1
              AND spot_id != ?
            ORDER BY distance_km ASC
            LIMIT ?
        ");
        $stmt->execute([$lat, $lng, $lat, $blockedSpotId, $limit]);
        return $stmt->fetchAll();
    }

    // ─── NON-CRUD: Owner Verification Workflow ───────────────

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

    // ─── NON-CRUD: Overlapping Reservation Check ─────────────

    public function checkOverlapWithExistingReservations(int $spotId, string $start, string $end): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM reservations
            WHERE spot_id = ?
              AND status IN ('confirmed','active','pending')
              AND start_time < ? AND end_time > ?
        ");
        $stmt->execute([$spotId, $end, $start]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // ─── NON-CRUD: Dynamic Market-Rate Calculator ─────────────

    /**
     * Suggests optimal price to Owner based on average price of nearby spots.
     */
    public function suggestMarketRate(int $spotId): array {
        $spot = $this->getSpotById($spotId);
        if (!$spot) return ['suggested_price' => 0, 'nearby_avg' => 0, 'count' => 0];

        $lat = $spot['latitude'];
        $lng = $spot['longitude'];

        $stmt = $this->db->prepare("
            SELECT AVG(price_per_hour) as avg_price, COUNT(*) as total
            FROM parking_spots
            WHERE spot_id != ?
              AND is_verified = 1
              AND status = 'available'
              AND ABS(latitude - ?) < 0.05
              AND ABS(longitude - ?) < 0.05
        ");
        $stmt->execute([$spotId, $lat, $lng]);
        $data = $stmt->fetch();

        $avg = round((float)($data['avg_price'] ?? $spot['base_price']), 2);
        return [
            'suggested_price' => $avg,
            'nearby_avg'      => $avg,
            'count'           => (int)($data['total'] ?? 0),
        ];
    }

    // ─── NON-CRUD: Owner Dashboard Analytics ─────────────────

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

        // Best performing time slots
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