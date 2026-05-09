<?php
// owner/add_garage.php
require_once __DIR__ . '/../config/session.php';
requireRole('owner');
require_once __DIR__ . '/../classes/Garage.php';

$pageTitle = 'Add Garage — Rakna';
$user      = currentUser();
$b         = BASE_URL;
$garageObj = new Garage();
$step      = (int)($_GET['step']      ?? 1);
$garageId  = (int)($_GET['garage_id'] ?? 0);
$garage    = null;

// ── STEP 1: Create Garage ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_step'] ?? '') === '1') {
    if (empty(trim($_POST['name'] ?? '')) || empty(trim($_POST['address'] ?? ''))) {
        setFlash('error', 'Garage name and address are required.');
        header("Location: $b/index.php?action=add_garage&step=1"); exit;
    }
    $result = $garageObj->createGarage([
        'owner_id'     => $user['user_id'],
        'name'         => trim($_POST['name']),
        'address'      => trim($_POST['address']),
        'latitude'     => $_POST['latitude']  !== '' ? $_POST['latitude']  : null,
        'longitude'    => $_POST['longitude'] !== '' ? $_POST['longitude'] : null,
        'city_zone'    => trim($_POST['city_zone']   ?? ''),
        'total_floors' => max(1, (int)($_POST['total_floors'] ?? 1)),
        'description'  => trim($_POST['description'] ?? ''),
    ]);
    if ($result['success']) {
        header("Location: $b/index.php?action=add_garage&step=2&garage_id={$result['garage_id']}");
        exit;
    }
    setFlash('error', 'Failed to create garage. Please try again.');
    header("Location: $b/index.php?action=add_garage&step=1"); exit;
}

// ── STEP 2: Generate Spots ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_step'] ?? '') === '2') {
    $gid  = (int)($_POST['garage_id'] ?? 0);
    if ($gid === 0) {
        setFlash('error', 'Garage not found.');
        header("Location: $b/index.php?action=add_garage&step=1"); exit;
    }
    $result = $garageObj->generateSpots($gid, [
        'rows'           => max(1, min(26, (int)($_POST['rows'] ?? 2))),
        'cols'           => max(1, min(50, (int)($_POST['cols'] ?? 10))),
        'price_per_hour' => (float)($_POST['price_per_hour'] ?? 25),
        'has_ev_charger' => isset($_POST['has_ev_charger']) ? 1 : 0,
        'max_height_cm'  => $_POST['max_height_cm'] !== '' ? $_POST['max_height_cm'] : null,
        'max_width_cm'   => $_POST['max_width_cm']  !== '' ? $_POST['max_width_cm']  : null,
        'prefix'         => strtoupper(trim($_POST['prefix'] ?? '')),
    ]);
    if ($result['success']) {
        setFlash('success', "Garage created with {$result['created']} spots!");
        header("Location: $b/index.php?action=garage_map&id=$gid"); exit;
    }
    setFlash('error', 'Error generating spots: ' . ($result['message'] ?? 'Unknown'));
    header("Location: $b/index.php?action=add_garage&step=2&garage_id=$gid"); exit;
}

