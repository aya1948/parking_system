<?php
// owner/my_spots.php
require_once __DIR__ . '/../config/session.php';
requireRole('owner');
require_once __DIR__ . '/../classes/ParkingSpot.php';

$pageTitle = 'My Spots — CitySlot';
$user      = currentUser();
$spotObj   = new ParkingSpot();
$spots     = $spotObj->listOwnerSpots($user['user_id']);

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">🅿️ My Parking Spots</h4>
    <a href="/parking_system/index.php?action=add_spot" class="btn btn-primary">+ Add New Spot</a>
  </div>

  <?php if (empty($spots)): ?>
    <div class="alert alert-info text-center py-5">
      <div style="font-size:3rem;">🅿️</div>
      <h5 class="mt-3">No spots yet!</h5>
      <p class="text-muted">List your first parking spot and start earning.</p>
      <a href="/parking_system/index.php?action=add_spot" class="btn btn-primary">Add Your First Spot</a>
    </div>
  <?php else: ?>
  <div class="row g-3">
    <?php foreach ($spots as $s): ?>
    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h6 class="fw-bold mb-0"><?= htmlspecialchars($s['title']) ?></h6>
            <div class="d-flex gap-1 flex-wrap justify-content-end">
              <?php $bc = ['available'=>'success','unavailable'=>'secondary','maintenance'=>'warning','owner_use'=>'info','pending_verification'=>'primary'][$s['status']]??'light'; ?>
              <span class="badge bg-<?= $bc ?>"><?= ucfirst(str_replace('_',' ',$s['status'])) ?></span>
              <?php if (!$s['is_verified']): ?><span class="badge bg-danger">Unverified</span><?php endif; ?>
            </div>
          </div>
          <p class="small text-muted mb-2"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($s['address']) ?></p>
          <div class="row g-2 mb-3">
            <div class="col-4 text-center">
              <div class="fw-bold text-primary"><?= number_format($s['price_per_hour'],2) ?></div>
              <small class="text-muted">EGP/hr</small>
            </div>
            <div class="col-4 text-center">
              <div class="fw-bold text-warning"><?= number_format($s['trust_score'],1) ?>★</div>
              <small class="text-muted"><?= $s['total_reviews'] ?> reviews</small>
            </div>
            <div class="col-4 text-center">
              <div class="fw-bold"><?= ucfirst($s['spot_type']) ?></div>
              <small class="text-muted">Type</small>
            </div>
          </div>
          <div class="d-flex gap-2 flex-wrap">
            <a href="/parking_system/index.php?action=edit_spot&id=<?= $s['spot_id'] ?>" class="btn btn-sm btn-outline-primary">✏️ Edit</a>
            <a href="/parking_system/index.php?action=spot_status&id=<?= $s['spot_id'] ?>" class="btn btn-sm btn-outline-secondary">🔄 Status</a>
            <?php if (!$s['is_verified']): ?>
            <a href="/parking_system/index.php?action=verify_spot&spot_id=<?= $s['spot_id'] ?>" class="btn btn-sm btn-warning">📋 Verify</a>
            <?php endif; ?>
            <a href="/parking_system/index.php?action=market_rate&spot_id=<?= $s['spot_id'] ?>" class="btn btn-sm btn-outline-info">💰 Market Rate</a>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
