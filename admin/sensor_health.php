<?php
// admin/sensor_health.php
require_once __DIR__ . '/../config/session.php';
requireRole('admin');
require_once __DIR__ . '/../classes/Report.php';

$pageTitle = 'Sensor Health Monitor — CitySlot';
$reportObj = new Report();

// Simulate heartbeat refresh
if (isset($_GET['ping']) && is_numeric($_GET['ping'])) {
    $reportObj->updateSensorHeartbeat((int)$_GET['ping']);
    setFlash('success', 'Sensor heartbeat updated.');
    header('Location: /parking_system/index.php?action=sensor_health'); exit;
}

$sensors = $reportObj->getSensorHealthReport();
$online  = count(array_filter($sensors, fn($s) => $s['status'] === 'online'));
$offline = count(array_filter($sensors, fn($s) => $s['status'] === 'offline'));

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">📡 IoT Sensor Health Monitor</h4>
    <a href="/parking_system/index.php?action=sensor_health" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</a>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card stat-card border-success p-3"><p class="text-muted small mb-1">Online</p><h3 class="fw-bold text-success"><?= $online ?></h3></div></div>
    <div class="col-md-3"><div class="card stat-card border-danger p-3"><p class="text-muted small mb-1">Offline</p><h3 class="fw-bold text-danger"><?= $offline ?></h3></div></div>
    <div class="col-md-3"><div class="card stat-card p-3"><p class="text-muted small mb-1">Total Sensors</p><h3 class="fw-bold"><?= count($sensors) ?></h3></div></div>
    <div class="col-md-3"><div class="card stat-card p-3"><p class="text-muted small mb-1">Uptime</p><h3 class="fw-bold <?= $online < count($sensors) ? 'text-warning':'text-success' ?>">
      <?= count($sensors) > 0 ? round($online/count($sensors)*100) : 100 ?>%
    </h3></div></div>
  </div>

  <?php if ($offline > 0): ?>
  <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><strong><?= $offline ?> sensor(s) offline!</strong> These spots may have inaccurate occupancy data.</div>
  <?php endif; ?>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>Spot</th><th>Address</th><th>Last Heartbeat</th><th>Minutes Ago</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach ($sensors as $s): ?>
          <tr>
            <td><strong><?= htmlspecialchars($s['spot_title']) ?></strong></td>
            <td><small><?= htmlspecialchars($s['address']) ?></small></td>
            <td><?= date('M d, H:i:s', strtotime($s['last_heartbeat'])) ?></td>
            <td><?= $s['minutes_since_heartbeat'] ?> min</td>
            <td>
              <?php $bc = ['online'=>'success','offline'=>'danger','warning'=>'warning'][$s['status']]??'secondary'; ?>
              <span class="badge bg-<?= $bc ?>"><?= strtoupper($s['status']) ?></span>
            </td>
            <td>
              <a href="/parking_system/index.php?action=sensor_health&ping=<?= $s['spot_id'] ?>" class="btn btn-sm btn-outline-success">
                <i class="bi bi-activity"></i> Ping
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($sensors)): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No sensors registered. Sensors are auto-created when spots are added.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
