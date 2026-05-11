<?php
// driver/cancel_reservation.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/Reservation.php';

$user   = currentUser();
$resObj = new Reservation();
$id     = (int)($_GET['id'] ?? 0);

if (!$id) {
    setFlash('error', 'Invalid reservation.');
    header('Location: ' . BASE_URL . '/index.php?action=my_reservations');
    exit;
}

// Verify ownership before showing form
$res = $resObj->getReservationById($id);
if (!$res || $res['driver_id'] != $user['user_id']) {
    setFlash('error', 'Reservation not found.');
    header('Location: ' . BASE_URL . '/index.php?action=my_reservations');
    exit;
}

if (!in_array($res['status'], ['confirmed', 'pending'])) {
    setFlash('error', 'Only confirmed or pending reservations can be cancelled.');
    header('Location: ' . BASE_URL . '/index.php?action=my_reservations');
    exit;
}

// Calculate refund preview
$hoursUntilStart = (strtotime($res['start_time']) - time()) / 3600;
if ($hoursUntilStart > 2)     { $refundPct = 100; }
elseif ($hoursUntilStart > 1) { $refundPct = 50;  }
else                          { $refundPct = 0;   }
$refundPreview = round((float)$res['total_amount'] * ($refundPct / 100), 2);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = trim($_POST['reason'] ?? '');
    $result = $resObj->cancelReservation($id, $user['user_id'], $reason);
    setFlash($result['success'] ? 'success' : 'error', $result['message']);
    header('Location: ' . BASE_URL . '/index.php?action=my_reservations');
    exit;
}

$pageTitle = 'Cancel Reservation — Rakna';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.btn-primary  { background-color:#480959; border-color:#480959; }
.btn-primary:hover { background-color:#8A2888; border-color:#8A2888; }
.btn-danger   { background-color:#dc3545; border-color:#dc3545; }
.card-header  { background-color:#480959; color:#fff; font-weight:bold; }
.text-primary { color:#480959 !important; }
</style>

<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <div class="row justify-content-center">
    <div class="col-md-6">

      <div class="card shadow">
        <div class="card-header">
          <i class="bi bi-x-circle me-1"></i> Cancel Reservation
        </div>
        <div class="card-body p-4">

          <!-- Booking summary -->
          <div class="bg-light rounded p-3 mb-4">
            <h6 class="fw-bold mb-2"><?= htmlspecialchars($res['spot_title']) ?></h6>
            <p class="mb-1 small text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($res['spot_address']) ?></p>
            <p class="mb-1 small"><strong>Check-in:</strong> <?= date('M d, Y h:i A', strtotime($res['start_time'])) ?></p>
            <p class="mb-0 small"><strong>Check-out:</strong> <?= date('M d, Y h:i A', strtotime($res['end_time'])) ?></p>
          </div>

          <!-- Refund policy -->
          <div class="alert alert-<?= $refundPct > 0 ? 'info' : 'warning' ?> mb-4">
            <strong>Refund Estimate:</strong>
            <?php if ($refundPct === 100): ?>
              <span class="text-success">Full refund — <?= number_format($refundPreview, 2) ?> EGP</span>
              <small class="d-block text-muted mt-1">Cancelling more than 2 hours before check-in.</small>
            <?php elseif ($refundPct === 50): ?>
              <span class="text-warning">50% refund — <?= number_format($refundPreview, 2) ?> EGP</span>
              <small class="d-block text-muted mt-1">Cancelling 1–2 hours before check-in.</small>
            <?php else: ?>
              <span class="text-danger">No refund (0 EGP)</span>
              <small class="d-block text-muted mt-1">Cancellation within 1 hour of check-in.</small>
            <?php endif; ?>
          </div>

          <!-- Confirm form -->
          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Reason (optional)</label>
              <textarea name="reason" class="form-control" rows="2"
                        placeholder="e.g. Change of plans, found alternative..."></textarea>
            </div>
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-danger flex-fill">
                <i class="bi bi-x-circle me-1"></i> Confirm Cancellation
              </button>
              <a href="<?= BASE_URL ?>/index.php?action=my_reservations" class="btn btn-outline-secondary flex-fill">
                Keep Booking
              </a>
            </div>
          </form>

        </div>
      </div>

    </div>
  </div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
