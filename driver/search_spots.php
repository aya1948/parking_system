<?php
// driver/search_spots.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/ParkingSpot.php';
require_once __DIR__ . '/../classes/Vehicle.php';

$pageTitle = 'Find Parking — CitySlot';
$user      = currentUser();
$b         = BASE_URL;
$spotObj   = new ParkingSpot();
$vehObj    = new Vehicle();
$vehicles  = $vehObj->listUserVehicles($user['user_id']);
$spots     = [];
$searched  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searched = true;
    $filters  = [
        'zone'          => trim($_POST['zone']          ?? ''),
        'max_price'     => $_POST['max_price']          ?? '',
        'spot_type'     => $_POST['spot_type']          ?? '',
        'needs_ev'      => !empty($_POST['needs_ev'])   ? 1 : 0,
        'vehicle_height'=> $_POST['vehicle_height']     ?? '',
        'vehicle_width' => $_POST['vehicle_width']      ?? '',
        'start_time'    => $_POST['start_time']         ?? date('Y-m-d H:i:s'),
        'end_time'      => $_POST['end_time']           ?? date('Y-m-d H:i:s', strtotime('+1 hour')),
        'hide_occupied' => !empty($_POST['hide_occupied']) ? 1 : 0,
    ];
    $spots = $spotObj->searchSpots($filters);
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0">
<div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

