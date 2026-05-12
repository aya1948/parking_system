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
            INSERT INTO garages (owner_id, name, address, city_zone, total_floors, description, is_verified)
            VALUES (?,?,?,?,?,?,0)
        ");
        $stmt->execute([
            $data['owner_id'],
            trim($data['name']),
            trim($data['address']),
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
                   SUM(s.status = 'available' AND s.is_verified = 1) AS available_spots
            FROM garages g
            JOIN users u ON g.owner_id = u.user_id
            LEFT JOIN parking_spots s ON s.garage_id = g.garage_id AND s.is_verified = 1
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
                   SUM(s.status = 'available' AND s.is_verified = 1) AS available_spots,
                   SUM(s.status = 'unavailable') AS unavailable_spots
            FROM garages g
            LEFT JOIN parking_spots s ON s.garage_id = g.garage_id AND s.is_verified = 1
            WHERE g.owner_id = ?
            GROUP BY g.garage_id
            ORDER BY g.created_at DESC
        ");
        $stmt->execute([$ownerId]);
        return $stmt->fetchAll();
    }

    public function deleteGarage(int $garageId, int $ownerId): array {
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

    public function approveGarage(int $garageId): bool {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("UPDATE garages SET is_verified = 1 WHERE garage_id = ?");
            $stmt->execute([$garageId]);
            $stmt = $this->db->prepare("UPDATE parking_spots SET is_verified = 1 WHERE garage_id = ?");
            $stmt->execute([$garageId]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function generateSpots(int $garageId, array $config): array {
        $garage = $this->getGarageById($garageId);
        if (!$garage) return ['success' => false, 'message' => 'Garage not found.'];

        $rows       = (int)($config['rows']           ?? 1);
        $cols       = (int)($config['cols']           ?? 10);
        $pricePerHr = (float)($config['price_per_hour'] ?? 20);
        $hasEV      = (int)($config['has_ev_charger']  ?? 0);
        $prefix     = strtoupper($config['prefix']      ?? '');

        $created = 0;
        $letters = range('A', 'Z');

        $this->db->beginTransaction();
        try {
            for ($r = 0; $r < $rows; $r++) {
                $rowLetter = $prefix . $letters[$r];
                for ($c = 1; $c <= $cols; $c++) {
                    $spotNumber = $rowLetter . $c;
                    $title      = $garage['name'] . ' — Spot ' . $spotNumber;

                    $stmt = $this->db->prepare("
                        INSERT INTO parking_spots
                        (owner_id, garage_id, spot_number, title, address,
                         spot_type, status, price_per_hour, base_price,
                         has_ev_charger, city_zone, is_verified)
                        VALUES (?,?,?,?,?,'garage','available',?,?,?,?,0)
                    ");
                    $stmt->execute([
                        $garage['owner_id'],
                        $garageId,
                        $spotNumber,
                        $title,
                        $garage['address'],
                        $pricePerHr,
                        $pricePerHr,
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

        $grid = [];
        foreach ($spots as $spot) {
            $row = preg_replace('/[0-9]/', '', $spot['spot_number']);
            $grid[$row][] = $spot;
        }
        return $grid;
    }

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
                SUM(s.status = 'available' AND s.is_verified = 1) AS available
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

    // ─── searchGarages ───────────────────────────────────────
    public function searchGarages(array $filters): array {
        $bufferMinutes = 10;
        $searchStart   = $filters['start_time'] ?? date('Y-m-d H:i:s');
        $searchEnd     = $filters['end_time']   ?? date('Y-m-d H:i:s', strtotime('+1 hour'));

        $sql = "
            SELECT g.*,
                   u.full_name AS owner_name,
                   COUNT(DISTINCT s.spot_id) AS total_spots,

                   SUM(CASE
                     WHEN s.spot_id IS NOT NULL
                       AND s.status = 'available'
                       AND s.is_verified = 1
                       AND NOT EXISTS (
                         SELECT 1 FROM reservations r
                         WHERE r.spot_id = s.spot_id
                           AND r.status IN ('confirmed','active','extended','pending')
                           AND r.start_time  < DATE_ADD(?, INTERVAL ? MINUTE)
                           AND DATE_ADD(r.end_time, INTERVAL ? MINUTE) > ?
                       )
                     THEN 1 ELSE 0
                   END) AS free_spots,

                   MIN(s.price_per_hour) AS min_price,
                   MAX(s.price_per_hour) AS max_price,
                   MAX(s.has_ev_charger) AS has_ev
            FROM garages g
            JOIN users u ON g.owner_id = u.user_id
            LEFT JOIN parking_spots s ON s.garage_id = g.garage_id
                AND s.status != 'maintenance'
                AND s.is_verified = 1
            WHERE g.is_verified = 1
        ";

        $params = [
            $searchEnd, $bufferMinutes,
            $bufferMinutes, $searchStart,
        ];

        if (!empty($filters['zone'])) {
            $sql .= " AND g.city_zone LIKE ?";
            $params[] = '%' . $filters['zone'] . '%';
        }
        if (!empty($filters['needs_ev'])) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM parking_spots ps
                WHERE ps.garage_id = g.garage_id AND ps.has_ev_charger = 1 AND ps.is_verified = 1
            )";
        }
        if (!empty($filters['max_price'])) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM parking_spots ps
                WHERE ps.garage_id = g.garage_id AND ps.price_per_hour <= ? AND ps.is_verified = 1
            )";
            $params[] = $filters['max_price'];
        }

        $sql .= " GROUP BY g.garage_id ORDER BY free_spots DESC, g.city_zone ASC LIMIT 50";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function listZones(): array {
        $stmt = $this->db->query("
            SELECT city_zone, COUNT(*) AS garage_count
            FROM garages
            WHERE city_zone IS NOT NULL AND city_zone != ''
            GROUP BY city_zone
            ORDER BY city_zone ASC
        ");
        return $stmt->fetchAll();
    }

    public function getAvailableSpotsInGarage(int $garageId, string $start, string $end, array $vehicleFilters = []): array {
        $bufferMinutes = 10;
        $sql = "
            SELECT s.*,
                   CASE
                     WHEN EXISTS (
                       SELECT 1 FROM reservations r
                       WHERE r.spot_id = s.spot_id
                         AND r.status IN ('confirmed','active','extended','pending')
                         AND r.start_time  < DATE_ADD(?, INTERVAL ? MINUTE)
                         AND DATE_ADD(r.end_time, INTERVAL ? MINUTE) > ?
                     ) THEN 'occupied'
                     ELSE 'available'
                   END AS real_status
            FROM parking_spots s
            WHERE s.garage_id = ?
              AND s.status = 'available'
              AND s.is_verified = 1
        ";
        $params = [$end, $bufferMinutes, $bufferMinutes, $start, $garageId];

        if (!empty($vehicleFilters['needs_ev'])) {
            $sql .= " AND s.has_ev_charger = 1";
        }

        $sql .= " ORDER BY s.spot_number ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

}