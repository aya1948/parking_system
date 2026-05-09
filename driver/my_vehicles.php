<?php
// driver/my_vehicles.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/Vehicle.php';

$pageTitle = 'My Vehicles — Rakna';
$user      = currentUser();
$vehObj    = new Vehicle();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'set_default') {
        $vehObj->setDefaultVehicle((int)$_POST['vehicle_id'], $user['user_id']);
        setFlash('success', 'Default vehicle updated.');
    } elseif ($action === 'delete') {
        $result = $vehObj->deleteVehicle((int)$_POST['vehicle_id'], $user['user_id']);
        setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Vehicle removed.' : $result['message']);
    }
    header('Location: /parking_system/index.php?action=my_vehicles'); exit;
}

$vehicles = $vehObj->listUserVehicles($user['user_id']);
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
.btn-outline-primary {
    color: #480959;
    border-color: #480959;
}
.btn-outline-primary:hover {
    background-color: #480959;
    color: #fff;
}
.btn-outline-danger {
    color: #dc3545;
    border-color: #dc3545;
}
.btn-outline-danger:hover {
    background-color: #dc3545;
    color: #fff;
}
.border-primary {
    border-color: #480959 !important;
}
.badge.bg-primary {
    background-color: #480959 !important;
}
.badge.bg-success {
    background-color: #198754 !important;
}
</style>

<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-car-front-fill me-2"></i>My Vehicles</h4>
    <a href="/parking_system/index.php?action=add_vehicle" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i> Add Vehicle</a>
  </div>

  <?php if (empty($vehicles)): ?>
    <div class="alert alert-info"><i class="bi bi-info-circle me-1"></i> No vehicles added. <a href="/parking_system/index.php?action=add_vehicle">Add one now</a> to start booking.</div>
  <?php else: ?>
  <div class="row g-3">
    <?php foreach ($vehicles as $v): ?>
    <div class="col-md-4">
      <div class="card <?= $v['is_default'] ? 'border-primary' : '' ?>">
        <div class="card-body">
          <?php if ($v['is_default']): ?><span class="badge bg-primary mb-2"><i class="bi bi-check-circle me-1"></i>Default</span><?php endif; ?>
          <h5 class="font-monospace"><?= htmlspecialchars($v['license_plate']) ?></h5>
          <p class="text-muted mb-1"><?= htmlspecialchars($v['make'] . ' ' . $v['model']) ?> — <?= htmlspecialchars($v['color']) ?></p>
          <p class="small mb-1">Type: <strong><?= ucfirst($v['vehicle_type']) ?></strong></p>
          <?php if ($v['is_ev']): ?><span class="badge bg-success mb-2">EV</span><?php endif; ?>
          <?php if ($v['height_cm']): ?><p class="small mb-0">H: <?= $v['height_cm'] ?>cm / W: <?= $v['width_cm'] ?>cm</p><?php endif; ?>
          <div class="d-flex gap-2 mt-3">
            <?php if (!$v['is_default']): ?>
            <form method="POST" class="d-inline">
              <input type="hidden" name="action" value="set_default">
              <input type="hidden" name="vehicle_id" value="<?= $v['vehicle_id'] ?>">
              <button class="btn btn-sm btn-outline-primary"><i class="bi bi-star me-1"></i>Set Default</button>
            </form>
            <?php endif; ?>
            <form method="POST" class="d-inline" onsubmit="return confirm('Remove this vehicle?')">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="vehicle_id" value="<?= $v['vehicle_id'] ?>">
              <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3 me-1"></i>Remove</button>
            </form>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>