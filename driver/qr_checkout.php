<?php
// driver/qr_checkout.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/Reservation.php';
require_once __DIR__ . '/../classes/Payment.php';
require_once __DIR__ . '/../classes/Pricing.php';   // للتحويل إلى العملة المفضلة

$user       = currentUser();
$resObj     = new Reservation();
$paymentObj = new Payment();
$pricingObj = new Pricing();
$resId      = (int)($_GET['id'] ?? 0);
$res        = $resObj->getReservationById($resId);

if (!$res || $res['driver_id'] != $user['user_id']) {
    setFlash('error', 'Reservation not found.');
    header('Location: /parking_system/index.php?action=my_reservations');
    exit;
}

// تنفيذ Check-out (State Machine)
$result = $resObj->qrCheckOut($res['qr_code']);

// جلب تفاصيل الدفع من جدول transactions
$payment = $paymentObj->getPaymentByReservation($resId);

// العملة المفضلة للمستخدم
$prefCurrency = $pricingObj->getUserPreferredCurrency($user['user_id']);
$convertedPayment = null;
if ($prefCurrency !== 'EGP' && $payment) {
    $convertedPayment = $pricingObj->convertCurrency($payment['amount'], $prefCurrency);
}

$pageTitle = 'Checkout — Rakna';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
/* ألوان Rakna */
.checkout-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    padding: 2rem;
}
.checkout-card i {
    color: #480959;
}
.btn-primary {
    background-color: #480959;
    border-color: #480959;
}
.btn-primary:hover {
    background-color: #8A2888;
    border-color: #8A2888;
}
.text-primary {
    color: #480959 !important;
}
.badge.bg-warning {
    background-color: #480959 !important;
}
</style>

<div class="container-fluid px-0">
<div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-box-arrow-right me-2"></i>Checkout</h4>
    <a href="/parking_system/index.php?action=my_reservations" class="btn btn-outline-secondary btn-sm">← Back to Reservations</a>
  </div>

  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="checkout-card">

        <?php if ($result['success']): ?>
          <div class="text-center mb-4">
            <i class="bi bi-check-circle fs-1"></i>
            <h5 class="mt-2">Parking Session Completed</h5>
            <p class="text-muted">Thank you for using Rakna!</p>
          </div>

          <ul class="list-group list-group-flush mb-4">
            <li class="list-group-item d-flex justify-content-between">
              <span><i class="bi bi-geo-alt me-2"></i>Spot</span>
              <strong><?= htmlspecialchars($res['spot_title'] ?? 'N/A') ?></strong>
            </li>
            <li class="list-group-item d-flex justify-content-between">
              <span><i class="bi bi-clock me-2"></i>Start</span>
              <strong><?= date('M d, Y h:i A', strtotime($res['start_time'])) ?></strong>
            </li>
            <li class="list-group-item d-flex justify-content-between">
              <span><i class="bi bi-clock-fill me-2"></i>End</span>
              <strong><?= date('M d, Y h:i A', strtotime($res['end_time'])) ?></strong>
            </li>
            <?php if ($result['overstay_minutes'] > 0): ?>
            <li class="list-group-item d-flex justify-content-between text-danger">
              <span><i class="bi bi-exclamation-triangle me-2"></i>Overstay</span>
              <strong><?= $result['overstay_minutes'] ?> min</strong>
            </li>
            <?php endif; ?>
          </ul>

          <?php if ($payment): ?>
          <div class="bg-light p-3 rounded">
            <h6 class="fw-bold mb-3"><i class="bi bi-wallet2 me-1"></i>Payment Summary</h6>
            <div class="d-flex justify-content-between">
              <span>Total Paid</span>
              <strong><?= number_format($payment['amount'], 2) ?> EGP</strong>
            </div>
            <?php if ($convertedPayment && $convertedPayment['success']): ?>
            <div class="d-flex justify-content-between small text-primary">
              <span><i class="bi bi-currency-exchange me-1"></i>Amount in <?= $prefCurrency ?></span>
              <strong><?= number_format($convertedPayment['converted'], 2) ?> <?= $prefCurrency ?></strong>
            </div>
            <?php endif; ?>
            <div class="d-flex justify-content-between small text-muted">
              <span>Platform Fee (<?= round($payment['platform_fee'] * 100 / $payment['amount'], 1) ?>%)</span>
              <span><?= number_format($payment['platform_fee'], 2) ?> EGP</span>
            </div>
            <div class="d-flex justify-content-between small text-muted">
              <span>VAT</span>
              <span><?= number_format($payment['tax_amount'] ?? 0, 2) ?> EGP</span>
            </div>
            <div class="d-flex justify-content-between small text-muted">
              <span>Owner Earnings</span>
              <span><?= number_format($payment['owner_earnings'] ?? 0, 2) ?> EGP</span>
            </div>
            <?php if ($result['penalty_amount'] > 0): ?>
            <hr>
            <div class="d-flex justify-content-between text-danger">
              <span>Overstay Penalty</span>
              <strong><?= number_format($result['penalty_amount'], 2) ?> EGP</strong>
            </div>
            <?php endif; ?>
          </div>
          <?php else: ?>
            <div class="alert alert-warning"><i class="bi bi-info-circle me-1"></i> Payment details not available.</div>
          <?php endif; ?>

          <div class="text-center mt-4">
            <a href="/parking_system/index.php?action=my_reservations" class="btn btn-primary">
              <i class="bi bi-list-ul me-1"></i> My Reservations
            </a>
          </div>

        <?php else: ?>
          <div class="text-center">
            <i class="bi bi-x-circle fs-1 text-danger"></i>
            <h5 class="mt-2">Checkout Failed</h5>
            <p class="text-muted"><?= htmlspecialchars($result['message']) ?></p>
            <a href="/parking_system/index.php?action=my_reservations" class="btn btn-primary mt-3">
              ← Back
            </a>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>