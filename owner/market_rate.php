<?php
// owner/market_rate.php
require_once __DIR__ . '/../config/session.php';
requireRole('owner');
require_once __DIR__ . '/../classes/ParkingSpot.php';

$pageTitle = 'Market Rate — Rakna';
$user      = currentUser();
$spotObj   = new ParkingSpot();
$spotId    = (int)($_GET['spot_id'] ?? 0);
$spot      = $spotObj->getSpotById($spotId);

if (!$spot || $spot['owner_id'] != $user['user_id']) {
    setFlash('error', 'Spot not found.');
    header('Location: /parking_system/index.php?action=my_spots'); exit;
}

$market = $spotObj->suggestMarketRate($spotId);
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header fw-bold">💰 Dynamic Market-Rate Calculator</div>
        <div class="card-body">
          <h5 class="fw-bold"><?= htmlspecialchars($spot['title']) ?></h5>
          <p class="text-muted small"><?= htmlspecialchars($spot['address']) ?></p>
          <hr>

          <div class="row g-3 text-center mb-4">
            <div class="col-4">
              <div class="card bg-light p-3">
                <div class="fw-bold text-primary fs-4"><?= number_format($spot['price_per_hour'],2) ?></div>
                <small class="text-muted">Your Current Price</small>
              </div>
            </div>
            <div class="col-4">
              <div class="card bg-light p-3">
                <div class="fw-bold text-success fs-4"><?= number_format($market['nearby_avg'],2) ?></div>
                <small class="text-muted">Market Average</small>
              </div>
            </div>
            <div class="col-4">
              <div class="card bg-warning p-3">
                <div class="fw-bold fs-4"><?= number_format($market['suggested_price'],2) ?></div>
                <small>Suggested Price</small>
              </div>
            </div>
          </div>

          <p class="text-muted small">Based on <strong><?= $market['count'] ?></strong> nearby verified spots.</p>

          <?php
          $diff = $spot['price_per_hour'] - $market['suggested_price'];
          if (abs($diff) < 2): ?>
            <div class="alert alert-success">✅ Your price is well-aligned with the market!</div>
          <?php elseif ($diff > 2): ?>
            <div class="alert alert-warning">⚠️ Your price is <strong><?= number_format($diff,2) ?> EGP above</strong> the market average. Consider lowering it to get more bookings.</div>
          <?php else: ?>
            <div class="alert alert-info">💡 Your price is <strong><?= number_format(abs($diff),2) ?> EGP below</strong> the market average. You could earn more!</div>
          <?php endif; ?>

          <div class="d-flex gap-2 mt-3">
            <a href="/parking_system/index.php?action=edit_spot&id=<?= $spotId ?>" class="btn btn-primary flex-fill">Update My Price</a>
            <a href="/parking_system/index.php?action=my_spots" class="btn btn-outline-secondary flex-fill">Back</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