<div class="col-md-10 p-4">
  <h4 class="fw-bold mb-4">🔍 Find Parking</h4>

  <!-- FILTER FORM -->
  <div class="card mb-4">
    <div class="card-body">
      <form method="POST">
        <div class="row g-3 mb-3">
          <div class="col-md-3">
            <label class="form-label">Zone / Area</label>
            <input type="text" name="zone" class="form-control"
                   placeholder="e.g. Maadi, Zamalek"
                   value="<?= htmlspecialchars($_POST['zone'] ?? '') ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label">Start Time</label>
            <input type="datetime-local" name="start_time" class="form-control"
                   value="<?= htmlspecialchars($_POST['start_time'] ?? date('Y-m-d\TH:i')) ?>"
                   min="<?= date('Y-m-d\TH:i') ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label">End Time</label>
            <input type="datetime-local" name="end_time" class="form-control"
                   value="<?= htmlspecialchars($_POST['end_time'] ?? date('Y-m-d\TH:i', strtotime('+1 hour'))) ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label">Max Price (EGP/hr)</label>
            <input type="number" name="max_price" class="form-control"
                   placeholder="Any" min="0"
                   value="<?= htmlspecialchars($_POST['max_price'] ?? '') ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label">Spot Type</label>
            <select name="spot_type" class="form-select">
              <option value="">Any</option>
              <option value="driveway" <?= ($_POST['spot_type']??'')==='driveway'?'selected':'' ?>>Driveway</option>
              <option value="lot"      <?= ($_POST['spot_type']??'')==='lot'?'selected':'' ?>>Lot</option>
              <option value="garage"   <?= ($_POST['spot_type']??'')==='garage'?'selected':'' ?>>Garage</option>
              <option value="street"   <?= ($_POST['spot_type']??'')==='street'?'selected':'' ?>>Street</option>
            </select>
          </div>
        </div>
        <div class="row g-3 align-items-end">
          <div class="col-md-3">
            <label class="form-label">Vehicle</label>
            <select name="vehicle_id" class="form-select" id="vehicleSelect">
              <option value="">Select Vehicle</option>
              <?php foreach ($vehicles as $v): ?>
              <option value="<?= $v['vehicle_id'] ?>"
                      data-height="<?= $v['height_cm'] ?>"
                      data-width="<?= $v['width_cm'] ?>"
                      data-ev="<?= $v['is_ev'] ?>"
                      <?= $v['is_default'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($v['license_plate'].' — '.$v['make'].' '.$v['model']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2 d-flex gap-3 align-items-center">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="needs_ev" id="needsEv"
                     <?= !empty($_POST['needs_ev'])?'checked':'' ?>>
              <label class="form-check-label" for="needsEv">⚡ EV</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="hide_occupied" id="hideOccupied"
                     <?= !empty($_POST['hide_occupied'])?'checked':'' ?>>
              <label class="form-check-label" for="hideOccupied">Hide Occupied</label>
            </div>
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">
              <i class="bi bi-search me-1"></i>Search
            </button>
          </div>
        </div>
        <input type="hidden" name="vehicle_height" id="vehicleHeight" value="<?= htmlspecialchars($_POST['vehicle_height'] ?? '') ?>">
        <input type="hidden" name="vehicle_width"  id="vehicleWidth"  value="<?= htmlspecialchars($_POST['vehicle_width']  ?? '') ?>">
      </form>
    </div>
  </div>

  <!-- RESULTS -->
  <?php if ($searched): ?>
    <?php
    $freeSpots     = array_filter($spots, fn($s) => $s['real_time_status'] === 'free');
    $occupiedSpots = array_filter($spots, fn($s) => $s['real_time_status'] === 'occupied');
    ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <p class="text-muted mb-0">
        <span class="badge bg-success me-1"><?= count($freeSpots) ?> Free</span>
        <span class="badge bg-danger me-1"><?= count($occupiedSpots) ?> Occupied</span>
        <?= count($spots) ?> total spots found
      </p>
    </div>

    <?php if (empty($spots)): ?>
      <div class="alert alert-info">No spots found. Try adjusting your filters.</div>
    <?php else: ?>
    <div class="row g-3">
      <?php foreach ($spots as $spot):
        $isOccupied = $spot['real_time_status'] === 'occupied';
      ?>
      <div class="col-md-4">
        <div class="card spot-card h-100 <?= $isOccupied ? 'border-danger opacity-75' : 'border-success' ?>">

          <!-- OCCUPIED RIBBON -->
          <?php if ($isOccupied): ?>
          <div class="bg-danger text-white text-center py-1 small fw-bold rounded-top">
            🔴 OCCUPIED
            <?php if ($spot['next_available_at']): ?>
            — Free at <?= date('h:i A', strtotime($spot['next_available_at'])) ?>
            <?php endif; ?>
          </div>
          <?php else: ?>
          <div class="bg-success text-white text-center py-1 small fw-bold rounded-top">
            🟢 AVAILABLE
          </div>
          <?php endif; ?>

          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <h6 class="fw-bold mb-0"><?= htmlspecialchars($spot['title']) ?></h6>
              <span class="badge bg-<?= $spot['spot_type']==='garage'?'dark':($spot['spot_type']==='lot'?'secondary':'info') ?>">
                <?= ucfirst($spot['spot_type']) ?>
              </span>
            </div>

            <p class="small text-muted mb-2">
              <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($spot['address']) ?>
            </p>

            <div class="d-flex gap-2 mb-2 flex-wrap">
              <?php if ($spot['has_ev_charger']): ?>
                <span class="badge bg-success"><i class="bi bi-lightning-charge me-1"></i>EV</span>
              <?php endif; ?>
              <?php if ($spot['max_height_cm']): ?>
                <span class="badge bg-light text-dark">H: <?= $spot['max_height_cm'] ?>cm</span>
              <?php endif; ?>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <span class="fs-5 fw-bold text-primary"><?= number_format($spot['price_per_hour'],2) ?> EGP</span>
                <small class="text-muted">/hr</small>
              </div>
              <div class="text-end">
                <div class="text-warning small">
                  <?= str_repeat('★', (int)round($spot['trust_score'])) ?><?= str_repeat('☆', 5-(int)round($spot['trust_score'])) ?>
                </div>
                <small class="text-muted">(<?= $spot['total_reviews'] ?> reviews)</small>
              </div>
            </div>

            <?php if ($isOccupied): ?>
              <!-- Occupied: زر Waitlist بس -->
              <div class="d-flex gap-2">
                <a href="<?= $b ?>/index.php?action=spot_detail&id=<?= $spot['spot_id'] ?>"
                   class="btn btn-outline-secondary btn-sm flex-fill">Details</a>
                <a href="<?= $b ?>/index.php?action=waitlist&spot_id=<?= $spot['spot_id'] ?>"
                   class="btn btn-warning btn-sm flex-fill">
                  <i class="bi bi-bell me-1"></i>Watch Spot
                </a>
              </div>
              <?php if ($spot['next_available_at']): ?>
              <p class="text-muted small mt-2 mb-0 text-center">
                <i class="bi bi-clock me-1"></i>
                Available after <?= date('h:i A', strtotime($spot['next_available_at'])) ?>
                (includes 10-min buffer)
              </p>
              <?php endif; ?>
            <?php else: ?>
              <!-- Free: زر Book -->
              <div class="d-flex gap-2">
                <a href="<?= $b ?>/index.php?action=spot_detail&id=<?= $spot['spot_id'] ?>"
                   class="btn btn-outline-primary btn-sm flex-fill">Details</a>
                <a href="<?= $b ?>/index.php?action=book_spot&id=<?= $spot['spot_id'] ?>"
                   class="btn btn-primary btn-sm flex-fill">Book Now</a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  <?php else: ?>
    <div class="text-center py-5">
      <i class="bi bi-map" style="font-size:4rem;color:#ddd;"></i>
      <p class="text-muted mt-3">Use the filters above to find available parking spots</p>
      <?php if (empty($vehicles)): ?>
      <div class="alert alert-warning d-inline-block mt-2">
        <i class="bi bi-car-front me-1"></i>
        <a href="<?= $b ?>/index.php?action=add_vehicle">Add a vehicle first</a> to use smart filters.
      </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>
</div>
</div>

<script>
document.getElementById('vehicleSelect')?.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    document.getElementById('vehicleHeight').value = opt.dataset.height || '';
    document.getElementById('vehicleWidth').value  = opt.dataset.width  || '';
    if (opt.dataset.ev === '1') document.getElementById('needsEv').checked = true;
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
