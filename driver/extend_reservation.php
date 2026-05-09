<?php
// driver/extend_reservation.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/Reservation.php';

$pageTitle = 'Extend Reservation — Rakna';
$user      = currentUser();
$resObj    = new Reservation();
$id        = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $extra  = (int)($_POST['extra_minutes'] ?? 30);
    $result = $resObj->extendReservation($id, $extra, $user['user_id']);
    setFlash($result['success'] ? 'success' : 'error', $result['message']);
    header('Location: /parking_system/index.php?action=my_reservations'); exit;
}

require_once __DIR__ . '/../includes/header.php';
?>
<style>
/* ألوان Rakna */
.btn-primary {
    background-color: #480959;
    border-color: #480959;
}
.btn-primary:hover {
    background-color: #8A2888;
    border-color: #8A2888;
}
.card-header {
    background-color: #480959;
    color: #fff;
    font-weight: bold;
}
</style>

<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card">
        <div class="card-header"><i class="bi bi-hourglass-split me-1"></i> Extend Parking Session</div>
        <div class="card-body">
          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Extend by how many minutes?</label>
              <select name="extra_minutes" class="form-select form-select-lg">
                <option value="30">30 minutes</option>
                <option value="60">1 hour</option>
                <option value="90">1.5 hours</option>
                <option value="120">2 hours</option>
                <option value="180">3 hours</option>
              </select>
            </div>
            <p class="text-muted small">Extension charges will be added at the standard hourly rate. Only possible if the next time slot is available.</p>
            <button type="submit" class="btn btn-primary w-100">Confirm Extension</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>