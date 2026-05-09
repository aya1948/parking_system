<?php
// driver/search_spots.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/Garage.php';
require_once __DIR__ . '/../classes/Vehicle.php';

$pageTitle  = 'Find Parking — Rakna';
$user       = currentUser();
$b          = BASE_URL;
$garageObj  = new Garage();
$vehObj     = new Vehicle();
$vehicles   = $vehObj->listUserVehicles($user['user_id']);
$zones      = $garageObj->listZones();
$garages    = [];
$searched   = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searched = true;
    $filters  = [
        'zone'           => trim($_POST['zone']         ?? ''),
        'max_price'      => $_POST['max_price']         ?? '',
        'needs_ev'       => !empty($_POST['needs_ev'])  ? 1 : 0,
        'vehicle_height' => $_POST['vehicle_height']    ?? '',
        'vehicle_width'  => $_POST['vehicle_width']     ?? '',
        'start_time'     => $_POST['start_time']        ?? date('Y-m-d H:i:s'),
        'end_time'       => $_POST['end_time']          ?? date('Y-m-d H:i:s', strtotime('+1 hour')),
    ];
    $garages = $garageObj->searchGarages($filters);
}

require_once __DIR__ . '/../includes/header.php';
?>
<style>
/* ألوان موحدة مع هوية Rakna */
.btn-primary {
    background-color: #480959;
    border-color: #480959;
    color: #fff;
}
.btn-primary:hover {
    background-color: #8A2888;
    border-color: #8A2888;
    border-left: 3px solid #a1abb9;
}
.btn-outline-primary {
    color: #480959;
    border-color: #480959;
}
.btn-outline-primary:hover {
    background-color: #480959;
    color: #fff;
}
.btn-warning {
    background-color: #480959;
    border-color: #480959;
    color: #fff;
}
.btn-warning:hover {
    background-color: #8A2888;
    border-color: #8A2888;
}
.text-primary {
    color: #480959 !important;
}
.badge.bg-primary {
    background-color: #480959 !important;
}
.badge.bg-success {
    background-color: #198754 !important;
}
/* شريط عنوان المنطقة */
.zone-header i, .zone-header h5 {
    color: #480959 !important;
}
/* البطاقات الفارغة */
.empty-state-icon {
    color: #480959;
}
</style>

<div class="container-fluid px-0">
<div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

