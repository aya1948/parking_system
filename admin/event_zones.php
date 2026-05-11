<?php
// admin/event_zones.php
require_once __DIR__ . '/../config/session.php';
requireRole('admin');
require_once __DIR__ . '/../classes/Fine.php';
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Event Zones — Rakna';
$user      = currentUser();
$fineObj   = new Fine();
$db        = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $fineObj->createEventZone([
        'zone_name' => trim($_POST['zone_name']),
        'lat'       => (float)$_POST['lat'],
        'lng'       => (float)$_POST['lng'],
        'radius_km' => (float)$_POST['radius_km'],
        'reason'    => trim($_POST['reason']),
        'from'      => $_POST['active_from'],
        'until'     => $_POST['active_until'],
    ], $user['user_id']);
    setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Event zone created. Nearby spots locked.' : 'Error creating zone.');
    header('Location: /parking_system/index.php?action=event_zones'); exit;
}

$stmt = $db->query("SELECT ez.*, u.full_name AS admin_name FROM event_zones ez JOIN users u ON ez.admin_id = u.user_id ORDER BY ez.created_at DESC");
$zones = $stmt->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <h4 class="fw-bold mb-4">🚧 Municipal Event Zones</h4>
  <div class="row g-4">
    <!-- CREATE FORM -->
    <div class="col-md-4">
      <div class="card">
        <div class="card-header fw-bold">+ Create Event Zone</div>
        <div class="card-body">
          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Zone Name</label>
              <input type="text" name="zone_name" class="form-control" placeholder="e.g. Tahrir Square Security Zone" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Reason</label>
              <input type="text" name="reason" class="form-control" placeholder="Presidential motorcade, concert..." required>
            </div>
            <div class="row g-2 mb-3">
              <div class="col-6">
                <label class="form-label">Center Latitude</label>
                <input type="number" name="lat" class="form-control" step="0.000001" required>
              </div>
              <div class="col-6">
                <label class="form-label">Center Longitude</label>
                <input type="number" name="lng" class="form-control" step="0.000001" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Radius (km)</label>
              <input type="number" name="radius_km" class="form-control" step="0.1" min="0.1" placeholder="0.5" required>
            </div>
            <div class="row g-2 mb-3">
              <div class="col-6">
                <label class="form-label">Active From</label>
                <input type="datetime-local" name="active_from" class="form-control" required>
              </div>
              <div class="col-6">
                <label class="form-label">Active Until</label>
                <input type="datetime-local" name="active_until" class="form-control" required>
              </div>
            </div>
            <button type="submit" class="btn btn-danger w-100">🔒 Create & Lock Zone</button>
          </form>
        </div>
      </div>
    </div>

    <!-- ZONES LIST -->
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">Active & Past Zones</div>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr><th>Zone</th><th>Reason</th><th>Radius</th><th>From</th><th>Until</th><th>Admin</th><th>Status</th></tr>
            </thead>
            <tbody>
              <?php foreach ($zones as $z): ?>
              <tr>
                <td><strong><?= htmlspecialchars($z['zone_name']) ?></strong></td>
                <td><small><?= htmlspecialchars($z['reason']) ?></small></td>
                <td><?= $z['radius_km'] ?> km</td>
                <td><small><?= date('M d, H:i', strtotime($z['active_from'])) ?></small></td>
                <td><small><?= date('M d, H:i', strtotime($z['active_until'])) ?></small></td>
                <td><small><?= htmlspecialchars($z['admin_name']) ?></small></td>
                <td>
                  <?php $now = time();
                  if (strtotime($z['active_until']) < $now) $bc = 'secondary';
                  elseif (strtotime($z['active_from']) > $now) $bc = 'info';
                  else $bc = 'danger';
                  ?>
                  <span class="badge bg-<?= $bc ?>"><?= $bc === 'danger' ? 'ACTIVE' : ($bc === 'info' ? 'Upcoming' : 'Expired') ?></span>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($zones)): ?>
              <tr><td colspan="7" class="text-center text-muted py-4">No zones created yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
