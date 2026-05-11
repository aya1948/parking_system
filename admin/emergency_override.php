<?php
// admin/emergency_override.php
require_once __DIR__ . '/../config/session.php';
requireRole('admin');
require_once __DIR__ . '/../classes/Fine.php';
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Emergency Override — Rakna';
$user      = currentUser();
$fineObj   = new Fine();
$db        = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $fineObj->emergencyOverride((int)$_POST['spot_id'], $user['user_id'], trim($_POST['reason']));
    setFlash($result['success'] ? 'success' : 'error', $result['message']);
    header('Location: /parking_system/index.php?action=emergency_override'); exit;
}

$spots = $db->query("SELECT spot_id, title, address, status FROM parking_spots WHERE is_verified = 1 ORDER BY title")->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.btn-danger { background-color:#dc3545; border-color:#dc3545; }
.btn-danger:hover { background-color:#c82333; border-color:#c82333; }
</style>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <h4 class="fw-bold mb-4 text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Emergency Vehicle Override</h4>
  <div class="alert alert-danger">
    <i class="bi bi-info-circle me-1"></i> <strong>Warning:</strong> This action will immediately cancel ALL active and upcoming reservations for the selected spot and issue full refunds. This action is logged and irreversible.
  </div>
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card border-danger">
        <div class="card-header bg-danger text-white fw-bold"><i class="bi bi-exclamation-triangle me-1"></i> Emergency Spot Override</div>
        <div class="card-body">
          <form method="POST">
            <div class="mb-3">
              <label class="form-label fw-semibold">Select Spot *</label>
              <select name="spot_id" class="form-select" required>
                <option value="">-- Choose Spot --</option>
                <?php foreach ($spots as $s): ?>
                <option value="<?= $s['spot_id'] ?>"><?= htmlspecialchars($s['title']) ?> — <?= htmlspecialchars($s['address']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-4">
              <label class="form-label fw-semibold">Emergency Reason *</label>
              <textarea name="reason" class="form-control" rows="4"
                placeholder="e.g. Emergency vehicle access required, fire evacuation zone..." required></textarea>
            </div>
            <button type="submit" class="btn btn-danger w-100 fw-bold"
                    onclick="return confirm('CONFIRM: This will cancel ALL reservations for this spot with full refunds. Proceed?')">
              <i class="bi bi-exclamation-triangle me-1"></i> Execute Emergency Override
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>