<div class="col-md-10 p-4">
  <h4 class="fw-bold mb-4"><i class="bi bi-search me-2"></i>Find Parking</h4>

  <!-- ── FILTER FORM ── -->
  <div class="card mb-4 shadow-sm">
    <div class="card-body">
      <form method="POST">
        <div class="row g-3 mb-3">

          <!-- Zone -->
          <div class="col-md-3">
            <label class="form-label fw-semibold">Zone / Area</label>
            <?php if (!empty($zones)): ?>
            <select name="zone" class="form-select mb-1" id="zoneSelect">
              <option value=""><i class="bi bi-globe"></i> All Zones</option>
              <?php foreach ($zones as $z): ?>
              <option value="<?= htmlspecialchars($z['city_zone']) ?>"
                      <?= ($_POST['zone'] ?? '') === $z['city_zone'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($z['city_zone']) ?> (<?= $z['garage_count'] ?> garage<?= $z['garage_count']>1?'s':'' ?>)
              </option>
              <?php endforeach; ?>
            </select>
            <?php else: ?>
            <input type="text" name="zone" class="form-control"
                   placeholder="e.g. Maadi, Zamalek"
                   value="<?= htmlspecialchars($_POST['zone'] ?? '') ?>">
            <?php endif; ?>
          </div>

          <!-- Times -->
          <div class="col-md-2">
            <label class="form-label fw-semibold">Start Time</label>
            <input type="datetime-local" name="start_time" class="form-control"
                   value="<?= htmlspecialchars($_POST['start_time'] ?? date('Y-m-d\TH:i')) ?>"
                   min="<?= date('Y-m-d\TH:i') ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label fw-semibold">End Time</label>
            <input type="datetime-local" name="end_time" class="form-control"
                   value="<?= htmlspecialchars($_POST['end_time'] ?? date('Y-m-d\TH:i', strtotime('+1 hour'))) ?>">
          </div>

          <!-- Max Price -->
          <div class="col-md-2">
            <label class="form-label fw-semibold">Max Price (EGP/hr)</label>
            <input type="number" name="max_price" class="form-control"
                   placeholder="Any" min="0"
                   value="<?= htmlspecialchars($_POST['max_price'] ?? '') ?>">
          </div>

          <!-- Vehicle -->
          <div class="col-md-3">
            <label class="form-label fw-semibold">Vehicle</label>
            <select name="vehicle_id" class="form-select" id="vehicleSelect">
              <option value="">Select vehicle (optional)</option>
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
        </div>

        <div class="d-flex gap-4 align-items-center">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="needs_ev" id="needsEv"
                   <?= !empty($_POST['needs_ev']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="needsEv"><i class="bi bi-lightning-charge me-1"></i> EV Charger</label>
          </div>
          <button type="submit" class="btn btn-primary px-5">
            <i class="bi bi-search me-1"></i>Search Garages
          </button>
        </div>

        <input type="hidden" name="vehicle_height" id="vehicleHeight"
               value="<?= htmlspecialchars($_POST['vehicle_height'] ?? '') ?>">
        <input type="hidden" name="vehicle_width"  id="vehicleWidth"
               value="<?= htmlspecialchars($_POST['vehicle_width'] ?? '') ?>">
      </form>
    </div>
  </div>

  <!-- ── RESULTS ── -->
  <?php if ($searched): ?>

    <?php if (empty($garages)): ?>
      <div class="alert alert-info">
        <i class="bi bi-info-circle me-1"></i>
        No garages found in this area. Try a different zone or remove filters.
      </div>

    <?php else: ?>

      <!-- SUMMARY -->
      <?php
      $totalFree = array_sum(array_column($garages, 'free_spots'));
      $groupedByZone = [];
      foreach ($garages as $g) {
          $zone = $g['city_zone'] ?: 'Other';
          $groupedByZone[$zone][] = $g;
      }
      ?>
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <span class="badge bg-success fs-6 me-2"><?= $totalFree ?> Free Spots</span>
          <span class="badge bg-secondary fs-6"><?= count($garages) ?> Garage(s)</span>
        </div>
        <small class="text-muted">
          <?= date('h:i A', strtotime($_POST['start_time'] ?? 'now')) ?>
          → <?= date('h:i A', strtotime($_POST['end_time'] ?? '+1 hour')) ?>
        </small>
      </div>

      <!-- ZONES -->
      <?php foreach ($groupedByZone as $zoneName => $zoneGarages): ?>
      <div class="mb-4">
        <!-- Zone Header -->
        <div class="d-flex align-items-center gap-2 mb-3 zone-header">
          <i class="bi bi-geo-alt-fill fs-5"></i>
          <h5 class="fw-bold mb-0"><?= htmlspecialchars($zoneName) ?></h5>
          <span class="badge bg-light text-dark"><?= count($zoneGarages) ?> garage(s)</span>
          <hr class="flex-grow-1 my-0 ms-2">
        </div>

        <!-- Garage Cards -->
        <div class="row g-3">
          <?php foreach ($zoneGarages as $g): ?>
          <?php
          $isFull     = $g['free_spots'] === 0;
          $occupancy  = $g['total_spots'] > 0
                        ? round(($g['total_spots'] - $g['free_spots']) / $g['total_spots'] * 100)
                        : 0;
          $cardBorder = $isFull ? 'border-danger' : 'border-success';
          ?>
          <div class="col-md-4">
            <div class="card h-100 shadow-sm <?= $cardBorder ?>" style="border-width:2px!important;">

              <!-- Status Banner -->
              <div class="text-center py-2 fw-bold text-white"
                   style="background:<?= $isFull ? '#dc3545' : '#28a745' ?>;font-size:13px;">
                <?php if ($isFull): ?>
                  <i class="bi bi-x-circle me-1"></i> FULL — No Available Spots
                <?php else: ?>
                  <i class="bi bi-check-circle me-1"></i> <?= $g['free_spots'] ?> / <?= $g['total_spots'] ?> Spots Available
                <?php endif; ?>
              </div>

              <div class="card-body">
                <!-- Garage Name -->
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div>
                    <h6 class="fw-bold mb-0">
                      <i class="bi bi-building me-1 text-primary"></i>
                      <?= htmlspecialchars($g['name']) ?>
                    </h6>
                    <small class="text-muted">
                      <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($g['address']) ?>
                    </small>
                  </div>
                  <span class="badge bg-primary"><?= htmlspecialchars($g['city_zone'] ?: 'N/A') ?></span>
                </div>

                <!-- Info Grid -->
                <div class="row g-2 mb-3 text-center">
                  <div class="col-4">
                    <div class="fw-bold text-primary">
                      <?php if ($g['min_price'] == $g['max_price']): ?>
                        <?= number_format($g['min_price'], 0) ?>
                      <?php else: ?>
                        <?= number_format($g['min_price'], 0) ?>–<?= number_format($g['max_price'], 0) ?>
                      <?php endif; ?>
                    </div>
                    <small class="text-muted">EGP/hr</small>
                  </div>
                  <div class="col-4">
                    <div class="fw-bold"><?= $g['total_floors'] ?></div>
                    <small class="text-muted">Floor(s)</small>
                  </div>
                  <div class="col-4">
                    <div class="fw-bold"><?= $g['total_spots'] ?></div>
                    <small class="text-muted">Total Spots</small>
                  </div>
                </div>

                <!-- Occupancy Bar -->
                <div class="mb-3">
                  <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">Occupancy</span>
                    <span class="fw-bold <?= $occupancy>80?'text-danger':($occupancy>50?'text-warning':'text-success') ?>">
                      <?= $occupancy ?>%
                    </span>
                  </div>
                  <div class="progress" style="height:8px;">
                    <div class="progress-bar bg-<?= $occupancy>80?'danger':($occupancy>50?'warning':'success') ?>"
                         style="width:<?= $occupancy ?>%;"></div>
                  </div>
                </div>

                <!-- ACTION BUTTONS -->
                <?php if ($isFull): ?>
                <div class="d-flex gap-2">
                  <a href="<?= $b ?>/index.php?action=waitlist&spot_id=<?= $g['garage_id'] ?>"
                     class="btn btn-warning btn-sm flex-fill">
                    <i class="bi bi-bell me-1"></i> Watch Garage
                  </a>
                </div>
                <?php else: ?>
                <a href="<?= $b ?>/index.php?action=pick_spot&garage_id=<?= $g['garage_id'] ?>&start_time=<?= urlencode($_POST['start_time'] ?? '') ?>&end_time=<?= urlencode($_POST['end_time'] ?? '') ?>&vehicle_height=<?= urlencode($_POST['vehicle_height'] ?? '') ?>&vehicle_width=<?= urlencode($_POST['vehicle_width'] ?? '') ?>&needs_ev=<?= urlencode($_POST['needs_ev'] ?? '') ?>"
                   class="btn btn-primary w-100 fw-bold">
                  <i class="bi bi-car-front me-1"></i>Pick a Spot →
                </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>

    <?php endif; ?>

  <?php else: ?>
  <!-- Empty State -->
  <div class="text-center py-5">
    <div style="font-size:5rem;" class="empty-state-icon"><i class="bi bi-building"></i></div>
    <h5 class="mt-3 fw-bold">Find Your Parking</h5>
    <p class="text-muted">Search by zone to see available garages near you</p>
    <?php if (!empty($zones)): ?>
    <div class="d-flex flex-wrap gap-2 justify-content-center mt-3">
      <?php foreach ($zones as $z): ?>
      <button type="button" class="btn btn-outline-primary btn-sm zone-quick-btn"
              data-zone="<?= htmlspecialchars($z['city_zone']) ?>">
        <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($z['city_zone']) ?>
        <span class="badge bg-primary ms-1"><?= $z['garage_count'] ?></span>
      </button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php if (empty($vehicles)): ?>
    <div class="alert alert-warning d-inline-block mt-3">
      <a href="<?= $b ?>/index.php?action=add_vehicle">Add a vehicle</a> first to enable smart filters.
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

</div><!-- /.col -->
</div><!-- /.row -->
</div><!-- /.container -->

<script>
// Auto-fill vehicle dimensions from dropdown
document.getElementById('vehicleSelect')?.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    document.getElementById('vehicleHeight').value = opt.dataset.height || '';
    document.getElementById('vehicleWidth').value  = opt.dataset.width  || '';
    if (opt.dataset.ev === '1') document.getElementById('needsEv').checked = true;
});

// Auto-select default vehicle on load
window.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('vehicleSelect');
    if (sel && sel.value) sel.dispatchEvent(new Event('change'));
});

// Zone quick buttons
document.querySelectorAll('.zone-quick-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const zoneSelect = document.getElementById('zoneSelect');
        if (zoneSelect) {
            for (let opt of zoneSelect.options) {
                if (opt.value === btn.dataset.zone) {
                    opt.selected = true;
                    break;
                }
            }
        }
        document.querySelector('form').submit();
    });
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>