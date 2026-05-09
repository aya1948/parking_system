<?php
// owner/dashboard.php
require_once __DIR__ . '/../config/session.php';
requireRole('owner');
require_once __DIR__ . '/../classes/ParkingSpot.php';
require_once __DIR__ . '/../classes/Report.php';
require_once __DIR__ . '/../classes/Garage.php';

$pageTitle  = 'Owner Dashboard — Rakna';
$user       = currentUser();
$spotObj    = new ParkingSpot();
$reportObj  = new Report();
$garageObj  = new Garage();

$stats    = $spotObj->getOwnerDashboardStats($user['user_id']);
$garages  = $garageObj->listOwnerGarages($user['user_id']);

require_once __DIR__ . '/../includes/header.php';
?>

<style>
/* توحيد ألوان الأزرار وتأثيرات hover مع الموف الغامق */
.btn-primary {
    background-color: #480959;
    border-color: #480959;
    color: #fff;
}
.btn-primary:hover {
    background-color: #5e2b6d;
    border-color: #5e2b6d;
}
.btn-outline-secondary {
    color: #480959;
    border-color: #480959;
}
.btn-outline-secondary:hover {
    background-color: #480959;
    color: #fff;
}
.btn-outline-success {
    color: #480959;
    border-color: #480959;
}
.btn-outline-success:hover {
    background-color: #480959;
    color: #fff;
}
.btn-outline-info {
    color: #480959;
    border-color: #480959;
}
.btn-outline-info:hover {
    background-color: #480959;
    color: #fff;
}
/* تلوين الأيقونات والنصوص الأساسية باللون الموف */
.text-primary {
    color: #480959 !important;
}
/* تأثير hover على صفوف الجدول */
.table-hover tbody tr:hover {
    background-color: #f3e5f5; /* لون خلفية موف فاتح عند التمرير */
}
/* بطاقات الإحصائيات - حدود يسار */
.stat-card {
    border-left: 4px solid #480959;
}
/* عرض أفضل الساعات */
.fw-bold.text-primary {
    color: #480959 !important;
}
</style>

<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <h4 class="fw-bold mb-4">Owner Dashboard</h4>

  <!-- STAT CARDS -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card stat-card p-3">
        <p class="text-muted small mb-1">Total Spots</p>
        <h3 class="fw-bold mb-0" style="color: #480959;"><?= $stats['total_spots'] ?></h3>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card stat-card p-3">
        <p class="text-muted small mb-1">Total Bookings</p>
        <h3 class="fw-bold mb-0" style="color: #480959;"><?= $stats['total_bookings'] ?? 0 ?></h3>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card stat-card p-3">
        <p class="text-muted small mb-1">Total Earned</p>
        <h3 class="fw-bold mb-0" style="color: #480959;"><?= number_format($stats['total_earned'] ?? 0, 2) ?> EGP</h3>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card stat-card p-3">
        <p class="text-muted small mb-1">Avg Trust Score</p>
        <h3 class="fw-bold mb-0" style="color: #480959;"><?= number_format($stats['avg_trust_score'] ?? 0, 1) ?>/5</h3>
      </div>
    </div>
  </div>

  <!-- QUICK ACTIONS -->
  <div class="card mb-4 p-3">
    <div class="d-flex flex-wrap gap-2">
      <a href="/parking_system/index.php?action=add_garage" class="btn btn-primary"><i class="bi bi-building me-1"></i>Add New Garage</a>
      <a href="/parking_system/index.php?action=owner_reservations" class="btn btn-outline-secondary"><i class="bi bi-calendar3 me-1"></i>View Reservations</a>
      <a href="/parking_system/index.php?action=earnings" class="btn btn-outline-success"><i class="bi bi-wallet2 me-1"></i>Earnings</a>
      <a href="/parking_system/index.php?action=owner_report" class="btn btn-outline-info"><i class="bi bi-file-earmark-bar-graph me-1"></i>Monthly Report</a>
    </div>
  </div>

  <!-- MY SPOTS TABLE -->
  <div class="card">
    <div class="card-header d-flex justify-content-between" style="background-color: #480959; color: #fff;">
      <span><i class="bi bi-building me-1"></i> My Garages</span>
      <a href="/parking_system/index.php?action=my_spots" class="small text-white">View All Garages</a>
    </div>
    <div class="card-body p-0">
      <?php if (empty($spots)): ?>
        <div class="p-4 text-center text-muted">
          No spots yet. <a href="/parking_system/index.php?action=add_garage">List your first spot!</a>
        </div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr><th>Garage</th><th>Spots</th><th>Occupancy</th><th>Status</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <?php foreach ($garages as $s): ?>
            <tr>
              <td>
                <strong><i class="bi bi-building me-1" style="color: #480959;"></i><?= htmlspecialchars($s['name']) ?></strong>
                <br><small class="text-muted"><?= htmlspecialchars($s['address']) ?></small>
              </td>
              <td class="text-center">
                <span class="badge" style="background-color: #480959;"><?= $s['available_spots'] ?> free</span>
                <span class="text-muted"> / <?= $s['total_spots'] ?> total</span>
              </td>
              <td>
                <?php $occ = $s['total_spots']>0 ? round(($s['total_spots']-$s['available_spots'])/$s['total_spots']*100) : 0; ?>
                <div class="progress" style="height:8px;min-width:80px;">
                  <div class="progress-bar bg-<?= $occ>80?'danger':($occ>50?'warning':'success') ?>" style="width:<?= $occ ?>%;"></div>
                </div>
                <small class="text-muted"><?= $occ ?>% occupied</small>
              </td>
              <td><span class="badge" style="background-color: #480959;">Active</span></td>
              <td>
                <a href="/parking_system/index.php?action=garage_map&id=<?= $s['garage_id'] ?>" class="btn btn-sm btn-primary">View Map</a>
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
    <div class="card-header" style="background-color: #480959; color: #fff;">
      <i class="bi bi-clock-history me-1"></i> Your Busiest Hours
    </div>
    <div class="card-body">
      <div class="d-flex flex-wrap gap-2">
        <?php foreach ($stats['top_hours'] as $h): ?>
        <div class="text-center p-3 rounded bg-light">
          <div class="fw-bold" style="color: #480959;"><?= $h['hour_slot'] ?>:00</div>
          <small class="text-muted"><?= $h['bookings'] ?> bookings</small>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>