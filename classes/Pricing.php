<?php
// classes/Pricing.php
require_once __DIR__ . '/../config/db.php';

class Pricing {
    private PDO $db;
    private const PLATFORM_COMMISSION = 0.15; // 15% platform fee
    private const OVERSTAY_RATE       = 2.00; // EGP per minute overstay
    private const VAT_RATE            = 0.14; // 14% Egyptian VAT

    public function __construct() {
        $this->db = getDB();
    }

    // ─── NON-CRUD: Full Price Calculator ─────────────────────

    /**
     * Calculates total price including:
     * peak-hour multiplier, loyalty discount, promo code, VAT.
     */
    public function calculateTotal(int $spotId, string $start, string $end, int $driverId, ?string $promoCode, bool $incrementPromo = false): array {
        $stmt = $this->db->prepare("SELECT price_per_hour, base_price FROM parking_spots WHERE spot_id = ?");
        $stmt->execute([$spotId]);
        $spot = $stmt->fetch();

        $hours     = (strtotime($end) - strtotime($start)) / 3600;
        $baseRate  = (float)$spot['price_per_hour'];

        // 1. Apply peak-hour multiplier
        $multiplier = $this->getPeakMultiplier($spotId, $start);
        $priceAfterPeak = $baseRate * $multiplier * $hours;

        // 2. Loyalty discount
        $loyaltyDiscount = $this->getLoyaltyDiscountRate($driverId);
        $priceAfterLoyalty = $priceAfterPeak * (1 - $loyaltyDiscount);

        // 3. Promo code discount
        $promoDiscount = 0;
        $promoInfo     = null;
        if ($promoCode) {
            $promoInfo = $this->validatePromoCode($promoCode, $incrementPromo);
            if ($promoInfo['valid']) {
                if ($promoInfo['discount_type'] === 'percentage') {
                    $promoDiscount = $priceAfterLoyalty * ($promoInfo['discount_value'] / 100);
                } else {
                    $promoDiscount = $promoInfo['discount_value'];
                }
            }
        }
        $priceAfterPromo = max(0, $priceAfterLoyalty - $promoDiscount);

        // 4. VAT
        $vat   = $this->calculateVAT($priceAfterPromo);
        $total = $priceAfterPromo + $vat;

        return [
            'base_price'       => round($baseRate * $hours, 2),
            'peak_multiplier'  => $multiplier,
            'after_peak'       => round($priceAfterPeak, 2),
            'loyalty_discount' => round($loyaltyDiscount * 100, 1) . '%',
            'promo_discount'   => round($promoDiscount, 2),
            'discount'         => round($promoDiscount, 2),
            'subtotal'         => round($priceAfterPromo, 2),
            'vat'              => round($vat, 2),
            'total'            => round($total, 2),
        ];
    }

    // ─── NON-CRUD: Peak-Hour Pricing Engine ──────────────────

