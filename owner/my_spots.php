<?php
// owner/my_spots.php
require_once __DIR__ . '/../config/session.php';
requireRole('owner');
require_once __DIR__ . '/../classes/Garage.php';
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'My Garages & Spots — Rakna';
$user      = currentUser();
$b         = BASE_URL;
$garageObj = new Garage();
$db        = getDB();

// Handle delete garage
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_garage') {
    $result = $garageObj->deleteGarage((int)$_POST['garage_id'], $user['user_id']);
    setFlash($result['success'] ? 'success' : 'error',
             $result['success'] ? 'Garage deleted.' : $result['message']);
    header("Location: $b/index.php?action=my_spots"); exit;
}

$garages = $garageObj->listOwnerGarages($user['user_id']);
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">🏢 My Garages & Spots</h4>
    <a href="<?= $b ?>/index.php?action=add_garage" class="btn btn-primary">
      <i class="bi bi-plus-circle me-1"></i>Add New Garage
    </a>
  </div>

  <?php if (empty($garages)): ?>
  <div class="text-center py-5">
    <div style="font-size:5rem;">🏢</div>
    <h5 class="mt-3 fw-bold">No Garages Yet</h5>
    <p class="text-muted">Add your first garage and generate numbered spots.</p>
    <a href="<?= $b ?>/index.php?action=add_garage" class="btn btn-primary btn-lg mt-2">
      Add First Garage
    </a>
  </div>
  <?php else: ?>

  <!-- SUMMARY -->
  <?php
  $totalSpots = array_sum(array_column($garages, 'total_spots'));
  $availSpots = array_sum(array_column($garages, 'available_spots'));
  ?>
  <div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card p-3 text-center"><h3 class="fw-bold text-primary mb-0"><?= count($garages) ?></h3><small class="text-muted">Garages</small></div></div>
    <div class="col-md-3"><div class="card p-3 text-center"><h3 class="fw-bold mb-0"><?= $totalSpots ?></h3><small class="text-muted">Total Spots</small></div></div>
    <div class="col-md-3"><div class="card p-3 text-center border-success"><h3 class="fw-bold text-success mb-0"><?= $availSpots ?></h3><small class="text-muted">Available</small></div></div>
    <div class="col-md-3"><div class="card p-3 text-center border-danger"><h3 class="fw-bold text-danger mb-0"><?= $totalSpots - $availSpots ?></h3><small class="text-muted">Occupied/Other</small></div></div>
  </div>

  <?php foreach ($garages as $g): ?>
  <?php $occ = $g['total_spots'] > 0 ? round(($g['total_spots']-$g['available_spots'])/$g['total_spots']*100) : 0; ?>
  <div class="card mb-4 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center py-3" style="background:#f8f9fa;">
      <div class="d-flex align-items-center gap-3">
        <div style="font-size:2rem;">🏢</div>
        <div>
          <h5 class="fw-bold mb-0"><?= htmlspecialchars($g['name']) ?></h5>
          <small class="text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($g['address']) ?>
            <?php if ($g['city_zone']): ?><span class="badge bg-light text-dark ms-2"><?= htmlspecialchars($g['city_zone']) ?></span><?php endif; ?>
          </small>
        </div>
      </div>
      <div class="d-flex gap-2">
        <a href="<?= $b ?>/index.php?action=garage_map&id=<?= $g['garage_id'] ?>" class="btn btn-primary btn-sm">
          <i class="bi bi-map me-1"></i>View Map
        </a>
        <a href="<?= $b ?>/index.php?action=owner_reservations" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-calendar3 me-1"></i>Reservations
        </a>
        <form method="POST" class="d-inline" onsubmit="return confirm('Delete garage and ALL spots?')">
          <input type="hidden" name="action"    value="delete_garage">
          <input type="hidden" name="garage_id" value="<?= $g['garage_id'] ?>">
          <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
        </form>
      </div>
    </div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-3 text-center"><div class="fw-bold fs-4"><?= $g['total_spots'] ?></div><small class="text-muted">Total Spots</small></div>
        <div class="col-md-3 text-center"><div class="fw-bold fs-4 text-success"><?= $g['available_spots'] ?></div><small class="text-muted">Available</small></div>
        <div class="col-md-3 text-center"><div class="fw-bold fs-4 text-danger"><?= $g['total_spots']-$g['available_spots'] ?></div><small class="text-muted">Occupied/Other</small></div>
        <div class="col-md-3 text-center"><div class="fw-bold fs-4"><?= $g['total_floors'] ?></div><small class="text-muted">Floor(s)</small></div>
      </div>
      <!-- OCCUPANCY BAR -->
      <div class="mb-3">
        <div class="d-flex justify-content-between small mb-1">
          <span class="text-muted">Occupancy</span><span class="fw-bold"><?= $occ ?>%</span>
        </div>
        <div class="progress" style="height:10px;">
          <div class="progress-bar bg-<?= $occ>80?'danger':($occ>50?'warning':'success') ?>" style="width:<?= $occ ?>%;"></div>
        </div>
      </div>
      <?php if ($g['total_spots'] === 0): ?>
      <div class="alert alert-warning d-flex justify-content-between align-items-center mb-0">
        <span>⚠️ No spots yet.</span>
        <a href="<?= $b ?>/index.php?action=add_garage&step=2&garage_id=<?= $g['garage_id'] ?>" class="btn btn-warning btn-sm">Generate Spots</a>
      </div>
      <?php else: ?>
      <?php
      $stmt = $db->prepare("
          SELECT spot_number,
                 CASE
                   WHEN status != 'available' THEN status
                   WHEN EXISTS (SELECT 1 FROM reservations r WHERE r.spot_id = parking_spots.spot_id
                     AND r.status IN ('confirmed','active','extended')
                     AND NOW() BETWEEN r.start_time AND DATE_ADD(r.end_time, INTERVAL 10 MINUTE)) THEN 'occupied'
                   ELSE 'available'
                 END AS real_status
          FROM parking_spots WHERE garage_id = ? ORDER BY spot_number ASC LIMIT 40
      ");
      $stmt->execute([$g['garage_id']]);
      $previewSpots = $stmt->fetchAll();
      $remaining = $g['total_spots'] - count($previewSpots);
      ?>
      <div class="d-flex flex-wrap gap-1 align-items-center mb-2">
        <?php foreach ($previewSpots as $ps):
          $c = match($ps['real_status']) { 'occupied'=>'danger','maintenance'=>'warning','unavailable'=>'secondary','owner_use'=>'info',default=>'success' };
        ?>
        <span class="badge bg-<?= $c ?> font-monospace" style="font-size:11px;"><?= htmlspecialchars($ps['spot_number']) ?></span>
        <?php endforeach; ?>
        <?php if ($remaining > 0): ?><span class="text-muted small">+<?= $remaining ?> more</span><?php endif; ?>
      </div>
      <div class="d-flex gap-3 small">
        <span><span class="badge bg-success">■</span> Available</span>
        <span><span class="badge bg-danger">■</span> Occupied</span>
        <span><span class="badge bg-warning">■</span> Maintenance</span>
        <span><span class="badge bg-secondary">■</span> Unavailable</span>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
