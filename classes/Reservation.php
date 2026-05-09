<?php
// classes/Reservation.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/Pricing.php';

class Reservation {
    private PDO $db;
    private Pricing $pricing;
    private const BUFFER_MINUTES   = 10;  // Buffer between bookings
    private const GRACE_MINUTES    = 5;   // Late arrival grace period
    private const NOSHOW_THRESHOLD = 5;   // Minutes after start before no-show

    public function __construct() {
        $this->db      = getDB();
        $this->pricing = new Pricing();
    }

    // ─── CRUD ────────────────────────────────────────────────

    public function createReservation(array $data): array {
        $spotId    = (int)$data['spot_id'];
        $driverId  = (int)$data['driver_id'];
        $vehicleId = (int)$data['vehicle_id'];
        $start     = $data['start_time'];
        $end       = $data['end_time'];
        $promoCode = $data['promo_code'] ?? null;

        // 1. Validate time window
        if (strtotime($start) >= strtotime($end)) {
            return ['success' => false, 'message' => 'End time must be after start time.'];
        }
        if (strtotime($start) < time()) {
            return ['success' => false, 'message' => 'Cannot book in the past.'];
        }

        // 2. Apply buffer time: check slot + 10 min buffer
        $bufferedEnd = date('Y-m-d H:i:s', strtotime($end) + self::BUFFER_MINUTES * 60);
        if ($this->hasConflict($spotId, $start, $bufferedEnd)) {
            return ['success' => false, 'message' => 'Time slot unavailable (includes 10-min buffer between bookings).'];
        }

        // 3. Blind-spot check: spot must be visible
        $stmt = $this->db->prepare("SELECT status, is_verified FROM parking_spots WHERE spot_id = ?");
        $stmt->execute([$spotId]);
        $spot = $stmt->fetch();
        if (!$spot || $spot['status'] !== 'available' || !$spot['is_verified']) {
            return ['success' => false, 'message' => 'This parking spot is not available for booking.'];
        }

        // 4. Blacklist check
        $stmt = $this->db->prepare("SELECT is_blacklisted FROM users WHERE user_id = ?");
        $stmt->execute([$driverId]);
        if ($stmt->fetchColumn()) {
            return ['success' => false, 'message' => 'Your account is suspended. Please pay outstanding fines.'];
        }

        // 5. Calculate price
        $hours        = (strtotime($end) - strtotime($start)) / 3600;
        $pricing      = $this->pricing->calculateTotal($spotId, $start, $end, $driverId, $promoCode);
        $totalAmount  = $pricing['total'];
        $discount     = $pricing['discount'];

        // 6. Generate unique QR code
        $qrCode = $this->generateQRCode($driverId, $spotId, $start);

        // 7. Insert reservation
        $stmt = $this->db->prepare("
            INSERT INTO reservations 
            (driver_id, spot_id, vehicle_id, start_time, end_time, status, qr_code, total_amount, discount_amount, promo_code)
            VALUES (?,?,?,?,?,'confirmed',?,?,?,?)
        ");
        $stmt->execute([$driverId, $spotId, $vehicleId, $start, $end, $qrCode, $totalAmount, $discount, $promoCode]);
        $reservationId = (int)$this->db->lastInsertId();

        // 8. Lock payment in escrow
        $this->pricing->lockEscrow($reservationId, $driverId, $totalAmount);

        // 9. Add loyalty points (1 point per EGP spent)
        $stmt = $this->db->prepare("UPDATE users SET loyalty_points = loyalty_points + ? WHERE user_id = ?");
        $stmt->execute([(int)$totalAmount, $driverId]);

        return [
            'success'        => true,
            'reservation_id' => $reservationId,
            'qr_code'        => $qrCode,
            'total'          => $totalAmount,
            'pricing_detail' => $pricing,
        ];
    }

    public function getReservationById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT r.*, 
                   u.full_name AS driver_name, u.phone AS driver_phone,
                   s.title AS spot_title, s.address AS spot_address,
                   v.license_plate, v.make, v.model,
                   o.full_name AS owner_name
            FROM reservations r
            JOIN users u ON r.driver_id = u.user_id
            JOIN parking_spots s ON r.spot_id = s.spot_id
            JOIN vehicles v ON r.vehicle_id = v.vehicle_id
            JOIN users o ON s.owner_id = o.user_id
            WHERE r.reservation_id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function listDriverReservations(int $driverId, string $status = ''): array {
        $sql = "SELECT r.*, s.title AS spot_title, s.address, s.spot_number,
                       g.name AS garage_name
                FROM reservations r
                JOIN parking_spots s ON r.spot_id = s.spot_id
                LEFT JOIN garages g ON s.garage_id = g.garage_id
                WHERE r.driver_id = ?";
        $params = [$driverId];
        if ($status) { $sql .= " AND r.status = ?"; $params[] = $status; }
        $sql .= " ORDER BY r.start_time DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ─── NON-CRUD: QR Check-in / Check-out State Machine ─────

    /**
     * State machine: validates QR code on arrival.
     * States: confirmed → active → completed
     * 
     * Allow check-in:
     *   - Up to 5 min BEFORE start time
     *   - Any time AFTER start time (within grace period of 5 min)
     * No-show: only if > 5 min AFTER start time with no check-in
     */
    public function qrCheckIn(string $qrCode): array {
        $stmt = $this->db->prepare("SELECT * FROM reservations WHERE qr_code = ?");
        $stmt->execute([$qrCode]);
        $res = $stmt->fetch();

        if (!$res) {
            return ['success' => false, 'message' => 'Invalid QR code.'];
        }
        if ($res['status'] === 'active') {
            return ['success' => false, 'message' => 'Already checked in!'];
        }
        if ($res['status'] === 'completed') {
            return ['success' => false, 'message' => 'This reservation is already completed.'];
        }
        if ($res['status'] === 'cancelled') {
            return ['success' => false, 'message' => 'This reservation was cancelled.'];
        }
        if ($res['status'] !== 'confirmed') {
            return ['success' => false, 'message' => 'Cannot check in. Status: ' . $res['status']];
        }

        $now         = time();
        $startTime   = strtotime($res['start_time']);
        $minutesLate = ($now - $startTime) / 60; //양수 = بعد البداية، سالب = قبلها

        // Too early: أكتر من 5 دقايق قبل البداية
        if ($now < ($startTime - 5 * 60)) {
            $waitMins = round(($startTime - $now) / 60);
            return [
                'success' => false,
                'message' => "Too early! Check-in opens 5 minutes before your booking. Please wait {$waitMins} more minute(s)."
            ];
        }

        // No-show: أكتر من 5 دقايق بعد البداية من غير check-in
        if ($minutesLate > self::GRACE_MINUTES) {
            $stmt = $this->db->prepare("UPDATE reservations SET status = 'no_show' WHERE reservation_id = ?");
            $stmt->execute([$res['reservation_id']]);
            return [
                'success' => false,
                'message' => 'Booking marked as No-Show. Grace period of ' . self::GRACE_MINUTES . ' minutes has passed.'
            ];
        }

        // ✅ Check-in ناجح — في الوقت أو خلال الـ grace period
        $stmt = $this->db->prepare("
            UPDATE reservations 
            SET status = 'active', actual_checkin = NOW() 
            WHERE reservation_id = ?
        ");
        $stmt->execute([$res['reservation_id']]);

        $msg = $minutesLate > 0
            ? 'Check-in successful! ('. round($minutesLate) .' minute(s) late — within grace period)'
            : 'Check-in successful! Welcome.';

        return ['success' => true, 'message' => $msg, 'reservation' => $res];
    }

    public function qrCheckOut(string $qrCode): array {
        $stmt = $this->db->prepare("SELECT * FROM reservations WHERE qr_code = ?");
        $stmt->execute([$qrCode]);
        $res = $stmt->fetch();

        // Allow checkout for both 'active' AND 'extended' statuses
        if (!$res || !in_array($res['status'], ['active', 'extended'])) {
            return ['success' => false, 'message' => 'No active or extended reservation found for this QR code.'];
        }

        $now     = time();
        $endTime = strtotime($res['end_time']);

        // Overstay penalty
        $overstayMinutes = 0;
        $penaltyAmount   = 0;
        if ($now > $endTime) {
            $overstayMinutes = (int)(($now - $endTime) / 60);
            $penaltyAmount   = $this->pricing->calculateOverstayPenalty($res['spot_id'], $overstayMinutes);
        }

        $stmt = $this->db->prepare("UPDATE reservations SET status = 'completed', actual_checkout = NOW() WHERE reservation_id = ?");
        $stmt->execute([$res['reservation_id']]);

        // Issue fine if overstayed + send notification
        if ($overstayMinutes > 0) {
            $this->issueOverstayFine($res['driver_id'], $res['spot_id'], $res['reservation_id'], $overstayMinutes, $penaltyAmount);
            // Send overstay notification to driver
            require_once __DIR__ . '/Notification.php';
            $notif = new Notification();
            $notif->send(
                $res['driver_id'],
                'penalty_alert',
                'web',
                'Overstay Fine Issued',
                "You overstayed by {$overstayMinutes} minute(s). A fine of {$penaltyAmount} EGP has been added to your account."
            );
        }

        // Release escrow to owner
        $this->pricing->releaseEscrow($res['reservation_id']);

        return [
            'success'          => true,
            'message'          => 'Check-out successful!',
            'overstay_minutes' => $overstayMinutes,
            'penalty_amount'   => $penaltyAmount,
        ];
    }

    // ─── NON-CRUD: Cancellation with Tiered Refund ────────────

    /**
     * Calculates refund % based on how far cancellation is from booking start:
     * >2 hours before: 100% refund
     * 1-2 hours: 50% refund
     * <1 hour: 0% refund
     */
    public function cancelReservation(int $reservationId, int $driverId, string $reason = ''): array {
        $stmt = $this->db->prepare("SELECT * FROM reservations WHERE reservation_id = ? AND driver_id = ?");
        $stmt->execute([$reservationId, $driverId]);
        $res = $stmt->fetch();

        if (!$res) return ['success' => false, 'message' => 'Reservation not found.'];
        if (!in_array($res['status'], ['pending','confirmed'])) {
            return ['success' => false, 'message' => 'Cannot cancel: reservation is ' . $res['status']];
        }

        $hoursUntilStart = (strtotime($res['start_time']) - time()) / 3600;

        if ($hoursUntilStart > 2)     $refundPct = 1.00;  // 100%
        elseif ($hoursUntilStart > 1) $refundPct = 0.50;  // 50%
        else                          $refundPct = 0.00;  // 0%

        $refundAmount = round($res['total_amount'] * $refundPct, 2);

        $stmt = $this->db->prepare("
            UPDATE reservations 
            SET status = 'cancelled', cancellation_time = NOW(), cancellation_reason = ?, refund_amount = ?
            WHERE reservation_id = ?
        ");
        $stmt->execute([$reason, $refundAmount, $reservationId]);

        // Process refund
        if ($refundAmount > 0) {
            $this->pricing->processRefund($reservationId, $refundAmount);
        }

        return [
            'success'       => true,
            'refund_amount' => $refundAmount,
            'refund_pct'    => $refundPct * 100,
            'message'       => "Cancelled. Refund: {$refundAmount} EGP (" . ($refundPct*100) . "%)",
        ];
    }

    // ─── NON-CRUD: Instant Extension ─────────────────────────

    /**
     * Allows driver to extend stay if next slot is free.
     * Checks buffer time + no conflict.
     */
    public function extendReservation(int $reservationId, int $extraMinutes, int $driverId): array {
        $stmt = $this->db->prepare("SELECT * FROM reservations WHERE reservation_id = ? AND driver_id = ? AND status = 'active'");
        $stmt->execute([$reservationId, $driverId]);
        $res = $stmt->fetch();

        if (!$res) return ['success' => false, 'message' => 'No active reservation found.'];

        $newEnd = date('Y-m-d H:i:s', strtotime($res['end_time']) + $extraMinutes * 60);

        // Check if next slot is free (including buffer)
        $bufferedNewEnd = date('Y-m-d H:i:s', strtotime($newEnd) + self::BUFFER_MINUTES * 60);
        if ($this->hasConflictExcluding($res['spot_id'], $res['end_time'], $bufferedNewEnd, $reservationId)) {
            return ['success' => false, 'message' => 'Cannot extend: next time slot is already booked.'];
        }

        // Calculate extra charge
        $extraCost = $this->pricing->calculateExtensionCost($res['spot_id'], $extraMinutes);

        $stmt = $this->db->prepare("UPDATE reservations SET end_time = ?, status = 'extended', total_amount = total_amount + ? WHERE reservation_id = ?");
        $stmt->execute([$newEnd, $extraCost, $reservationId]);

        return [
            'success'    => true,
            'new_end'    => $newEnd,
            'extra_cost' => $extraCost,
            'message'    => "Extended by {$extraMinutes} minutes. Additional charge: {$extraCost} EGP",
        ];
    }

    // ─── NON-CRUD: Recurring / Subscription Booking ──────────

    /**
     * Creates recurring weekly reservations (Mon-Fri or custom days).
     * Bulk discount applied for weekly bookings.
     */
    public function createRecurringReservation(array $data, array $daysOfWeek, int $weeks = 4): array {
        $createdIds = [];
        $baseStart  = new DateTime($data['start_time']);
        $baseEnd    = new DateTime($data['end_time']);
        $discount   = 0.10; // 10% bulk discount for recurring

        $this->db->beginTransaction();
        try {
            for ($week = 0; $week < $weeks; $week++) {
                foreach ($daysOfWeek as $dayNum) {
                    $start = clone $baseStart;
                    $end   = clone $baseEnd;
                    $start->modify("+{$week} week");
                    $end->modify("+{$week} week");

                    // Adjust to correct day
                    $currentDay = (int)$start->format('N');
                    $diff       = $dayNum - $currentDay;
                    if ($diff !== 0) {
                        $start->modify("{$diff} days");
                        $end->modify("{$diff} days");
                    }

                    $data['start_time'] = $start->format('Y-m-d H:i:s');
                    $data['end_time']   = $end->format('Y-m-d H:i:s');
                    $data['promo_code'] = 'RECURRING_DISCOUNT';

                    $result = $this->createReservation($data);
                    if ($result['success']) {
                        $createdIds[] = $result['reservation_id'];
                    }
                }
            }
            $this->db->commit();
            return ['success' => true, 'created' => count($createdIds), 'reservation_ids' => $createdIds];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ─── NON-CRUD: No-Show Auto Check ────────────────────────

    /**
     * Cron-job-ready: marks confirmed reservations as no-show after grace period.
     */
    public function processNoShows(): int {
        $graceEnd = date('Y-m-d H:i:s', time() - self::GRACE_MINUTES * 60);
        $stmt     = $this->db->prepare("
            UPDATE reservations 
            SET status = 'no_show'
            WHERE status = 'confirmed'
              AND start_time < ?
              AND actual_checkin IS NULL
        ");
        $stmt->execute([$graceEnd]);
        return $stmt->rowCount();
    }

    // ─── Helpers ──────────────────────────────────────────────

    private function hasConflict(int $spotId, string $start, string $end): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM reservations
            WHERE spot_id = ? AND status IN ('confirmed','active','pending','extended')
              AND start_time < ? AND end_time > ?
        ");
        $stmt->execute([$spotId, $end, $start]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function hasConflictExcluding(int $spotId, string $start, string $end, int $excludeId): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM reservations
            WHERE spot_id = ? AND reservation_id != ?
              AND status IN ('confirmed','active','pending','extended')
              AND start_time < ? AND end_time > ?
        ");
        $stmt->execute([$spotId, $excludeId, $end, $start]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function generateQRCode(int $driverId, int $spotId, string $start): string {
        $raw = "PARK-{$driverId}-{$spotId}-" . strtotime($start) . '-' . bin2hex(random_bytes(8));
        return hash('sha256', $raw);
    }

    private function issueOverstayFine(int $driverId, int $spotId, int $reservationId, int $minutes, float $amount): void {
        $stmt = $this->db->prepare("INSERT INTO fines (driver_id, spot_id, reservation_id, fine_type, amount, overstay_minutes) VALUES (?,?,?,'overstay',?,?)");
        $stmt->execute([$driverId, $spotId, $reservationId, $amount, $minutes]);
        // Update driver's unpaid fines count
        $stmt = $this->db->prepare("UPDATE users SET unpaid_fines_count = unpaid_fines_count + 1 WHERE user_id = ?");
        $stmt->execute([$driverId]);
    }
}
