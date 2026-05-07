<?php
// owner/add_garage.php
require_once __DIR__ . '/../config/session.php';
requireRole('owner');
require_once __DIR__ . '/../classes/Garage.php';

$pageTitle   = 'Add Garage — CitySlot';
$user        = currentUser();
$b           = BASE_URL;
$garageObj   = new Garage();
$step        = (int)($_GET['step'] ?? 1);
$garageId    = (int)($_GET['garage_id'] ?? 0);

// STEP 1: Create Garage info
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_step'] === '1') {
    $result = $garageObj->createGarage([
        'owner_id'     => $user['user_id'],
        'name'         => trim($_POST['name']),
        'address'      => trim($_POST['address']),
        'latitude'     => $_POST['latitude']     ?: null,
        'longitude'    => $_POST['longitude']    ?: null,
        'city_zone'    => trim($_POST['city_zone']),
        'total_floors' => (int)($_POST['total_floors'] ?? 1),
        'description'  => trim($_POST['description']),
    ]);
    if ($result['success']) {
        header("$b/index.php?action=add_garage&step=2&garage_id={$result['garage_id']}");
        exit;
    }
    setFlash('error', 'Failed to create garage.');
}

// STEP 2: Generate spots
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_step'] === '2') {
    $gid    = (int)$_POST['garage_id'];
    $result = $garageObj->generateSpots($gid, [
        'rows'           => (int)$_POST['rows'],
        'cols'           => (int)$_POST['cols'],
        'price_per_hour' => (float)$_POST['price_per_hour'],
        'has_ev_charger' => isset($_POST['has_ev_charger']) ? 1 : 0,
        'max_height_cm'  => $_POST['max_height_cm'] ?: null,
        'max_width_cm'   => $_POST['max_width_cm']  ?: null,
        'prefix'         => trim($_POST['prefix']   ?? ''),
    ]);
    if ($result['success']) {
        setFlash('success', "Garage created with {$result['created']} spots!");
        header("Location: $b/index.php?action=garage_map&id=$gid");
        exit;
    }
    setFlash('error', $result['message']);
}

