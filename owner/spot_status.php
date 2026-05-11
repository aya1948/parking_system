<?php
// owner/spot_status.php — Change spot availability status
require_once __DIR__ . '/../config/session.php';
requireRole('owner');
require_once __DIR__ . '/../classes/ParkingSpot.php';

$pageTitle = 'Spot Status — Rakna';
$user      = currentUser();
$spotObj   = new ParkingSpot();
$spotId    = (int)($_GET['id'] ?? 0);
$spot      = $spotObj->getSpotById($spotId);

if (!$spot || $spot['owner_id'] != $user['user_id']) {
    setFlash('error', 'Spot not found.'); header('Location: /parking_system/index.php?action=my_spots'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $spotObj->setSpotStatus($spotId, $_POST['status'], $user['user_id']);
    setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Status updated.' : $result['message']);
    header('Location: /parking_system/index.php?action=my_spots'); exit;
}

require_once __DIR__ . '/../includes/header.php';
?>
<style>
.btn-primary { background-color:#480959; border-color:#480959; }
.btn-primary:hover { background-color:#8A2888; }
</style>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card">
        <div class="card-header fw-bold"><i class="bi bi-arrow-repeat me-1"></i> Change Spot Status</div>
        <div class="card-body">
          <h6><?= htmlspecialchars($spot['title']) ?></h6>
          <p class="text-muted small"><?= htmlspecialchars($spot['address']) ?></p>
          <hr>
          <form method="POST">
            <div class="mb-4">
              <label class="form-label fw-semibold">New Status</label>
              <?php
              $statuses = [
                'available'   => ['success',   '<i class="bi bi-check-circle me-1"></i> Available'],
                'unavailable' => ['secondary', '<i class="bi bi-lock me-1"></i> Unavailable'],
                'maintenance' => ['warning',   '<i class="bi bi-tools me-1"></i> Maintenance'],
                'owner_use'   => ['info',      '<i class="bi bi-house-door me-1"></i> Owner Use'],
              ];
              foreach ($statuses as $s => [$c, $label]): ?>
              <div class="form-check mb-2">
                <input class="form-check-input" type="radio" name="status" value="<?= $s ?>" id="s_<?= $s ?>"
                  <?= $spot['status'] === $s ? 'checked' : '' ?>>
                <label class="form-check-label" for="s_<?= $s ?>">
                  <span class="badge bg-<?= $c ?>"><?= $label ?></span>
                </label>
              </div>
              <?php endforeach; ?>
            </div>
            <div class="alert alert-warning small">
              <i class="bi bi-exclamation-triangle me-1"></i>
              Setting to Maintenance or Owner Use will fail if there are upcoming reservations.
            </div>
            <button type="submit" class="btn btn-primary w-100">Update Status</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>