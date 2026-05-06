<?php
// classes/Report.php
require_once __DIR__ . '/../config/db.php';

class Report {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    // ─── NON-CRUD: Revenue Heatmap Data ──────────────────────

    /**
     * Aggregates revenue by city zone for municipal heatmap visualization.
     */
    public function getRevenueHeatmapData(): array {
        $stmt = $this->db->prepare("
            SELECT 
                s.city_zone,
                s.latitude,
                s.longitude,
                COUNT(r.reservation_id) AS total_bookings,
                SUM(t.amount) AS total_revenue,
                AVG(t.amount) AS avg_transaction
            FROM parking_spots s
            LEFT JOIN reservations r ON s.spot_id = r.spot_id AND r.status = 'completed'
            LEFT JOIN transactions t ON r.reservation_id = t.reservation_id
            GROUP BY s.city_zone, s.latitude, s.longitude
            ORDER BY total_revenue DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ─── NON-CRUD: Sensor Health Monitor ─────────────────────

    /**
     * Tracks uptime of virtual parking sensors.
     * Flags sensors that haven't sent a heartbeat in >10 minutes.
     */
    public function getSensorHealthReport(): array {
        $stmt = $this->db->prepare("
            SELECT sh.*, s.title AS spot_title, s.address,
                   TIMESTAMPDIFF(MINUTE, sh.last_heartbeat, NOW()) AS minutes_since_heartbeat
            FROM sensor_health sh
            JOIN parking_spots s ON sh.spot_id = s.spot_id
            ORDER BY minutes_since_heartbeat DESC
        ");
        $stmt->execute();
        $sensors = $stmt->fetchAll();

        // Auto-flag offline sensors
        foreach ($sensors as &$sensor) {
            if ($sensor['minutes_since_heartbeat'] > 10) {
                $sensor['status'] = 'offline';
                $stmt2 = $this->db->prepare("UPDATE sensor_health SET status = 'offline' WHERE sensor_id = ?");
                $stmt2->execute([$sensor['sensor_id']]);
            }
        }
        return $sensors;
    }

    public function updateSensorHeartbeat(int $spotId): bool {
        $stmt = $this->db->prepare("
            INSERT INTO sensor_health (spot_id, last_heartbeat, status)
            VALUES (?, NOW(), 'online')
            ON DUPLICATE KEY UPDATE last_heartbeat = NOW(), status = 'online'
        ");
        return $stmt->execute([$spotId]);
    }

    // ─── NON-CRUD: Owner Monthly PDF Report Data ──────────────

    /**
     * Generates monthly report data for owner's business dashboard.
     */
    public function getOwnerMonthlyReport(int $ownerId, int $month, int $year): array {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(r.reservation_id) AS total_reservations,
                SUM(t.owner_earnings) AS total_earnings,
                SUM(t.platform_fee) AS platform_fees,
                AVG(r.total_amount) AS avg_booking_value,
                COUNT(CASE WHEN r.status = 'no_show' THEN 1 END) AS no_shows,
                COUNT(CASE WHEN r.status = 'cancelled' THEN 1 END) AS cancellations
            FROM parking_spots s
            LEFT JOIN reservations r ON s.spot_id = r.spot_id
                AND MONTH(r.created_at) = ? AND YEAR(r.created_at) = ?
            LEFT JOIN transactions t ON r.reservation_id = t.reservation_id
            WHERE s.owner_id = ?
        ");
        $stmt->execute([$month, $year, $ownerId]);
        $summary = $stmt->fetch();

        // Best time slots
        $stmt = $this->db->prepare("
            SELECT HOUR(r.start_time) AS hour, COUNT(*) AS count
            FROM reservations r
            JOIN parking_spots s ON r.spot_id = s.spot_id
            WHERE s.owner_id = ?
              AND MONTH(r.created_at) = ? AND YEAR(r.created_at) = ?
              AND r.status = 'completed'
            GROUP BY HOUR(r.start_time)
            ORDER BY count DESC
            LIMIT 5
        ");
        $stmt->execute([$ownerId, $month, $year]);
        $summary['top_hours'] = $stmt->fetchAll();

        // Occupancy rate per spot
        $stmt = $this->db->prepare("
            SELECT s.spot_id, s.title,
                   COUNT(r.reservation_id) AS bookings,
                   s.trust_score
            FROM parking_spots s
            LEFT JOIN reservations r ON s.spot_id = r.spot_id AND r.status = 'completed'
                AND MONTH(r.created_at) = ? AND YEAR(r.created_at) = ?
            WHERE s.owner_id = ?
            GROUP BY s.spot_id, s.title, s.trust_score
        ");
        $stmt->execute([$month, $year, $ownerId]);
        $summary['spots'] = $stmt->fetchAll();

        return $summary;
    }

    /**
     * Generates a simple HTML-based PDF report using basic output buffering.
     * In production: use FPDF or TCPDF library.
     */
    public function generateOwnerPDF(int $ownerId, int $month, int $year): string {
        $data = $this->getOwnerMonthlyReport($ownerId, $month, $year);

        // Get owner info
        $stmt = $this->db->prepare("SELECT full_name, email FROM users WHERE user_id = ?");
        $stmt->execute([$ownerId]);
        $owner = $stmt->fetch();

        $monthName  = date('F', mktime(0, 0, 0, $month, 1, $year));
        $reportFile = __DIR__ . "/../reports/owner_{$ownerId}_{$year}_{$month}.html";

        $html = "<!DOCTYPE html><html><head>
        <meta charset='utf-8'>
        <title>Monthly Report - {$monthName} {$year}</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; color: #333; }
            h1 { color: #1a73e8; } 
            .summary { display: flex; gap: 20px; margin: 20px 0; }
            .card { background: #f5f5f5; padding: 15px; border-radius: 8px; min-width: 150px; text-align: center; }
            .card h2 { margin: 0; font-size: 28px; color: #1a73e8; }
            .card p { margin: 5px 0 0; font-size: 14px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th { background: #1a73e8; color: white; padding: 10px; text-align: left; }
            td { padding: 8px 10px; border-bottom: 1px solid #ddd; }
            tr:nth-child(even) { background: #f9f9f9; }
        </style></head><body>
        <h1>🅿️ CitySlot - Monthly Earnings Report</h1>
        <p><strong>Owner:</strong> {$owner['full_name']} ({$owner['email']})</p>
        <p><strong>Period:</strong> {$monthName} {$year}</p>
        <hr>
        <div class='summary'>
            <div class='card'><h2>{$data['total_reservations']}</h2><p>Total Bookings</p></div>
            <div class='card'><h2>" . number_format((float)$data['total_earnings'], 2) . " EGP</h2><p>Net Earnings</p></div>
            <div class='card'><h2>" . number_format((float)$data['avg_booking_value'], 2) . " EGP</h2><p>Avg Booking Value</p></div>
            <div class='card'><h2>{$data['no_shows']}</h2><p>No-Shows</p></div>
            <div class='card'><h2>{$data['cancellations']}</h2><p>Cancellations</p></div>
        </div>
        <h2>Spot Performance</h2>
        <table>
            <tr><th>Spot</th><th>Bookings</th><th>Trust Score</th></tr>";

        foreach ($data['spots'] as $spot) {
            $html .= "<tr><td>{$spot['title']}</td><td>{$spot['bookings']}</td><td>{$spot['trust_score']}/5</td></tr>";
        }
        $html .= "</table>
        <h2>Top Booking Hours</h2><table>
        <tr><th>Hour</th><th>Bookings</th></tr>";
        foreach ($data['top_hours'] as $h) {
            $html .= "<tr><td>{$h['hour']}:00</td><td>{$h['count']}</td></tr>";
        }
        $html .= "</table><br><p style='color:#999;font-size:12px;'>Generated on " . date('Y-m-d H:i') . " | CitySlot Parking System</p></body></html>";

        file_put_contents($reportFile, $html);
        return $reportFile;
    }

    // ─── NON-CRUD: System-wide Analytics ─────────────────────

    public function getSystemStats(): array {
        $queries = [
            'total_users'        => "SELECT COUNT(*) FROM users WHERE is_active = 1",
            'total_spots'        => "SELECT COUNT(*) FROM parking_spots WHERE is_verified = 1",
            'total_reservations' => "SELECT COUNT(*) FROM reservations",
            'active_now'         => "SELECT COUNT(*) FROM reservations WHERE status = 'active'",
            'total_revenue'      => "SELECT SUM(amount) FROM transactions WHERE payment_status = 'released'",
            'pending_verif'      => "SELECT COUNT(*) FROM owner_verifications WHERE status = 'pending'",
            'open_appeals'       => "SELECT COUNT(*) FROM fine_appeals WHERE status = 'pending'",
            'blacklisted_users'  => "SELECT COUNT(*) FROM users WHERE is_blacklisted = 1",
        ];
        $stats = [];
        foreach ($queries as $key => $sql) {
            $stmt = $this->db->query($sql);
            $stats[$key] = $stmt->fetchColumn() ?? 0;
        }
        return $stats;
    }
}
