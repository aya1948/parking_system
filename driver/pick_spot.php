<?php
// driver/pick_spot.php
// Driver picks a specific numbered spot inside a garage
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/Garage.php';
require_once __DIR__ . '/../classes/Vehicle.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Pricing.php';

$pageTitle  = 'Pick a Spot — CitySlot';
$user       = currentUser();
$b          = BASE_URL;
$garageObj  = new Garage();
$vehObj     = new Vehicle();
$db         = getDB();

$garageId   = (int)($_GET['garage_id'] ?? 0);
$startTime  = $_GET['start_time']     ?? date('Y-m-d H:i:s');
$endTime    = $_GET['end_time']       ?? date('Y-m-d H:i:s', strtotime('+1 hour'));
$vHeight    = $_GET['vehicle_height'] ?? '';
$vWidth     = $_GET['vehicle_width']  ?? '';
$needsEv    = $_GET['needs_ev']       ?? '';

$garage = $garageObj->getGarageById($garageId);
if (!$garage) {
    setFlash('error', 'Garage not found.');
    header("Location: $b/index.php?action=search_spots"); exit;
}

// Get available spots filtered by vehicle dimensions
$vehicleFilters = [];
if ($vHeight) $vehicleFilters['height']   = $vHeight;
if ($vWidth)  $vehicleFilters['width']    = $vWidth;
if ($needsEv) $vehicleFilters['needs_ev'] = 1;

$spots    = $garageObj->getAvailableSpotsInGarage($garageId, $startTime, $endTime, $vehicleFilters);
$vehicles = $vehObj->listUserVehicles($user['user_id']);

// Group by row letter
$grid       = [];
$freeCount  = 0;
$totalCount = 0;
foreach ($spots as $spot) {
    $row = preg_replace('/[0-9]/', '', $spot['spot_number']);
    $grid[$row][] = $spot;
    $totalCount++;
    if ($spot['real_status'] === 'available') $freeCount++;
}

