<?php
// classes/Vehicle.php
require_once __DIR__ . '/../config/db.php';

class Vehicle {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    // ─── CRUD ────────────────────────────────────────────────

    public function addVehicle(array $data): array {
        // If this is driver's first vehicle, set as default
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM vehicles WHERE user_id = ?");
        $stmt->execute([$data['user_id']]);
        $isFirst = (int)$stmt->fetchColumn() === 0;

        $stmt = $this->db->prepare("
            INSERT INTO vehicles (user_id, license_plate, make, model, color, vehicle_type, height_cm, width_cm, is_ev, is_default)
            VALUES (?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            $data['user_id'], strtoupper($data['license_plate']),
            $data['make'] ?? '', $data['model'] ?? '', $data['color'] ?? '',
            $data['vehicle_type'] ?? 'sedan',
            $data['height_cm'] ?? null, $data['width_cm'] ?? null,
            $data['is_ev'] ?? 0, $isFirst ? 1 : 0
        ]);
        return ['success' => true, 'vehicle_id' => $this->db->lastInsertId()];
    }

    public function getVehicleById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM vehicles WHERE vehicle_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function updateVehicle(int $vehicleId, int $userId, array $data): bool {
        $allowed = ['make','model','color','height_cm','width_cm','is_ev'];
        $fields  = []; $values = [];
        foreach ($allowed as $f) {
            if (isset($data[$f])) { $fields[] = "$f = ?"; $values[] = $data[$f]; }
        }
        if (empty($fields)) return false;
        $values[] = $vehicleId; $values[] = $userId;
        $stmt = $this->db->prepare("UPDATE vehicles SET " . implode(', ', $fields) . " WHERE vehicle_id = ? AND user_id = ?");
        return $stmt->execute($values);
    }

    public function deleteVehicle(int $vehicleId, int $userId): array {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM reservations WHERE vehicle_id = ? AND status IN ('pending','confirmed','active')");
        $stmt->execute([$vehicleId]);
        if ((int)$stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Cannot delete vehicle with active reservations.'];
        }
        $stmt = $this->db->prepare("DELETE FROM vehicles WHERE vehicle_id = ? AND user_id = ?");
        $stmt->execute([$vehicleId, $userId]);
        return ['success' => true];
    }

    public function listUserVehicles(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM vehicles WHERE user_id = ? ORDER BY is_default DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    // ─── NON-CRUD: Multi-Vehicle Profile Switcher ─────────────

    /**
     * Allows driver to switch active (default) vehicle for new bookings.
     */
    public function setDefaultVehicle(int $vehicleId, int $userId): bool {
        $this->db->beginTransaction();
        // Clear all defaults for this user
        $stmt = $this->db->prepare("UPDATE vehicles SET is_default = 0 WHERE user_id = ?");
        $stmt->execute([$userId]);
        // Set new default
        $stmt = $this->db->prepare("UPDATE vehicles SET is_default = 1 WHERE vehicle_id = ? AND user_id = ?");
        $stmt->execute([$vehicleId, $userId]);
        $this->db->commit();
        return true;
    }

    public function getDefaultVehicle(int $userId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM vehicles WHERE user_id = ? AND is_default = 1 LIMIT 1");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }
}


// ════════════════════════════════════════════════════════════
// classes/Review.php  (included in same file for convenience)
// ════════════════════════════════════════════════════════════

class Review {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    // ─── CRUD ────────────────────────────────────────────────

    public function submitReview(int $reservationId, int $reviewerId, int $rating, ?int $difficultyRating, ?string $comment): array {
        // Verify reservation is completed and belongs to reviewer
        $stmt = $this->db->prepare("SELECT spot_id, status FROM reservations WHERE reservation_id = ? AND driver_id = ?");
        $stmt->execute([$reservationId, $reviewerId]);
        $res = $stmt->fetch();

        if (!$res) return ['success' => false, 'message' => 'Reservation not found.'];
        if ($res['status'] !== 'completed') return ['success' => false, 'message' => 'Can only review completed reservations.'];

        // Check not already reviewed
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM reviews WHERE reservation_id = ? AND reviewer_id = ?");
        $stmt->execute([$reservationId, $reviewerId]);
        if ((int)$stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Already reviewed this reservation.'];
        }

        $stmt = $this->db->prepare("INSERT INTO reviews (reservation_id, reviewer_id, spot_id, rating, difficulty_rating, comment) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$reservationId, $reviewerId, $res['spot_id'], $rating, $difficultyRating, $comment]);

        // Recalculate trust & difficulty scores
        require_once __DIR__ . '/ParkingSpot.php';
        $spotObj = new ParkingSpot();
        $spotObj->recalculateTrustScore($res['spot_id']);
        $spotObj->recalculateDifficultyScore($res['spot_id']);

        return ['success' => true, 'review_id' => $this->db->lastInsertId()];
    }

    public function getSpotReviews(int $spotId, int $limit = 20): array {
        $stmt = $this->db->prepare("
            SELECT r.*, u.full_name AS reviewer_name
            FROM reviews r
            JOIN users u ON r.reviewer_id = u.user_id
            WHERE r.spot_id = ?
            ORDER BY r.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$spotId, $limit]);
        return $stmt->fetchAll();
    }

    public function deleteReview(int $reviewId, int $adminId): bool {
        $stmt = $this->db->prepare("DELETE FROM reviews WHERE review_id = ?");
        $result = $stmt->execute([$reviewId]);
        // Re-run trust score after deletion
        return $result;
    }
}