    /**
     * Returns price multiplier based on time-of-day rules.
     * Checks peak_hour_rules table for matching rules.
     */
    public function getPeakMultiplier(int $spotId, string $datetime): float {
        $time      = date('H:i:s', strtotime($datetime));
        $dayOfWeek = (int)date('w', strtotime($datetime)); // 0=Sun

        // Check spot-specific rules first, then global rules
        $stmt = $this->db->prepare("
            SELECT MAX(multiplier) as max_mult
            FROM peak_hour_rules
            WHERE (spot_id = ? OR spot_id IS NULL)
              AND (day_of_week = ? OR day_of_week IS NULL)
              AND start_time <= ?
              AND end_time   >= ?
        ");
        $stmt->execute([$spotId, $dayOfWeek, $time, $time]);
        $row = $stmt->fetch();
        return $row && $row['max_mult'] ? (float)$row['max_mult'] : 1.00;
    }

    // ─── NON-CRUD: Escrow System ──────────────────────────────

    /**
     * Locks driver payment in escrow until parking session confirmed complete.
     */
    public function lockEscrow(int $reservationId, int $payerId, float $amount): bool {
        $fee      = round($amount * self::PLATFORM_COMMISSION, 2);
        $earnings = round($amount - $fee, 2);
        $vat      = round($amount * self::VAT_RATE / (1 + self::VAT_RATE), 2);

        $stmt = $this->db->prepare("
            INSERT INTO transactions 
            (reservation_id, payer_id, amount, platform_fee, owner_earnings, tax_amount, payment_status)
            VALUES (?,?,?,?,?,?,'escrow')
        ");
        return $stmt->execute([$reservationId, $payerId, $amount, $fee, $earnings, $vat]);
    }

    /**
     * Releases escrow after successful checkout: splits into owner earnings + platform fee.
     */
    public function releaseEscrow(int $reservationId): bool {
        $stmt = $this->db->prepare("
            UPDATE transactions 
            SET payment_status = 'released', escrow_released_at = NOW()
            WHERE reservation_id = ? AND payment_status = 'escrow'
        ");
        $result = $stmt->execute([$reservationId]);

        // Check if owner should be paid out
        $stmt = $this->db->prepare("
            SELECT r.spot_id, s.owner_id, SUM(t.owner_earnings) AS total_pending
            FROM transactions t
            JOIN reservations r ON t.reservation_id = r.reservation_id
            JOIN parking_spots s ON r.spot_id = s.spot_id
            WHERE r.reservation_id = ?
            GROUP BY r.spot_id, s.owner_id
        ");
        $stmt->execute([$reservationId]);
        $row = $stmt->fetch();
        if ($row) {
            $this->checkPayoutThreshold((int)$row['owner_id']);
        }
        return $result;
    }

    // ─── NON-CRUD: Commission Split ───────────────────────────

    /**
     * Returns breakdown: platform fee vs owner net earnings for a transaction.
     */
    public function getCommissionSplit(float $amount): array {
        $platformFee    = round($amount * self::PLATFORM_COMMISSION, 2);
        $ownerEarnings  = round($amount - $platformFee, 2);
        $vat            = round($amount * self::VAT_RATE, 2);
        return [
            'gross_amount'   => $amount,
            'platform_fee'   => $platformFee,
            'platform_pct'   => self::PLATFORM_COMMISSION * 100 . '%',
            'owner_earnings' => $ownerEarnings,
            'vat'            => $vat,
        ];
    }

    // ─── NON-CRUD: Overstay Penalty Calculator ────────────────

    /**
     * Every minute beyond reserved time is charged at penalty rate.
     * Penalty rate = 2x the normal per-minute price.
     */
    public function calculateOverstayPenalty(int $spotId, int $overstayMinutes): float {
        $stmt = $this->db->prepare("SELECT price_per_hour FROM parking_spots WHERE spot_id = ?");
        $stmt->execute([$spotId]);
        $hourlyRate    = (float)$stmt->fetchColumn();
        $perMinuteRate = $hourlyRate / 60;
        $penaltyRate   = $perMinuteRate * 2; // 2x penalty multiplier
        return round($penaltyRate * $overstayMinutes, 2);
    }

    // ─── NON-CRUD: Promo Code Validator ──────────────────────

    /**
     * Validates promo code: checks expiry, usage limit, active status.
     * Ensures discount doesn't apply to expired codes mid-session.
     *
     * @param bool $incrementUsage  Set true ONLY when actually confirming a booking,
     *                              false for price previews (default: false for safety).
     */
    public function validatePromoCode(string $code, bool $incrementUsage = false): array {
        $stmt = $this->db->prepare("
            SELECT * FROM promo_codes
            WHERE code = ?
              AND is_active = 1
              AND valid_from  <= NOW()
              AND valid_until >= NOW()
              AND current_uses < max_uses
        ");
        $stmt->execute([$code]);
        $promo = $stmt->fetch();

        if (!$promo) {
            return ['valid' => false, 'message' => 'Invalid, expired, or fully used promo code.'];
        }

        // Only increment usage when actually applying (not during preview)
        if ($incrementUsage) {
            $stmt = $this->db->prepare("UPDATE promo_codes SET current_uses = current_uses + 1 WHERE promo_id = ?");
            $stmt->execute([$promo['promo_id']]);
        }

        return [
            'valid'          => true,
            'discount_type'  => $promo['discount_type'],
            'discount_value' => $promo['discount_value'],
            'message'        => 'Promo code applied!',
        ];
    }

    // ─── NON-CRUD: VAT / Tax Simulation ──────────────────────

    /**
     * Simulates Egyptian VAT (14%) calculation.
     */
    public function calculateVAT(float $subtotal): float {
        return round($subtotal * self::VAT_RATE, 2);
    }

    // ─── NON-CRUD: Refund & Dispute Reconciliation ────────────

    public function processRefund(int $reservationId, float $amount): bool {
        $stmt = $this->db->prepare("
            UPDATE transactions 
            SET payment_status = 'refunded'
            WHERE reservation_id = ? AND payment_status IN ('escrow','released')
        ");
        return $stmt->execute([$reservationId]);
    }

    // ─── NON-CRUD: Owner Payout Scheduling ───────────────────

    /**
     * Aggregates weekly earnings and initiates payout once minimum threshold (500 EGP) is met.
     */
    public function checkPayoutThreshold(int $ownerId, float $threshold = 500.00): bool {
        $stmt = $this->db->prepare("
            SELECT SUM(t.owner_earnings) AS total
            FROM transactions t
            JOIN reservations r ON t.reservation_id = r.reservation_id
            JOIN parking_spots s ON r.spot_id = s.spot_id
            WHERE s.owner_id = ?
              AND t.payment_status = 'released'
              AND NOT EXISTS (
                SELECT 1 FROM owner_payouts op
                WHERE op.owner_id = ? AND op.status = 'pending'
              )
        ");
        $stmt->execute([$ownerId, $ownerId]);
        $total = (float)$stmt->fetchColumn();

        if ($total >= $threshold) {
            $stmt = $this->db->prepare("
                INSERT INTO owner_payouts (owner_id, amount, period_start, period_end)
                VALUES (?, ?, DATE_SUB(NOW(), INTERVAL 1 WEEK), NOW())
            ");
            return $stmt->execute([$ownerId, $total]);
        }
        return false;
    }

    // ─── NON-CRUD: Multi-Currency Converter ──────────────────

    /**
     * Simulates currency conversion for international travelers.
     * Uses fixed simulation rates (in real system: fetch from API).
     */
    public function convertCurrency(float $amountEGP, string $targetCurrency): array {
        $rates = [
            'USD' => 0.032,
            'EUR' => 0.029,
            'GBP' => 0.025,
            'SAR' => 0.120,
            'AED' => 0.117,
            'EGP' => 1.000,
        ];
        if (!isset($rates[$targetCurrency])) {
            return ['success' => false, 'message' => 'Currency not supported.'];
        }
        $converted = round($amountEGP * $rates[$targetCurrency], 2);
        return [
            'success'         => true,
            'original_egp'    => $amountEGP,
            'converted'       => $converted,
            'currency'        => $targetCurrency,
            'rate'            => $rates[$targetCurrency],
        ];
    }

    // ─── NEW: الحصول على العملة المفضلة للمستخدم ─────────────

    /**
     * Returns the preferred currency of a given user.
     * Useful for displaying converted amounts in checkout or invoices.
     */
    public function getUserPreferredCurrency(int $userId): string {
        $stmt = $this->db->prepare("SELECT preferred_currency FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() ?: 'EGP';
    }

    // ─── NON-CRUD: Extension Cost ─────────────────────────────

    public function calculateExtensionCost(int $spotId, int $extraMinutes): float {
        $stmt = $this->db->prepare("SELECT price_per_hour FROM parking_spots WHERE spot_id = ?");
        $stmt->execute([$spotId]);
        $hourly = (float)$stmt->fetchColumn();
        return round(($hourly / 60) * $extraMinutes, 2);
    }

    // ─── Helper ───────────────────────────────────────────────

    private function getLoyaltyDiscountRate(int $userId): float {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM reservations 
            WHERE driver_id = ? AND status = 'completed'
              AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
        ");
        $stmt->execute([$userId]);
        $count = (int)$stmt->fetchColumn();
        if ($count >= 20) return 0.20;
        if ($count >= 10) return 0.10;
        if ($count >= 5)  return 0.05;
        return 0.00;
    }
}