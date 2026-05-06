<?php
// owner/dashboard.php
require_once __DIR__ . '/../config/session.php';
requireRole('owner');
require_once __DIR__ . '/../classes/ParkingSpot.php';
require_once __DIR__ . '/../classes/Report.php';

$pageTitle = 'Owner Dashboard — CitySlot';
$user      = currentUser();
$spotObj   = new ParkingSpot();
$reportObj = new Report();

$stats   = $spotObj->getOwnerDashboardStats($user['user_id']);
$spots   = $spotObj->listOwnerSpots($user['user_id']);

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <h4 class="fw-bold mb-4">🏠 Owner Dashboard</h4>

  <!-- STAT CARDS -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card stat-card p-3">
        <p class="text-muted small mb-1">Total Spots</p>
        <h3 class="fw-bold mb-0 text-primary"><?= $stats['total_spots'] ?></h3>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card stat-card p-3">
        <p class="text-muted small mb-1">Total Bookings</p>
        <h3 class="fw-bold mb-0"><?= $stats['total_bookings'] ?? 0 ?></h3>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card stat-card p-3">
        <p class="text-muted small mb-1">Total Earned</p>
        <h3 class="fw-bold mb-0 text-success"><?= number_format($stats['total_earned'] ?? 0, 2) ?> EGP</h3>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card stat-card p-3">
        <p class="text-muted small mb-1">Avg Trust Score</p>
        <h3 class="fw-bold mb-0 text-warning"><?= number_format($stats['avg_trust_score'] ?? 0, 1) ?>/5</h3>
      </div>
    </div>
  </div>

  <!-- QUICK ACTIONS -->
  <div class="card mb-4 p-3">
    <div class="d-flex flex-wrap gap-2">
      <a href="/parking_system/index.php?action=add_spot" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Add New Spot</a>
      <a href="/parking_system/index.php?action=owner_reservations" class="btn btn-outline-secondary"><i class="bi bi-calendar3 me-1"></i>View Reservations</a>
      <a href="/parking_system/index.php?action=earnings" class="btn btn-outline-success"><i class="bi bi-wallet2 me-1"></i>Earnings</a>
      <a href="/parking_system/index.php?action=owner_report" class="btn btn-outline-info"><i class="bi bi-file-earmark-bar-graph me-1"></i>Monthly Report</a>
    </div>
  </div>

  <!-- MY SPOTS TABLE -->
  <div class="card">
    <div class="card-header d-flex justify-content-between">
      <span>🅿️ My Parking Spots</span>
      <a href="/parking_system/index.php?action=my_spots" class="small">Manage All</a>
    </div>
    <div class="card-body p-0">
      <?php if (empty($spots)): ?>
        <div class="p-4 text-center text-muted">
          No spots yet. <a href="/parking_system/index.php?action=add_spot">List your first spot!</a>
        </div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr><th>Spot</th><th>Price/hr</th><th>Status</th><th>Trust</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <?php foreach ($spots as $s): ?>
            <tr>
              <td>
                <strong><?= htmlspecialchars($s['title']) ?></strong>
                <br><small class="text-muted"><?= htmlspecialchars($s['address']) ?></small>
              </td>
              <td class="text-primary fw-bold"><?= number_format($s['price_per_hour'],2) ?> EGP</td>
              <td>
                <?php $bc = ['available'=>'success','unavailable'=>'secondary','maintenance'=>'warning','owner_use'=>'info','pending_verification'=>'primary'][$s['status']]??'light'; ?>
                <span class="badge bg-<?= $bc ?>"><?= ucfirst(str_replace('_',' ',$s['status'])) ?></span>
                <?php if (!$s['is_verified']): ?><span class="badge bg-danger ms-1">Unverified</span><?php endif; ?>
              </td>
              <td><span class="text-warning"><?= round($s['trust_score'],1) ?>★</span></td>
              <td>
                <a href="/parking_system/index.php?action=edit_spot&id=<?= $s['spot_id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                <a href="/parking_system/index.php?action=spot_status&id=<?= $s['spot_id'] ?>" class="btn btn-sm btn-outline-secondary">Status</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- TOP HOURS -->
  <?php if (!empty($stats['top_hours'])): ?>
  <div class="card mt-4">
    <div class="card-header">⏰ Your Busiest Hours</div>
    <div class="card-body">
      <div class="d-flex flex-wrap gap-2">
        <?php foreach ($stats['top_hours'] as $h): ?>
        <div class="text-center p-3 rounded bg-light">
          <div class="fw-bold text-primary"><?= $h['hour_slot'] ?>:00</div>
          <small class="text-muted"><?= $h['bookings'] ?> bookings</small>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
