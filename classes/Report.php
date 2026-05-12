<?php
// classes/Report.php
require_once __DIR__ . '/../config/db.php';

class Report {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getSystemStats(): array {
        $stats = [];

        $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE is_active = 1");
        $stats['total_users'] = (int)$stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COUNT(*) FROM parking_spots WHERE is_verified = 1");
        $stats['total_spots'] = (int)$stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COUNT(*) FROM reservations WHERE status = 'completed'");
        $stats['total_reservations'] = (int)$stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COUNT(*) FROM reservations WHERE status IN ('confirmed','active','extended')");
        $stats['active_now'] = (int)$stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE payment_status = 'released_to_owner'");
        $stats['total_revenue'] = (float)$stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COUNT(*) FROM owner_verifications WHERE status = 'pending'");
        $stats['pending_verif'] = (int)$stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COUNT(*) FROM fine_appeals WHERE status = 'pending'");
        $stats['open_appeals'] = (int)$stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE is_blacklisted = 1");
        $stats['blacklisted_users'] = (int)$stmt->fetchColumn();

        return $stats;
    }

    public function getOwnerMonthlyReport(int $ownerId, int $month, int $year): array {
        $data = [];

        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM reservations r
            JOIN parking_spots s ON r.spot_id = s.spot_id
            WHERE s.owner_id = ? AND MONTH(r.start_time) = ? AND YEAR(r.start_time) = ? AND r.status = 'completed'
        ");
        $stmt->execute([$ownerId, $month, $year]);
        $data['total_reservations'] = (int)$stmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(t.owner_earnings), 0)
            FROM transactions t
            JOIN reservations r ON t.reservation_id = r.reservation_id
            JOIN parking_spots s ON r.spot_id = s.spot_id
            WHERE s.owner_id = ? AND MONTH(r.start_time) = ? AND YEAR(r.start_time) = ? AND t.payment_status = 'released_to_owner'
        ");
        $stmt->execute([$ownerId, $month, $year]);
        $data['total_earnings'] = (float)$stmt->fetchColumn();

        $data['avg_booking_value'] = $data['total_reservations'] > 0
            ? round($data['total_earnings'] / $data['total_reservations'], 2)
            : 0;

        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM reservations r
            JOIN parking_spots s ON r.spot_id = s.spot_id
            WHERE s.owner_id = ? AND MONTH(r.start_time) = ? AND YEAR(r.start_time) = ? AND r.status = 'no_show'
        ");
        $stmt->execute([$ownerId, $month, $year]);
        $data['no_shows'] = (int)$stmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM reservations r
            JOIN parking_spots s ON r.spot_id = s.spot_id
            WHERE s.owner_id = ? AND MONTH(r.start_time) = ? AND YEAR(r.start_time) = ? AND r.status = 'cancelled'
        ");
        $stmt->execute([$ownerId, $month, $year]);
        $data['cancellations'] = (int)$stmt->fetchColumn();

        // استعلام أداء المواقف مع trust_score
        $stmt = $this->db->prepare("
            SELECT s.title, COUNT(r.reservation_id) AS bookings, ROUND(s.trust_score, 1) AS trust_score
            FROM parking_spots s
            LEFT JOIN reservations r ON s.spot_id = r.spot_id AND MONTH(r.start_time) = ? AND YEAR(r.start_time) = ? AND r.status = 'completed'
            WHERE s.owner_id = ?
            GROUP BY s.spot_id
            ORDER BY bookings DESC
        ");
        $stmt->execute([$month, $year, $ownerId]);
        $data['spots'] = $stmt->fetchAll();

        $stmt = $this->db->prepare("
            SELECT HOUR(r.start_time) AS hour, COUNT(*) AS count
            FROM reservations r
            JOIN parking_spots s ON r.spot_id = s.spot_id
            WHERE s.owner_id = ? AND MONTH(r.start_time) = ? AND YEAR(r.start_time) = ? AND r.status = 'completed'
            GROUP BY HOUR(r.start_time)
            ORDER BY count DESC
        ");
        $stmt->execute([$ownerId, $month, $year]);
        $data['top_hours'] = $stmt->fetchAll();

        return $data;
    }

    public function getRevenueHeatmapData(): array {
        $stmt = $this->db->query("
            SELECT s.city_zone, 
                   COALESCE(SUM(t.amount), 0) AS total_revenue,
                   COUNT(DISTINCT s.spot_id) AS spot_count
            FROM parking_spots s
            LEFT JOIN reservations r ON r.spot_id = s.spot_id
            LEFT JOIN transactions t ON t.reservation_id = r.reservation_id AND t.payment_status = 'released_to_owner'
            WHERE s.city_zone IS NOT NULL AND s.city_zone != ''
            GROUP BY s.city_zone
            ORDER BY total_revenue DESC
        ");
        return $stmt->fetchAll();
    }

    public function generateOwnerPDF(int $ownerId, int $month, int $year): string {
        $data = $this->getOwnerMonthlyReport($ownerId, $month, $year);
        $monthName = date('F', mktime(0,0,0,$month,1,$year));

        $html = "<html><head><meta charset='UTF-8'><title>Report $monthName $year</title>";
        $html .= "<style>body{font-family:sans-serif} table{border-collapse:collapse;width:100%} th,td{border:1px solid #ddd;padding:8px} th{background:#480959;color:#fff}</style></head><body>";
        $html .= "<h1>Monthly Report - $monthName $year</h1>";

        $html .= "<h2>Summary</h2><table>";
        $html .= "<tr><td>Total Bookings</td><td>{$data['total_reservations']}</td></tr>";
        $html .= "<tr><td>Net Earnings</td><td>" . number_format($data['total_earnings'], 2) . " EGP</td></tr>";
        $html .= "<tr><td>Avg Booking Value</td><td>" . number_format($data['avg_booking_value'], 2) . " EGP</td></tr>";
        $html .= "<tr><td>No-Shows</td><td>{$data['no_shows']}</td></tr>";
        $html .= "<tr><td>Cancellations</td><td>{$data['cancellations']}</td></tr>";
        $html .= "</table>";

        $html .= "<h2>Spot Performance</h2><table><tr><th>Spot</th><th>Bookings</th><th>Trust</th></tr>";
        foreach ($data['spots'] as $spot) {
            $html .= "<tr><td>{$spot['title']}</td><td>{$spot['bookings']}</td><td>" . number_format($spot['trust_score'] ?? 0, 1) . " ★</td></tr>";
        }
        $html .= "</table>";

        $html .= "</body></html>";

        $filePath = __DIR__ . "/../reports/report_{$ownerId}_{$month}_{$year}.html";
        file_put_contents($filePath, $html);
        return $filePath;
    }
}