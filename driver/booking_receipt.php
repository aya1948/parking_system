<?php
// driver/booking_receipt.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/Reservation.php';
require_once __DIR__ . '/../config/db.php';

$pageTitle     = 'Booking Receipt — CitySlot';
$user          = currentUser();
$b             = BASE_URL;
$reservationId = (int)($_GET['id'] ?? 0);
$resObj        = new Reservation();
$res           = $resObj->getReservationById($reservationId);

if (!$res || $res['driver_id'] != $user['user_id']) {
    setFlash('error', 'Receipt not found.');
    header("Location: $b/index.php?action=my_reservations"); exit;
}

// Get transaction details
$db   = getDB();
$stmt = $db->prepare("SELECT * FROM transactions WHERE reservation_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$reservationId]);
$txn  = $stmt->fetch();

$hours       = round((strtotime($res['end_time']) - strtotime($res['start_time'])) / 3600, 2);
$receiptNo   = 'RCP-' . str_pad($reservationId, 6, '0', STR_PAD_LEFT);
$receiptDate = date('Y-m-d H:i:s');

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <div class="row justify-content-center">
    <div class="col-md-7">

      <!-- RECEIPT CARD -->
      <div class="card shadow" id="receiptCard">
        <!-- HEADER -->
        <div class="card-header text-center py-4"
             style="background:linear-gradient(135deg,#0d1b2a,#1a73e8);color:white;">
          <div style="font-size:2.5rem;">🅿️</div>
          <h4 class="fw-bold mb-1 mt-2">CitySlot Parking</h4>
          <p class="mb-0 opacity-75 small">Official Booking Receipt</p>
        </div>

        <div class="card-body px-4 py-4">
          <!-- RECEIPT META -->
          <div class="d-flex justify-content-between mb-4">
            <div>
              <small class="text-muted d-block">Receipt No.</small>
              <strong class="font-monospace"><?= $receiptNo ?></strong>
            </div>
            <div class="text-end">
              <small class="text-muted d-block">Date</small>
              <strong><?= date('M d, Y', strtotime($receiptDate)) ?></strong>
            </div>
          </div>

          <hr>

          <!-- DRIVER INFO -->
          <h6 class="text-muted small fw-bold text-uppercase mb-2">Driver</h6>
          <p class="mb-1"><strong><?= htmlspecialchars($res['driver_name']) ?></strong></p>
          <p class="mb-3 small text-muted"><?= htmlspecialchars($res['license_plate']) ?> — <?= htmlspecialchars($res['make'].' '.$res['model']) ?></p>

          <hr>

          <!-- SPOT INFO -->
          <h6 class="text-muted small fw-bold text-uppercase mb-2">Parking Spot</h6>
          <p class="mb-1"><strong><?= htmlspecialchars($res['spot_title']) ?></strong></p>
          <p class="mb-3 small text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($res['spot_address']) ?></p>

          <hr>

          <!-- TIME INFO -->
          <h6 class="text-muted small fw-bold text-uppercase mb-2">Booking Time</h6>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <div class="bg-light rounded p-2 text-center">
                <small class="text-muted d-block">Check-in</small>
                <strong><?= date('M d, h:i A', strtotime($res['start_time'])) ?></strong>
              </div>
            </div>
            <div class="col-6">
              <div class="bg-light rounded p-2 text-center">
                <small class="text-muted d-block">Check-out</small>
                <strong><?= date('M d, h:i A', strtotime($res['end_time'])) ?></strong>
              </div>
            </div>
          </div>
          <p class="text-muted small text-center mb-3">Duration: <strong><?= $hours ?> hour(s)</strong></p>

          <hr>

          <!-- PRICE BREAKDOWN -->
          <h6 class="text-muted small fw-bold text-uppercase mb-3">Price Breakdown</h6>

          <?php
          $grossAmount    = $txn ? (float)$txn['amount'] : (float)$res['total_amount'];
          $vatAmount      = $txn ? (float)$txn['tax_amount'] : 0;
          $subtotal       = $grossAmount - $vatAmount;
          $discountAmount = (float)($res['discount_amount'] ?? 0);
          $baseAmount     = $subtotal + $discountAmount;
          ?>

          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Base Price</span>
            <span><?= number_format($baseAmount, 2) ?> EGP</span>
          </div>

          <?php if ($discountAmount > 0): ?>
          <div class="d-flex justify-content-between mb-2 text-success">
            <span>
              Discount
              <?php if ($res['promo_code']): ?>
                <span class="badge bg-success ms-1"><?= htmlspecialchars($res['promo_code']) ?></span>
              <?php endif; ?>
            </span>
            <span>- <?= number_format($discountAmount, 2) ?> EGP</span>
          </div>
          <?php endif; ?>

          <div class="d-flex justify-content-between mb-2 text-muted">
            <span>Subtotal (before tax)</span>
            <span><?= number_format($subtotal, 2) ?> EGP</span>
          </div>

          <div class="d-flex justify-content-between mb-3 text-muted">
            <span>VAT (14%)</span>
            <span><?= number_format($vatAmount, 2) ?> EGP</span>
          </div>

          <hr>

          <div class="d-flex justify-content-between mb-2">
            <span class="fw-bold fs-5">Total Paid</span>
            <span class="fw-bold fs-5 text-primary"><?= number_format($grossAmount, 2) ?> EGP</span>
          </div>

          <div class="d-flex justify-content-between mb-3">
            <small class="text-muted">Payment Method</small>
            <small class="badge bg-secondary"><?= strtoupper($txn['payment_method'] ?? 'CARD') ?></small>
          </div>

          <div class="d-flex justify-content-between">
            <small class="text-muted">Payment Status</small>
            <small class="badge bg-<?= ($txn['payment_status']??'') === 'released' ? 'success' : 'warning' ?>">
              <?= strtoupper($txn['payment_status'] ?? 'ESCROW') ?>
            </small>
          </div>

          <hr>

          <!-- QR CODE PLACEHOLDER -->
          <div class="text-center py-3">
            <div class="bg-light rounded p-3 d-inline-block">
              <div class="font-monospace small text-muted mb-1">Booking QR Code</div>
              <div style="font-size:3rem;">▦</div>
              <div class="font-monospace small mt-1"><?= htmlspecialchars(substr($res['qr_code'],0,20)) ?>...</div>
            </div>
          </div>

          <hr>

          <!-- STATUS -->
          <div class="text-center">
            <?php
            $bc = ['confirmed'=>'warning','active'=>'success','completed'=>'primary','cancelled'=>'danger'][$res['status']] ?? 'secondary';
            ?>
            <span class="badge bg-<?= $bc ?> fs-6 px-4 py-2">
              <?= strtoupper(str_replace('_',' ',$res['status'])) ?>
            </span>
          </div>

        </div>

        <!-- FOOTER -->
        <div class="card-footer text-center text-muted small py-3"
             style="background:#f8f9fa;">
          <p class="mb-1">Thank you for using CitySlot! 🅿️</p>
          <p class="mb-0">Support: support@cityslot.com | This is an official receipt.</p>
        </div>
      </div>

      <!-- ACTIONS -->
      <div class="d-flex gap-3 mt-3 justify-content-center">
        <button onclick="window.print()" class="btn btn-outline-secondary">
          <i class="bi bi-printer me-1"></i>Print Receipt
        </button>
        <a href="<?= $b ?>/index.php?action=my_reservations" class="btn btn-primary">
          <i class="bi bi-calendar-check me-1"></i>My Reservations
        </a>
      </div>

    </div>
  </div>
</div></div></div>

<style>
@media print {
  .sidebar, nav, .btn, .card-footer { display: none !important; }
  #receiptCard { box-shadow: none !important; border: 1px solid #ddd !important; }
}
</style>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
