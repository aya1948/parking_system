<?php
// classes/Notification.php
require_once __DIR__ . '/../config/db.php';

class Notification {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    // ─── CRUD ────────────────────────────────────────────────

    public function getUserNotifications(int $userId, bool $unreadOnly = false): array {
        $sql    = "SELECT * FROM notifications WHERE user_id = ?";
        $params = [$userId];
        if ($unreadOnly) { $sql .= " AND is_read = 0"; }
        $sql .= " ORDER BY created_at DESC LIMIT 50";
        $stmt   = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function markAsRead(int $notificationId, int $userId): bool {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?");
        return $stmt->execute([$notificationId, $userId]);
    }

    public function markAllRead(int $userId): bool {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }

    public function deleteNotification(int $notificationId, int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM notifications WHERE notification_id = ? AND user_id = ?");
        return $stmt->execute([$notificationId, $userId]);
    }

    // ─── NON-CRUD: Notification Escalation Engine ─────────────

    /**
     * Sends tiered alerts:
     * 1st alert: Web push (15 min before expiry)
     * 2nd alert: Email (5 min before expiry)
     * Penalty alert: SMS simulation after overstay detected
     */
    public function runEscalationEngine(): int {
        $sent = 0;

        // Get active reservations expiring in next 15 minutes
        $stmt = $this->db->prepare("
            SELECT r.reservation_id, r.driver_id, r.end_time, r.spot_id,
                   s.title AS spot_title
            FROM reservations r
            JOIN parking_spots s ON r.spot_id = s.spot_id
            WHERE r.status = 'active'
              AND r.end_time BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 15 MINUTE)
        ");
        $stmt->execute();
        $expiring = $stmt->fetchAll();

        foreach ($expiring as $res) {
            $minutesLeft = (int)((strtotime($res['end_time']) - time()) / 60);

            if ($minutesLeft <= 15 && $minutesLeft > 5) {
                // Web push alert
                $this->send($res['driver_id'], 'expiry_warning', 'web',
                    'Parking Expiring Soon',
                    "Your booking at {$res['spot_title']} expires in {$minutesLeft} minutes."
                );
                $sent++;
            } elseif ($minutesLeft <= 5) {
                // Email escalation
                $this->send($res['driver_id'], 'expiry_warning', 'email',
                    'URGENT: Parking Expiring in 5 Minutes',
                    "Your parking at {$res['spot_title']} expires in {$minutesLeft} minutes. Penalties apply after expiry."
                );
                $sent++;
            }
        }

        // Detect overstays and send penalty alert
        $stmt = $this->db->prepare("
            SELECT r.reservation_id, r.driver_id, r.end_time, s.title AS spot_title
            FROM reservations r
            JOIN parking_spots s ON r.spot_id = s.spot_id
            WHERE r.status = 'active'
              AND r.end_time < NOW()
        ");
        $stmt->execute();
        $overstaying = $stmt->fetchAll();

        foreach ($overstaying as $res) {
            $this->send($res['driver_id'], 'penalty_alert', 'sms',
                'Overstay Penalty Activated',
                "You are overstaying at {$res['spot_title']}. Penalty charges are accumulating."
            );
            $sent++;
        }

        return $sent;
    }

    // ─── NON-CRUD: Waitlist Automation ───────────────────────

    /**
     * Notifies all waitlisted drivers when a spot becomes free.
     */
    public function notifyWaitlist(int $spotId, string $freedStart, string $freedEnd): int {
        $stmt = $this->db->prepare("
            SELECT w.*, u.full_name, u.email
            FROM waitlist w
            JOIN users u ON w.driver_id = u.user_id
            WHERE w.spot_id = ?
              AND w.status = 'watching'
              AND w.desired_start >= ?
              AND w.desired_end   <= ?
            ORDER BY w.added_at ASC
        ");
        $stmt->execute([$spotId, $freedStart, $freedEnd]);
        $waiting = $stmt->fetchAll();

        $notified = 0;
        foreach ($waiting as $entry) {
            $this->send($entry['driver_id'], 'waitlist_available', 'web',
                'Parking Spot Available!',
                "A spot you were watching is now available for your desired time. Book now before it's gone!"
            );
            // Update waitlist status
            $stmt2 = $this->db->prepare("UPDATE waitlist SET status = 'notified' WHERE waitlist_id = ?");
            $stmt2->execute([$entry['waitlist_id']]);
            $notified++;
        }
        return $notified;
    }

    public function addToWaitlist(int $spotId, int $driverId, int $vehicleId, string $desiredStart, string $desiredEnd): array {
        // Check not already watching
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM waitlist WHERE spot_id = ? AND driver_id = ? AND status = 'watching'");
        $stmt->execute([$spotId, $driverId]);
        if ((int)$stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Already watching this spot.'];
        }
        $stmt = $this->db->prepare("INSERT INTO waitlist (spot_id, driver_id, vehicle_id, desired_start, desired_end) VALUES (?,?,?,?,?)");
        $stmt->execute([$spotId, $driverId, $vehicleId, $desiredStart, $desiredEnd]);
        return ['success' => true, 'waitlist_id' => $this->db->lastInsertId()];
    }

    // ─── NON-CRUD: Core Send Method ──────────────────────────

    public function send(int $userId, string $type, string $channel, string $title, string $message): int {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (user_id, type, channel, title, message)
            VALUES (?,?,?,?,?)
        ");
        $stmt->execute([$userId, $type, $channel, $title, $message]);
        $notifId = (int)$this->db->lastInsertId();

        // Simulate actual email sending
        if ($channel === 'email') {
            $this->simulateSendEmail($userId, $title, $message);
        }
        return $notifId;
    }

    private function simulateSendEmail(int $userId, string $subject, string $body): void {
        // In production: use PHPMailer or similar
        // Simulation: log to audit
        $stmt = $this->db->prepare("INSERT INTO audit_log (user_id, action, target_table, new_value) VALUES (?,?,?,?)");
        $stmt->execute([$userId, 'EMAIL_SENT', 'notifications', json_encode(['subject' => $subject])]);
    }

    // ─── NON-CRUD: P2P Encrypted Messaging ───────────────────

    public function sendMessage(int $reservationId, int $senderId, int $receiverId, string $text): array {
        // Verify both parties belong to this reservation
        $stmt = $this->db->prepare("
            SELECT r.driver_id, s.owner_id 
            FROM reservations r
            JOIN parking_spots s ON r.spot_id = s.spot_id
            WHERE r.reservation_id = ?
        ");
        $stmt->execute([$reservationId]);
        $res = $stmt->fetch();

        if (!$res) return ['success' => false, 'message' => 'Reservation not found.'];
        $allowed = [(int)$res['driver_id'], (int)$res['owner_id']];
        if (!in_array($senderId, $allowed) || !in_array($receiverId, $allowed)) {
            return ['success' => false, 'message' => 'Unauthorized to message in this reservation.'];
        }

        // Simulate encryption: store as base64 (real: use libsodium)
        $encrypted = base64_encode($text);
        $stmt = $this->db->prepare("INSERT INTO messages (reservation_id, sender_id, receiver_id, message_text) VALUES (?,?,?,?)");
        $stmt->execute([$reservationId, $senderId, $receiverId, $encrypted]);

        return ['success' => true, 'message_id' => $this->db->lastInsertId()];
    }

    public function getMessages(int $reservationId, int $userId): array {
        $stmt = $this->db->prepare("
            SELECT m.*, u.full_name AS sender_name
            FROM messages m
            JOIN users u ON m.sender_id = u.user_id
            WHERE m.reservation_id = ?
              AND (m.sender_id = ? OR m.receiver_id = ?)
            ORDER BY m.sent_at ASC
        ");
        $stmt->execute([$reservationId, $userId, $userId]);
        $messages = $stmt->fetchAll();

        // Decrypt messages
        foreach ($messages as &$msg) {
            $msg['message_text'] = base64_decode($msg['message_text']);
        }
        return $messages;
    }
}
