<?php
// driver/qr_checkin.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/Reservation.php';

$pageTitle = 'QR Check-In — Rakna';
$user      = currentUser();
$resObj    = new Reservation();
$resId     = (int)($_GET['id'] ?? 0);
$res       = $resObj->getReservationById($resId);

if (!$res || $res['driver_id'] != $user['user_id']) {
    setFlash('error', 'Reservation not found.');
    header('Location: /parking_system/index.php?action=my_reservations'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $resObj->qrCheckIn($res['qr_code']);
    setFlash($result['success'] ? 'success' : 'error', $result['message']);
    header('Location: /parking_system/index.php?action=my_reservations'); exit;
}

require_once __DIR__ . '/../includes/header.php';
?>
<style>
/* ألوان Rakna */
.btn-success {
    background-color: #480959;
    border-color: #480959;
}
.btn-success:hover {
    background-color: #8A2888;
    border-color: #8A2888;
}
.badge.bg-primary {
    background-color: #480959 !important;
    color: #fff;
}
.text-primary {
    color: #480959 !important;
}
</style>

<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <div class="row justify-content-center">
    <div class="col-md-5 text-center">
      <div class="card">
        <div class="card-body py-5">
          <div style="font-size:5rem;">
            <i class="bi bi-qr-code-scan"></i>
          </div>
          <h4 class="fw-bold mt-3"><?= htmlspecialchars($res['spot_title']) ?></h4>
          <p class="text-muted"><?= htmlspecialchars($res['spot_address']) ?></p>
          <hr>
          <div class="mb-3">
            <span class="badge bg-primary fs-6 px-4 py-2 font-monospace"><?= htmlspecialchars(substr($res['qr_code'],0,16)) ?>…</span>
          </div>
          <p class="small text-muted">Start: <strong><?= date('h:i A', strtotime($res['start_time'])) ?></strong> &nbsp;|&nbsp; End: <strong><?= date('h:i A', strtotime($res['end_time'])) ?></strong></p>
          <p class="small text-muted">License Plate: <strong><?= htmlspecialchars($res['license_plate']) ?></strong></p>
          <form method="POST">
            <button type="submit" class="btn btn-success btn-lg px-5">
              <i class="bi bi-check-circle me-2"></i>Confirm Check-In
            </button>
          </form>
          <a href="/parking_system/index.php?action=my_reservations" class="btn btn-link mt-2 text-primary">Back</a>
        </div>
      </div>
    </div>
  </div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>