$garage = $garageId ? $garageObj->getGarageById($garageId) : null;
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">

  <!-- STEPS INDICATOR -->
  <div class="d-flex align-items-center gap-3 mb-4">
    <div class="d-flex align-items-center gap-2">
      <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
           style="width:36px;height:36px;background:<?= $step>=1?'#1a73e8':'#ddd' ?>;color:white;">1</div>
      <span class="fw-bold <?= $step>=1?'text-primary':'' ?>">Garage Info</span>
    </div>
    <div style="height:2px;width:60px;background:<?= $step>=2?'#1a73e8':'#ddd' ?>;"></div>
    <div class="d-flex align-items-center gap-2">
      <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
           style="width:36px;height:36px;background:<?= $step>=2?'#1a73e8':'#ddd' ?>;color:white;">2</div>
      <span class="fw-bold <?= $step>=2?'text-primary':'text-muted' ?>">Generate Spots</span>
    </div>
  </div>

  <div class="row justify-content-center">
    <div class="col-md-7">

      <?php if ($step === 1): ?>
      <!-- STEP 1: Garage Details -->
      <div class="card">
        <div class="card-header fw-bold">🏢 Step 1: Garage Information</div>
        <div class="card-body">
          <form method="POST" action="<?= $b ?>/index.php?action=add_garage&step=1">
            <input type="hidden" name="form_step" value="1">
            <div class="mb-3">
              <label class="form-label fw-semibold">Garage Name *</label>
              <input type="text" name="name" class="form-control"
                     placeholder="e.g. Maadi Central Parking" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Address *</label>
              <input type="text" name="address" class="form-control"
                     placeholder="15 Road 9, Maadi, Cairo" required>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label">Latitude</label>
                <input type="number" name="latitude" class="form-control"
                       step="0.000001" placeholder="29.960278">
              </div>
              <div class="col-6">
                <label class="form-label">Longitude</label>
                <input type="number" name="longitude" class="form-control"
                       step="0.000001" placeholder="31.249528">
              </div>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label">City Zone</label>
                <input type="text" name="city_zone" class="form-control" placeholder="Maadi">
              </div>
              <div class="col-6">
                <label class="form-label">Total Floors</label>
                <input type="number" name="total_floors" class="form-control"
                       min="1" max="20" value="1">
              </div>
            </div>
            <div class="mb-4">
              <label class="form-label fw-semibold">Description</label>
              <textarea name="description" class="form-control" rows="3"
                        placeholder="24/7 security, CCTV, covered parking..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">
              Next: Generate Spots →
            </button>
          </form>
        </div>
      </div>

      <?php elseif ($step === 2 && $garage): ?>
      <!-- STEP 2: Generate Spots -->
      <div class="card">
        <div class="card-header fw-bold">🅿️ Step 2: Generate Parking Spots</div>
        <div class="card-body">
          <div class="alert alert-info mb-4">
            <strong>Garage:</strong> <?= htmlspecialchars($garage['name']) ?><br>
            <strong>Address:</strong> <?= htmlspecialchars($garage['address']) ?>
          </div>

          <form method="POST" action="<?= $b ?>/index.php?action=add_garage&step=2">
            <input type="hidden" name="form_step" value="2">
            <input type="hidden" name="garage_id"  value="<?= $garageId ?>">

            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label fw-semibold">Number of Rows *</label>
                <input type="number" name="rows" id="rows" class="form-control"
                       min="1" max="26" value="2" required>
                <small class="text-muted">Each row = one letter (A, B, C...)</small>
              </div>
              <div class="col-6">
                <label class="form-label fw-semibold">Spots per Row *</label>
                <input type="number" name="cols" id="cols" class="form-control"
                       min="1" max="50" value="10" required>
                <small class="text-muted">e.g. 10 → A1 to A10</small>
              </div>
            </div>

            <!-- LIVE PREVIEW -->
            <div class="card bg-light mb-3 p-3" id="previewBox">
              <p class="fw-bold mb-2">📋 Preview</p>
              <div id="previewText" class="font-monospace small text-primary"></div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Row Prefix <span class="text-muted">(optional)</span></label>
              <input type="text" name="prefix" class="form-control"
                     placeholder="Leave blank, or type 'G1' for G1A1, G1B1...">
              <small class="text-muted">Useful for multi-floor garages</small>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Price per Hour (EGP) *</label>
              <div class="input-group">
                <span class="input-group-text">EGP</span>
                <input type="number" name="price_per_hour" class="form-control"
                       step="0.50" min="1" value="25.00" required>
                <span class="input-group-text">/hr</span>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label">Max Vehicle Height (cm)</label>
                <input type="number" name="max_height_cm" class="form-control"
                       step="0.1" placeholder="e.g. 200">
              </div>
              <div class="col-6">
                <label class="form-label">Max Vehicle Width (cm)</label>
                <input type="number" name="max_width_cm" class="form-control"
                       step="0.1" placeholder="e.g. 220">
              </div>
            </div>

            <div class="form-check mb-4">
              <input class="form-check-input" type="checkbox" name="has_ev_charger" id="hasEv">
              <label class="form-check-label" for="hasEv">
                ⚡ All spots have EV charging stations
              </label>
            </div>

            <button type="submit" class="btn btn-success w-100 fw-bold">
              ✅ Create Garage with Spots
            </button>
          </form>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div></div></div>

<script>
function updatePreview() {
    const rows = parseInt(document.getElementById('rows')?.value || 2);
    const cols = parseInt(document.getElementById('cols')?.value || 10);
    const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');
    let html = '';
    let total = 0;
    for (let r = 0; r < Math.min(rows, 4); r++) {
        let rowSpots = [];
        for (let c = 1; c <= Math.min(cols, 6); c++) {
            rowSpots.push(letters[r] + c);
            total++;
        }
        if (cols > 6) rowSpots.push('...' + letters[r] + cols);
        html += rowSpots.join(' | ') + '<br>';
    }
    if (rows > 4) html += `... up to row ${letters[rows-1]}<br>`;
    html += `<hr class="my-1"><strong>Total: ${rows * cols} spots</strong> (${letters[0]}1 → ${letters[rows-1]}${cols})`;
    const box = document.getElementById('previewText');
    if (box) box.innerHTML = html;
}
document.getElementById('rows')?.addEventListener('input', updatePreview);
document.getElementById('cols')?.addEventListener('input', updatePreview);
updatePreview();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
