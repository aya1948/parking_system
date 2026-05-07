<?php
// classes/Garage.php
require_once __DIR__ . '/../config/db.php';

class Garage {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    // ─── CRUD ────────────────────────────────────────────────

    public function createGarage(array $data): array {
        $stmt = $this->db->prepare("
            INSERT INTO garages (owner_id, name, address, latitude, longitude, city_zone, total_floors, description)
            VALUES (?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            $data['owner_id'],
            trim($data['name']),
            trim($data['address']),
            $data['latitude']     ?? null,
            $data['longitude']    ?? null,
            $data['city_zone']    ?? null,
            $data['total_floors'] ?? 1,
            $data['description']  ?? '',
        ]);
        $garageId = (int)$this->db->lastInsertId();
        return ['success' => true, 'garage_id' => $garageId];
    }

    public function getGarageById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT g.*, u.full_name AS owner_name,
                   COUNT(s.spot_id) AS total_spots,
                   SUM(s.status = 'available') AS available_spots
            FROM garages g
            JOIN users u ON g.owner_id = u.user_id
            LEFT JOIN parking_spots s ON s.garage_id = g.garage_id
            WHERE g.garage_id = ?
            GROUP BY g.garage_id
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function listOwnerGarages(int $ownerId): array {
        $stmt = $this->db->prepare("
            SELECT g.*,
                   COUNT(s.spot_id) AS total_spots,
                   SUM(s.status = 'available') AS available_spots,
                   SUM(s.status = 'unavailable') AS unavailable_spots
            FROM garages g
            LEFT JOIN parking_spots s ON s.garage_id = g.garage_id
            WHERE g.owner_id = ?
            GROUP BY g.garage_id
            ORDER BY g.created_at DESC
        ");
        $stmt->execute([$ownerId]);
        return $stmt->fetchAll();
    }

    public function deleteGarage(int $garageId, int $ownerId): array {
        // Check active reservations
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM reservations r
            JOIN parking_spots s ON r.spot_id = s.spot_id
            WHERE s.garage_id = ? AND r.status IN ('confirmed','active','pending')
        ");
        $stmt->execute([$garageId]);
        if ((int)$stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Cannot delete garage with active reservations.'];
        }
        $stmt = $this->db->prepare("DELETE FROM garages WHERE garage_id = ? AND owner_id = ?");
        $stmt->execute([$garageId, $ownerId]);
        return ['success' => true];
    }

    // ─── NON-CRUD: Auto-generate Numbered Spots ───────────────

    /**
     * Auto-generates numbered spots inside a garage.
     * Format: A1, A2... B1, B2... based on rows & cols.
     * e.g. rows=2, cols=5 → A1-A5, B1-B5 = 10 spots
     */
    public function generateSpots(int $garageId, array $config): array {
        $garage = $this->getGarageById($garageId);
        if (!$garage) return ['success' => false, 'message' => 'Garage not found.'];

        $rows       = (int)($config['rows']           ?? 1);
        $cols       = (int)($config['cols']           ?? 10);
        $pricePerHr = (float)($config['price_per_hour'] ?? 20);
        $hasEV      = (int)($config['has_ev_charger']  ?? 0);
        $maxH       = $config['max_height_cm']          ?? null;
        $maxW       = $config['max_width_cm']           ?? null;
        $prefix     = strtoupper($config['prefix']      ?? '');

        $created = 0;
        $letters = range('A', 'Z');

        $this->db->beginTransaction();
        try {
            for ($r = 0; $r < $rows; $r++) {
                $rowLetter = $prefix . $letters[$r];
                for ($c = 1; $c <= $cols; $c++) {
                    $spotNumber = $rowLetter . $c; // e.g. A1, A2, B3
                    $title      = $garage['name'] . ' — Spot ' . $spotNumber;

                    $stmt = $this->db->prepare("
                        INSERT INTO parking_spots
                        (owner_id, garage_id, spot_number, title, address, latitude, longitude,
                         spot_type, status, price_per_hour, base_price,
                         max_height_cm, max_width_cm, has_ev_charger, city_zone)
                        VALUES (?,?,?,?,?,?,?,'garage','available',?,?,?,?,?,?)
                    ");
                    $stmt->execute([
                        $garage['owner_id'],
                        $garageId,
                        $spotNumber,
                        $title,
                        $garage['address'],
                        $garage['latitude'],
                        $garage['longitude'],
                        $pricePerHr,
                        $pricePerHr,
                        $maxH,
                        $maxW,
                        $hasEV,
                        $garage['city_zone'],
                    ]);
                    $created++;
                }
            }
            $this->db->commit();
            return ['success' => true, 'created' => $created, 'garage_id' => $garageId];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ─── NON-CRUD: Get Garage Spots Grid ─────────────────────

    /**
     * Returns spots as a 2D grid grouped by row letter.
     * Used to display the visual parking map.
     */
    public function getSpotsGrid(int $garageId): array {
        $stmt = $this->db->prepare("
            SELECT s.*,
                   CASE
                     WHEN EXISTS (
                       SELECT 1 FROM reservations r
                       WHERE r.spot_id = s.spot_id
                         AND r.status IN ('confirmed','active','pending')
                         AND NOW() BETWEEN r.start_time AND DATE_ADD(r.end_time, INTERVAL 10 MINUTE)
                     ) THEN 'occupied'
                     ELSE s.status
                   END AS real_status
            FROM parking_spots s
            WHERE s.garage_id = ?
            ORDER BY s.spot_number ASC
        ");
        $stmt->execute([$garageId]);
        $spots = $stmt->fetchAll();

        // Group by row letter
        $grid = [];
        foreach ($spots as $spot) {
            $row = preg_replace('/[0-9]/', '', $spot['spot_number']); // A, B, C...
            $grid[$row][] = $spot;
        }
        return $grid;
    }

    // ─── NON-CRUD: Garage Occupancy Stats ────────────────────

    public function getGarageOccupancy(int $garageId): array {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN EXISTS (
                    SELECT 1 FROM reservations r
                    WHERE r.spot_id = s.spot_id
                      AND r.status IN ('confirmed','active')
                      AND NOW() BETWEEN r.start_time AND r.end_time
                ) THEN 1 ELSE 0 END) AS occupied_now,
                SUM(s.status = 'maintenance') AS maintenance,
                SUM(s.status = 'available') AS available
            FROM parking_spots s
            WHERE s.garage_id = ?
        ");
        $stmt->execute([$garageId]);
        $stats = $stmt->fetch();
        $stats['occupancy_rate'] = $stats['total'] > 0
            ? round($stats['occupied_now'] / $stats['total'] * 100, 1)
            : 0;
        return $stats;
    }
}