// Load garage for step 2
if ($step === 2 && $garageId > 0) {
    $garage = $garageObj->getGarageById($garageId);
    if (!$garage || $garage['owner_id'] != $user['user_id']) {
        setFlash('error', 'Garage not found.');
        header("Location: $b/index.php?action=add_garage&step=1"); exit;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-building-add me-2"></i>Add New Garage</h4>
    <a href="<?= $b ?>/index.php?action=my_spots" class="btn btn-outline-secondary btn-sm">← My Spots</a>
  </div>

  <!-- STEPS -->
  <div class="d-flex align-items-center gap-3 mb-4">
    <div class="d-flex align-items-center gap-2">
      <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
           style="width:38px;height:38px;background:<?= $step>=1 ? '#480959' : '#ccc' ?>;">1</div>
      <span class="fw-bold <?= $step>=1 ? 'text-primary' : 'text-muted' ?>">Garage Info</span>
    </div>
    <div style="height:3px;width:80px;background:<?= $step>=2 ? '#480959' : '#ddd' ?>;border-radius:2px;"></div>
    <div class="d-flex align-items-center gap-2">
      <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
           style="width:38px;height:38px;background:<?= $step>=2 ? '#480959' : '#ccc' ?>;">2</div>
      <span class="fw-bold <?= $step>=2 ? 'text-success' : 'text-muted' ?>">Generate Spots</span>
    </div>
  </div>

  <div class="row justify-content-center">
    <div class="col-md-8">

      <?php if ($step === 1): ?>
      <div class="card shadow-sm">
        <div class="card-header fw-bold" style="background:#480959;color:white;"><i class="bi bi-info-circle me-1"></i> Step 1 — Garage Information</div>
        <div class="card-body p-4">
          <form method="POST" action="<?= $b ?>/index.php?action=add_garage">
            <input type="hidden" name="form_step" value="1">
            <div class="mb-3">
              <label class="form-label fw-semibold">Garage Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control form-control-lg"
                     placeholder="e.g. Maadi Central Parking" required
                     value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Full Address <span class="text-danger">*</span></label>
              <input type="text" name="address" class="form-control"
                     placeholder="e.g. 15 Road 9, Maadi, Cairo" required
                     value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
            </div>
            <div class="row g-3 mb-3">
              <div class="col-md-4">
                <label class="form-label">City Zone</label>
                <input type="text" name="city_zone" class="form-control" placeholder="Maadi"
                       value="<?= htmlspecialchars($_POST['city_zone'] ?? '') ?>">
              </div>
              <div class="col-md-4">
                <label class="form-label">Total Floors</label>
                <input type="number" name="total_floors" class="form-control"
                       min="1" max="20" value="<?= (int)($_POST['total_floors'] ?? 1) ?>">
              </div>
              <div class="col-md-4">
                <label class="form-label">Latitude</label>
                <input type="number" name="latitude" class="form-control"
                       step="0.000001" placeholder="29.960278"
                       value="<?= htmlspecialchars($_POST['latitude'] ?? '') ?>">
              </div>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-md-4">
                <label class="form-label">Longitude</label>
                <input type="number" name="longitude" class="form-control"
                       step="0.000001" placeholder="31.249528"
                       value="<?= htmlspecialchars($_POST['longitude'] ?? '') ?>">
              </div>
            </div>
            <div class="mb-4">
              <label class="form-label fw-semibold">Description</label>
              <textarea name="description" class="form-control" rows="3"
                        placeholder="24/7 security, CCTV, covered parking..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-lg w-100" style="background:#480959;border-color:#480959;">
              Next: Configure Spots →
            </button>
          </form>
        </div>
      </div>

      <?php elseif ($step === 2 && $garage): ?>
      <div class="card shadow-sm">
        <div class="card-header fw-bold" style="background:#480959;color:white;"><i class="bi bi-grid-3x3-gap me-1"></i> Step 2 — Generate Parking Spots</div>
        <div class="card-body p-4">
          <div class="alert alert-success d-flex gap-3 align-items-center mb-4">
            <div style="font-size:2rem;"><i class="bi bi-building"></i></div>
            <div>
              <strong><?= htmlspecialchars($garage['name']) ?></strong><br>
              <small><?= htmlspecialchars($garage['address']) ?></small>
            </div>
          </div>
          <form method="POST" action="<?= $b ?>/index.php?action=add_garage">
            <input type="hidden" name="form_step" value="2">
            <input type="hidden" name="garage_id"  value="<?= $garageId ?>">
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Rows <span class="text-danger">*</span>
                  <small class="text-muted">(A, B, C...)</small>
                </label>
                <input type="number" name="rows" id="rows" class="form-control form-control-lg"
                       min="1" max="26" value="2" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Spots per Row <span class="text-danger">*</span></label>
                <input type="number" name="cols" id="cols" class="form-control form-control-lg"
                       min="1" max="50" value="10" required>
              </div>
            </div>
            <!-- LIVE PREVIEW -->
            <div class="card border-primary mb-4">
              <div class="card-header text-primary fw-bold small py-2"><i class="bi bi-eye me-1"></i> Live Preview</div>
              <div class="card-body py-2" id="previewGrid"></div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Floor Prefix <span class="text-muted small">(optional)</span></label>
              <input type="text" name="prefix" id="prefix" class="form-control"
                     placeholder="e.g. 'F1' → F1A1... | blank → A1...">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Price per Hour (EGP) <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">EGP</span>
                <input type="number" name="price_per_hour" class="form-control"
                       step="0.50" min="1" value="25.00" required>
                <span class="input-group-text">/hr</span>
              </div>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label">Max Height (cm)</label>
                <input type="number" name="max_height_cm" class="form-control"
                       step="1" placeholder="blank = no limit">
              </div>
              <div class="col-md-6">
                <label class="form-label">Max Width (cm)</label>
                <input type="number" name="max_width_cm" class="form-control"
                       step="1" placeholder="blank = no limit">
              </div>
            </div>
            <div class="form-check mb-4">
              <input class="form-check-input" type="checkbox" name="has_ev_charger" id="hasEv">
              <label class="form-check-label fw-semibold" for="hasEv"><i class="bi bi-lightning-charge me-1"></i> All spots have EV charging</label>
            </div>
            <div class="d-flex gap-3">
              <a href="<?= $b ?>/index.php?action=add_garage&step=1"
                 class="btn btn-outline-secondary flex-fill">← Back</a>
              <button type="submit" class="btn btn-primary btn-lg flex-fill fw-bold" style="background:#480959;border-color:#480959;">
                <i class="bi bi-check-circle me-1"></i> Create Garage & Spots
              </button>
            </div>
          </form>
        </div>
      </div>
      <?php else: ?>
      <div class="alert alert-danger">
        Something went wrong. <a href="<?= $b ?>/index.php?action=add_garage">Start over</a>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div></div></div>
<script>
function updatePreview() {
    const rows   = Math.min(26, Math.max(1, parseInt(document.getElementById('rows')?.value) || 2));
    const cols   = Math.min(50, Math.max(1, parseInt(document.getElementById('cols')?.value) || 10));
    const prefix = (document.getElementById('prefix')?.value || '').toUpperCase().trim();
    const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const box = document.getElementById('previewGrid');
    if (!box) return;
    let html = '';
    for (let r = 0; r < Math.min(rows, 3); r++) {
        const ltr = prefix + letters[r];
        let spots = '';
        for (let c = 1; c <= Math.min(cols, 6); c++) {
            spots += `<span class="badge me-1 mb-1" style="background:#480959;">${ltr}${c}</span>`;
        }
        if (cols > 6) spots += `<span class="text-muted small">...${ltr}${cols}</span>`;
        html += `<div class="mb-1"><strong>${ltr}:</strong> ${spots}</div>`;
    }
    if (rows > 3) html += `<div class="text-muted small">...${rows-3} more row(s) up to ${prefix}${letters[rows-1]}</div>`;
    html += `<hr class="my-2"><strong class="text-primary">Total: ${rows*cols} spots</strong>
             <span class="text-muted ms-2">(${prefix}${letters[0]}1 → ${prefix}${letters[rows-1]}${cols})</span>`;
    box.innerHTML = html;
}
['rows','cols','prefix'].forEach(id => document.getElementById(id)?.addEventListener('input', updatePreview));
document.addEventListener('DOMContentLoaded', updatePreview);
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>