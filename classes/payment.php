<?php
// classes/Payment.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/Pricing.php';

class Payment {
    private PDO $db;
    private Pricing $pricing;

    public function __construct() {
        $this->db      = getDB();
        $this->pricing = new Pricing();
    }

    /**
     * جلب سجل الدفعة (المعاملة) من جدول transactions
     */
    public function getPaymentByReservation(int $reservationId): ?array {
        $stmt = $this->db->prepare("
            SELECT * FROM transactions 
            WHERE reservation_id = ? 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$reservationId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * إعادة توجيه لحساب غرامة التجاوز من Pricing
     */
    public function calculateOverstayPenalty(int $spotId, int $overstayMinutes): float {
        return $this->pricing->calculateOverstayPenalty($spotId, $overstayMinutes);
    }

    /**
     * تحرير الإسكرو بعد الخروج
     */
    public function releaseEscrow(int $reservationId): bool {
        return $this->pricing->releaseEscrow($reservationId);
    }
}