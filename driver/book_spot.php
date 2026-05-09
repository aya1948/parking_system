<?php
// driver/book_spot.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/ParkingSpot.php';
require_once __DIR__ . '/../classes/Vehicle.php';
require_once __DIR__ . '/../classes/Pricing.php';

$pageTitle = 'Book Parking — Rakna';
$user      = currentUser();
$spotId    = (int)($_GET['id'] ?? 0);
$spotObj   = new ParkingSpot();
$vehObj    = new Vehicle();
$pricing   = new Pricing();

$spot      = $spotObj->getSpotById($spotId);
// Pre-fill times if coming from pick_spot page
$preStart  = $_GET['start_time'] ?? '';
$preEnd    = $_GET['end_time']   ?? '';
if (!$spot || !$spotObj->isVisibleInSearch($spotId)) {
    setFlash('error', 'Spot not found or unavailable.');
    header('Location: /parking_system/index.php?action=search_spots'); exit;
}

$vehicles    = $vehObj->listUserVehicles($user['user_id']);
$marketRate  = $spotObj->suggestMarketRate($spotId);
$promoResult = null;

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
.btn-outline-secondary {
    color: #480959;
    border-color: #480959;
}
.btn-outline-secondary:hover {
    background-color: #480959;
    color: #fff;
}
.text-primary {
    color: #480959 !important;
}
.card-header {
    background-color: #480959;
    color: #fff;
    font-weight: bold;
}
.breadcrumb .active {
    color: #480959;
}
.badge.bg-dark {
    background-color: #480959 !important;
}
.text-success {
    color: #480959 !important;
}
</style>

<div class="container-fluid px-0">
<div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

<div class="col-md-10 p-4">
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="/parking_system/index.php?action=search_spots">Find Parking</a></li>
      <li class="breadcrumb-item active">Book: <?= htmlspecialchars($spot['title']) ?></li>
    </ol>
  </nav>

  <div class="row g-4">
    <!-- SPOT INFO -->
    <div class="col-md-5">
      <div class="card">
        <div class="card-body">
          <?php if (!empty($spot['garage_name'] ?? '')): ?>
          <div class="small text-muted mb-1">
            <i class="bi bi-building me-1"></i><?= htmlspecialchars($spot['garage_name'] ?? '') ?>
          </div>
          <?php endif; ?>
          <h5 class="fw-bold">
            <?php if (!empty($spot['spot_number'] ?? '')): ?>
            <span class="badge bg-dark font-monospace me-1"><?= htmlspecialchars($spot['spot_number']) ?></span>
            <?php endif; ?>
            <?= htmlspecialchars($spot['title']) ?>
          </h5>
          <p class="text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($spot['address']) ?></p>
          <hr>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <small class="text-muted">Base Price</small>
              <div class="fw-bold text-primary"><?= number_format($spot['price_per_hour'], 2) ?> EGP/hr</div>
            </div>
            <div class="col-6">
              <small class="text-muted">Market Avg</small>
              <div class="fw-bold"><?= number_format($marketRate['suggested_price'], 2) ?> EGP/hr</div>
            </div>
            <div class="col-6">
              <small class="text-muted">Type</small>
              <div class="fw-bold"><?= ucfirst($spot['spot_type']) ?></div>
            </div>
            <div class="col-6">
              <small class="text-muted">EV Charger</small>
              <div class="fw-bold"><?= $spot['has_ev_charger'] ? '<span class="text-success">Yes</span>' : 'No' ?></div>
            </div>
            <?php if ($spot['max_height_cm']): ?>
            <div class="col-6">
              <small class="text-muted">Max Height</small>
              <div class="fw-bold"><?= $spot['max_height_cm'] ?> cm</div>
            </div>
            <?php endif; ?>
          </div>
          <div class="d-flex align-items-center gap-2 mb-2">
            <span class="text-warning"><?= str_repeat('★', round($spot['trust_score'])) ?><?= str_repeat('☆', 5 - round($spot['trust_score'])) ?></span>
            <small class="text-muted"><?= number_format($spot['trust_score'], 1) ?>/5 (<?= $spot['total_reviews'] ?> reviews)</small>
          </div>
          <p class="small text-muted"><?= nl2br(htmlspecialchars($spot['description'])) ?></p>
        </div>
      </div>
    </div>

    <!-- BOOKING FORM -->
    <div class="col-md-7">
      <div class="card">
        <div class="card-header"><i class="bi bi-calendar-check me-1"></i> Reserve Your Spot</div>
        <div class="card-body">
          <form action="/parking_system/index.php?action=do_booking" method="POST" id="bookingForm">
            <input type="hidden" name="spot_id" value="<?= $spotId ?>">

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label">Start Date & Time</label>
                <input type="datetime-local" name="start_time" id="startTime" class="form-control" required
                       min="<?= date('Y-m-d\TH:i') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">End Date & Time</label>
                <input type="datetime-local" name="end_time" id="endTime" class="form-control" required
                       value="<?= $preEnd ? date('Y-m-d\TH:i', strtotime($preEnd)) : '' ?>">
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Vehicle</label>
              <select name="vehicle_id" class="form-select" required>
                <?php foreach ($vehicles as $v): ?>
                <option value="<?= $v['vehicle_id'] ?>" <?= $v['is_default'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($v['license_plate'] . ' — ' . $v['make'] . ' ' . $v['model']) ?>
                </option>
                <?php endforeach; ?>
              </select>
              <?php if (empty($vehicles)): ?>
              <div class="alert alert-warning mt-2">
                <a href="/parking_system/index.php?action=add_vehicle">Add a vehicle first</a> before booking.
              </div>
              <?php endif; ?>
            </div>

            <div class="mb-3">
              <label class="form-label">Promo Code <span class="text-muted">(optional)</span></label>
              <div class="input-group">
                <input type="text" name="promo_code" id="promoCode" class="form-control" placeholder="Enter promo code">
                <button type="button" class="btn btn-outline-secondary" id="applyPromo">Apply</button>
              </div>
            </div>

            <div class="alert alert-info small mb-3">
              <i class="bi bi-receipt me-1"></i>
              A full price breakdown receipt (including VAT 14%) will be shown after booking.
            </div>
            <button type="submit" class="btn btn-primary w-100 btn-lg" <?= empty($vehicles) ? 'disabled' : '' ?>>
              <i class="bi bi-check-circle me-1"></i> Confirm Booking
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>