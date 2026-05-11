<?php
// owner/add_spot.php
require_once __DIR__ . '/../config/session.php';
requireRole('owner');
require_once __DIR__ . '/../classes/Garage.php';

$pageTitle = 'Add Parking Spot — Rakna';
$user      = currentUser();
$garageObj = new Garage();
$garages   = $garageObj->listOwnerGarages($user['user_id']);

require_once __DIR__ . '/../includes/header.php';
?>
<style>
.btn-primary  { background-color:#480959; border-color:#480959; }
.btn-primary:hover { background-color:#8A2888; border-color:#8A2888; }
.card-header  { background-color:#480959; color:#fff; font-weight:bold; }
.form-label   { font-weight:500; }
</style>

<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <h4 class="fw-bold mb-4"><i class="bi bi-plus-circle me-2"></i>Add Parking Spot</h4>

  <div class="row justify-content-center">
    <div class="col-md-7">
      <div class="card shadow">
        <div class="card-header">Spot Details</div>
        <div class="card-body p-4">
          <form action="<?= BASE_URL ?>/index.php?action=do_add_spot" method="POST">

            <?php if (!empty($garages)): ?>
            <div class="mb-3">
              <label class="form-label">Garage (optional)</label>
              <select name="garage_id" class="form-select">
                <option value="">— Standalone spot (no garage) —</option>
                <?php foreach ($garages as $g): ?>
                <option value="<?= $g['garage_id'] ?>"><?= htmlspecialchars($g['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php endif; ?>

            <div class="mb-3">
              <label class="form-label">Title *</label>
              <input type="text" name="title" class="form-control" required placeholder="e.g. Secure underground spot near Tahrir">
            </div>

            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="2" placeholder="Features, access instructions..."></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label">Address *</label>
              <input type="text" name="address" class="form-control" required placeholder="Full street address">
            </div>

            <div class="row g-2 mb-3">
              <div class="col-6">
                <label class="form-label">Latitude</label>
                <input type="number" step="any" name="latitude" class="form-control" placeholder="30.0444">
              </div>
              <div class="col-6">
                <label class="form-label">Longitude</label>
                <input type="number" step="any" name="longitude" class="form-control" placeholder="31.2357">
              </div>
            </div>

            <div class="row g-2 mb-3">
              <div class="col-6">
                <label class="form-label">Spot Type *</label>
                <select name="spot_type" class="form-select" required>
                  <option value="driveway">Driveway</option>
                  <option value="garage">Garage</option>
                  <option value="lot">Parking Lot</option>
                  <option value="street">Street</option>
                </select>
              </div>
              <div class="col-6">
                <label class="form-label">Price / Hour (EGP) *</label>
                <input type="number" step="0.01" min="0" name="price_per_hour" class="form-control" required placeholder="25.00">
              </div>
            </div>

            <div class="row g-2 mb-3">
              <div class="col-6">
                <label class="form-label">Max Height (cm)</label>
                <input type="number" name="max_height_cm" class="form-control" placeholder="200">
              </div>
              <div class="col-6">
                <label class="form-label">Max Width (cm)</label>
                <input type="number" name="max_width_cm" class="form-control" placeholder="220">
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">City Zone</label>
              <input type="text" name="city_zone" class="form-control" placeholder="e.g. Downtown, Maadi, Heliopolis">
            </div>

            <div class="form-check mb-4">
              <input type="checkbox" name="has_ev_charger" id="evCharger" class="form-check-input" value="1">
              <label class="form-check-label" for="evCharger">Has EV Charger</label>
            </div>

            <button type="submit" class="btn btn-primary w-100">
              <i class="bi bi-plus-circle me-1"></i> List My Spot
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
