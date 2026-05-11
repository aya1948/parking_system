<?php
// owner/garage_map.php
require_once __DIR__ . '/../config/session.php';
requireRole('owner','admin');
require_once __DIR__ . '/../classes/Garage.php';

$pageTitle  = 'Garage Map — Rakna';
$user       = currentUser();
$b          = BASE_URL;
$garageObj  = new Garage();
$garageId   = (int)($_GET['id'] ?? 0);
$garage     = $garageObj->getGarageById($garageId);

if (!$garage) {
    setFlash('error', 'Garage not found.');
    header("Location: $b/index.php?action=my_spots"); exit;
}

$grid      = $garageObj->getSpotsGrid($garageId);
$occupancy = $garageObj->getGarageOccupancy($garageId);

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">

  <!-- HEADER -->
  <div class="d-flex justify-content-between align-items-start mb-4">
    <div>
      <h4 class="fw-bold mb-1">🏢 <?= htmlspecialchars($garage['name']) ?></h4>
      <p class="text-muted mb-0"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($garage['address']) ?></p>
    </div>
    <a href="<?= $b ?>/index.php?action=my_spots" class="btn btn-outline-secondary btn-sm">← Back</a>
  </div>

  <!-- OCCUPANCY STATS -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card p-3 text-center">
        <h3 class="fw-bold mb-0"><?= $occupancy['total'] ?></h3>
        <small class="text-muted">Total Spots</small>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3 text-center border-success">
        <h3 class="fw-bold mb-0 text-success"><?= $occupancy['available'] ?></h3>
        <small class="text-muted">Available</small>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3 text-center border-danger">
        <h3 class="fw-bold mb-0 text-danger"><?= $occupancy['occupied_now'] ?></h3>
        <small class="text-muted">Occupied Now</small>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3 text-center border-primary">
        <h3 class="fw-bold mb-0 text-primary"><?= $occupancy['occupancy_rate'] ?>%</h3>
        <small class="text-muted">Occupancy Rate</small>
      </div>
    </div>
  </div>

  <!-- LEGEND -->
  <div class="d-flex gap-3 mb-3 flex-wrap">
    <span><span class="badge bg-success px-3 py-2">■</span> Available</span>
    <span><span class="badge bg-danger px-3 py-2">■</span> Occupied</span>
    <span><span class="badge bg-warning px-3 py-2">■</span> Maintenance</span>
    <span><span class="badge bg-secondary px-3 py-2">■</span> Unavailable</span>
  </div>

  <!-- SPOT GRID -->
  <div class="card">
    <div class="card-header fw-bold">🅿️ Parking Map</div>
    <div class="card-body">
      <?php if (empty($grid)): ?>
        <p class="text-muted">No spots generated yet.</p>
      <?php else: ?>
      <?php foreach ($grid as $rowLetter => $rowSpots): ?>
      <div class="mb-3">
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <span class="badge bg-dark px-3 py-2 me-2" style="font-size:1rem;">Row <?= $rowLetter ?></span>
          <?php foreach ($rowSpots as $spot):
            $color = match($spot['real_status']) {
              'occupied'     => 'danger',
              'maintenance'  => 'warning',
              'unavailable'  => 'secondary',
              'owner_use'    => 'info',
              default        => 'success',
            };
            $icon = match($spot['real_status']) {
              'occupied'    => '🚗',
              'maintenance' => '🔧',
              default       => '🅿️',
            };
          ?>
          <div class="text-center" style="min-width:70px;">
            <div class="btn btn-<?= $color ?> btn-sm fw-bold px-2 py-2 d-block position-relative"
                 title="<?= htmlspecialchars($spot['spot_number']) ?> — <?= ucfirst($spot['real_status']) ?>"
                 data-bs-toggle="tooltip">
              <?= $icon ?><br>
              <small class="font-monospace"><?= htmlspecialchars($spot['spot_number']) ?></small>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

</div></div></div>
<script>
// Enable Bootstrap tooltips
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el);
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