$hours = $startTime && $endTime
    ? round((strtotime($endTime) - strtotime($startTime)) / 3600, 1)
    : 1;

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">

  <!-- HEADER -->
  <div class="d-flex justify-content-between align-items-start mb-4">
    <div>
      <nav aria-label="breadcrumb" class="mb-1">
        <ol class="breadcrumb small mb-0">
          <li class="breadcrumb-item"><a href="<?= $b ?>/index.php?action=search_spots">Search</a></li>
          <li class="breadcrumb-item active"><?= htmlspecialchars($garage['name']) ?></li>
        </ol>
      </nav>
      <h4 class="fw-bold mb-0">
        <i class="bi bi-building me-2 text-primary"></i><?= htmlspecialchars($garage['name']) ?>
      </h4>
      <p class="text-muted mb-0">
        <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($garage['address']) ?>
        <span class="badge bg-primary ms-2"><?= htmlspecialchars($garage['city_zone']) ?></span>
      </p>
    </div>
    <a href="<?= $b ?>/index.php?action=search_spots" class="btn btn-outline-secondary btn-sm">
      ← Back
    </a>
  </div>

  <!-- BOOKING INFO BAR -->
  <div class="card bg-light mb-4">
    <div class="card-body py-3">
      <div class="row g-3 align-items-center">
        <div class="col-md-3">
          <small class="text-muted d-block">Check-in</small>
          <strong><?= date('M d, h:i A', strtotime($startTime)) ?></strong>
        </div>
        <div class="col-md-3">
          <small class="text-muted d-block">Check-out</small>
          <strong><?= date('M d, h:i A', strtotime($endTime)) ?></strong>
        </div>
        <div class="col-md-2">
          <small class="text-muted d-block">Duration</small>
          <strong><?= $hours ?> hr(s)</strong>
        </div>
        <div class="col-md-2">
          <small class="text-muted d-block">Free Spots</small>
          <strong class="text-success"><?= $freeCount ?> / <?= $totalCount ?></strong>
        </div>
        <div class="col-md-2">
          <small class="text-muted d-block">Vehicle</small>
          <?php $defVeh = $vehObj->getDefaultVehicle($user['user_id']); ?>
          <strong><?= $defVeh ? htmlspecialchars($defVeh['license_plate']) : 'Not selected' ?></strong>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <!-- SPOT GRID -->
    <div class="col-md-8">
      <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span class="fw-bold">🅿️ Parking Map — Pick Your Spot</span>
          <div class="d-flex gap-3 small">
            <span><span class="badge bg-success">■</span> Available</span>
            <span><span class="badge bg-danger">■</span> Occupied</span>
            <span><span class="badge bg-warning text-dark">■</span> Maintenance</span>
          </div>
        </div>
        <div class="card-body">
          <?php if (empty($grid)): ?>
          <div class="text-center py-5">
            <div style="font-size:3rem;">😔</div>
            <p class="text-muted mt-2">No spots match your filters for this time.</p>
          </div>
          <?php else: ?>
          <?php foreach ($grid as $rowLetter => $rowSpots): ?>
          <div class="mb-3">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <span class="badge bg-dark px-3 py-2 me-2" style="font-size:14px;min-width:50px;">
                <?= htmlspecialchars($rowLetter) ?>
              </span>
              <?php foreach ($rowSpots as $spot):
                $isFree = $spot['real_status'] === 'available';
                $color  = match($spot['real_status']) {
                  'occupied'    => 'danger',
                  'maintenance' => 'warning',
                  'unavailable' => 'secondary',
                  default       => 'success',
                };
                $icon = $isFree ? '🅿️' : ($spot['real_status'] === 'occupied' ? '🚗' : '🔧');
              ?>
              <div class="text-center" style="min-width:65px;">
                <?php if ($isFree): ?>
                <a href="<?= $b ?>/index.php?action=book_spot&id=<?= $spot['spot_id'] ?>&start_time=<?= urlencode($startTime) ?>&end_time=<?= urlencode($endTime) ?>"
                   class="btn btn-<?= $color ?> btn-sm d-flex flex-column align-items-center py-2 px-2 spot-btn"
                   data-price="<?= $spot['price_per_hour'] ?>"
                   data-spot="<?= htmlspecialchars($spot['spot_number']) ?>"
                   data-id="<?= $spot['spot_id'] ?>"
                   title="<?= htmlspecialchars($spot['spot_number']) ?> — <?= number_format($spot['price_per_hour'],2) ?> EGP/hr — Click to Book">
                  <span style="font-size:1.3rem;"><?= $icon ?></span>
                  <small class="font-monospace fw-bold"><?= htmlspecialchars($spot['spot_number']) ?></small>
                  <small style="font-size:10px;"><?= number_format($spot['price_per_hour'],0) ?> EGP</small>
                </a>
                <?php else: ?>
                <div class="btn btn-<?= $color ?> btn-sm d-flex flex-column align-items-center py-2 px-2"
                     style="cursor:not-allowed;opacity:0.7;"
                     title="<?= htmlspecialchars($spot['spot_number']) ?> — <?= ucfirst($spot['real_status']) ?>">
                  <span style="font-size:1.3rem;"><?= $icon ?></span>
                  <small class="font-monospace fw-bold"><?= htmlspecialchars($spot['spot_number']) ?></small>
                  <small style="font-size:10px;"><?= ucfirst($spot['real_status']) ?></small>
                </div>
                <?php endif; ?>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- SIDE PANEL: Selected Spot + Booking Summary -->
    <div class="col-md-4">
      <!-- SELECTED SPOT INFO (shows when spot clicked) -->
      <div class="card shadow-sm mb-3" id="selectedSpotCard" style="display:none;">
        <div class="card-header bg-primary text-white fw-bold">
          ✅ Selected Spot
        </div>
        <div class="card-body">
          <div class="text-center mb-3">
            <div style="font-size:3rem;">🅿️</div>
            <h4 class="fw-bold font-monospace text-primary" id="selectedSpotNumber">—</h4>
            <p class="text-muted mb-0" id="selectedGarageName"><?= htmlspecialchars($garage['name']) ?></p>
          </div>
          <hr>
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Price/hr</span>
            <strong id="selectedPrice">— EGP</strong>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Duration</span>
            <strong><?= $hours ?> hr(s)</strong>
          </div>
          <div class="d-flex justify-content-between mb-3">
            <span class="text-muted">Estimated Total</span>
            <strong class="text-primary fs-5" id="selectedTotal">— EGP</strong>
          </div>
          <small class="text-muted d-block mb-3">* Final price includes VAT (14%) and may vary with peak hours & promo codes</small>
          <a href="#" id="bookNowBtn" class="btn btn-success w-100 fw-bold btn-lg">
            Book This Spot →
          </a>
        </div>
      </div>

      <!-- GARAGE INFO -->
      <div class="card shadow-sm">
        <div class="card-header fw-bold">🏢 Garage Info</div>
        <div class="card-body">
          <p class="mb-1"><strong><?= htmlspecialchars($garage['name']) ?></strong></p>
          <p class="small text-muted mb-2">
            <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($garage['address']) ?>
          </p>
          <?php if ($garage['description']): ?>
          <p class="small text-muted mb-2"><?= nl2br(htmlspecialchars($garage['description'])) ?></p>
          <?php endif; ?>
          <div class="row g-2 text-center mb-3">
            <div class="col-6">
              <div class="fw-bold text-success"><?= $freeCount ?></div>
              <small class="text-muted">Free Now</small>
            </div>
            <div class="col-6">
              <div class="fw-bold"><?= $garage['total_floors'] ?></div>
              <small class="text-muted">Floor(s)</small>
            </div>
          </div>
          <?php if ($garage['owner_name']): ?>
          <p class="small text-muted mb-0">
            <i class="bi bi-person me-1"></i>Owner: <?= htmlspecialchars($garage['owner_name']) ?>
          </p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div></div></div>

<script>
const hours    = <?= $hours ?>;
const bookBase = '<?= $b ?>/index.php?action=book_spot&id=';
const timeParams = '&start_time=<?= urlencode($startTime) ?>&end_time=<?= urlencode($endTime) ?>';

document.querySelectorAll('.spot-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();

        const spotNumber = this.dataset.spot;
        const price      = parseFloat(this.dataset.price);
        const spotId     = this.dataset.id;
        const estimated  = (price * hours).toFixed(2);

        // Highlight selected
        document.querySelectorAll('.spot-btn').forEach(b => b.classList.remove('ring'));
        this.style.outline = '3px solid #0d6efd';
        this.style.outlineOffset = '2px';

        // Update side panel
        document.getElementById('selectedSpotCard').style.display = 'block';
        document.getElementById('selectedSpotNumber').textContent = spotNumber;
        document.getElementById('selectedPrice').textContent      = price.toFixed(2) + ' EGP';
        document.getElementById('selectedTotal').textContent      = estimated + ' EGP*';
        document.getElementById('bookNowBtn').href                = bookBase + spotId + timeParams;

        // Scroll to card on mobile
        document.getElementById('selectedSpotCard').scrollIntoView({behavior:'smooth', block:'nearest'});
    });
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
