<?php
// driver/book_spot.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/ParkingSpot.php';
require_once __DIR__ . '/../classes/Vehicle.php';
require_once __DIR__ . '/../classes/Pricing.php';

$pageTitle = 'Book Parking — CitySlot';
$user      = currentUser();
$spotId    = (int)($_GET['id'] ?? 0);
$spotObj   = new ParkingSpot();
$vehObj    = new Vehicle();
$pricing   = new Pricing();

$spot      = $spotObj->getSpotById($spotId);
if (!$spot || !$spotObj->isVisibleInSearch($spotId)) {
    setFlash('error', 'Spot not found or unavailable.');
    header('Location: /parking_system/index.php?action=search_spots'); exit;
}

$vehicles    = $vehObj->listUserVehicles($user['user_id']);
$marketRate  = $spotObj->suggestMarketRate($spotId);
$promoResult = null;

// AJAX price preview
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['preview'])) {
    header('Content-Type: application/json');
    $start = $_POST['start_time'] ?? '';
    $end   = $_POST['end_time']   ?? '';
    $promo = $_POST['promo_code'] ?? null;
    if ($start && $end) {
        echo json_encode($pricing->calculateTotal($spotId, $start, $end, $user['user_id'], $promo));
    } else {
        echo json_encode(['error' => 'Invalid times']);
    }
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>
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
          <h5 class="fw-bold"><?= htmlspecialchars($spot['title']) ?></h5>
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
        <div class="card-header">📅 Reserve Your Spot</div>
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
                <input type="datetime-local" name="end_time" id="endTime" class="form-control" required>
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

            <!-- PRICE PREVIEW BOX -->
            <div class="card bg-light mb-3" id="pricePreview" style="display:none;">
              <div class="card-body">
                <h6 class="fw-bold mb-3">💰 Price Breakdown</h6>
                <div class="d-flex justify-content-between small"><span>Base Price</span><span id="previewBase">—</span></div>
                <div class="d-flex justify-content-between small"><span>Peak Multiplier</span><span id="previewPeak">—</span></div>
                <div class="d-flex justify-content-between small text-success"><span>Promo Discount</span><span id="previewPromo">—</span></div>
                <div class="d-flex justify-content-between small text-muted"><span>VAT (14%)</span><span id="previewVat">—</span></div>
                <hr class="my-2">
                <div class="d-flex justify-content-between fw-bold"><span>Total</span><span id="previewTotal" class="text-primary">—</span></div>
              </div>
            </div>

            <button type="button" class="btn btn-outline-primary w-100 mb-2" id="calcPrice">Calculate Price</button>
            <button type="submit" class="btn btn-primary w-100" <?= empty($vehicles) ? 'disabled' : '' ?>>
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

<script>
async function fetchPrice() {
    const start = document.getElementById('startTime').value;
    const end   = document.getElementById('endTime').value;
    const promo = document.getElementById('promoCode').value;
    if (!start || !end) return alert('Please select start and end times first.');

    const form = new FormData();
    form.append('preview', '1');
    form.append('start_time', start);
    form.append('end_time', end);
    form.append('promo_code', promo);

    const res  = await fetch(`/index.php?action=book_spot&id=<?= $spotId ?>`, {method:'POST', body: form});
    const data = await res.json();
    if (data.error) return alert(data.error);

    document.getElementById('pricePreview').style.display = 'block';
    document.getElementById('previewBase').textContent  = data.base_price + ' EGP';
    document.getElementById('previewPeak').textContent  = 'x' + data.peak_multiplier;
    document.getElementById('previewPromo').textContent = '-' + data.promo_discount + ' EGP';
    document.getElementById('previewVat').textContent   = data.vat + ' EGP';
    document.getElementById('previewTotal').textContent = data.total + ' EGP';
}
document.getElementById('calcPrice').addEventListener('click', fetchPrice);
document.getElementById('applyPromo').addEventListener('click', fetchPrice);
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
