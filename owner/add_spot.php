<?php
// owner/add_spot.php
require_once __DIR__ . '/../config/session.php';
requireRole('owner');
$pageTitle = 'Add Parking Spot — CitySlot';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <div class="row justify-content-center">
    <div class="col-md-7">
      <div class="card">
        <div class="card-header fw-bold">🅿️ List a New Parking Spot</div>
        <div class="card-body">
          <form action="/parking_system/index.php?action=do_add_spot" method="POST">
            <div class="mb-3">
              <label class="form-label fw-semibold">Spot Title *</label>
              <input type="text" name="title" class="form-control" placeholder="e.g. Secure Driveway in Maadi" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Description</label>
              <textarea name="description" class="form-control" rows="3" placeholder="Describe the spot: access instructions, restrictions, etc."></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Address *</label>
              <input type="text" name="address" class="form-control" placeholder="15 El-Lasilky St, Maadi, Cairo" required>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label">Latitude</label>
                <input type="number" name="latitude" class="form-control" step="0.000001" placeholder="29.960278">
              </div>
              <div class="col-6">
                <label class="form-label">Longitude</label>
                <input type="number" name="longitude" class="form-control" step="0.000001" placeholder="31.249528">
              </div>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label">Spot Type *</label>
                <select name="spot_type" class="form-select" required>
                  <option value="driveway">Driveway</option>
                  <option value="lot">Parking Lot</option>
                  <option value="garage">Garage</option>
                  <option value="street">Street</option>
                </select>
              </div>
              <div class="col-6">
                <label class="form-label">City Zone</label>
                <input type="text" name="city_zone" class="form-control" placeholder="e.g. Maadi">
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Price per Hour (EGP) *</label>
              <div class="input-group">
                <span class="input-group-text">EGP</span>
                <input type="number" name="price_per_hour" class="form-control" step="0.50" min="1" placeholder="25.00" required>
                <span class="input-group-text">/hr</span>
              </div>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label">Max Vehicle Height (cm)</label>
                <input type="number" name="max_height_cm" class="form-control" step="0.1" placeholder="Leave blank = any">
              </div>
              <div class="col-6">
                <label class="form-label">Max Vehicle Width (cm)</label>
                <input type="number" name="max_width_cm" class="form-control" step="0.1" placeholder="Leave blank = any">
              </div>
            </div>
            <div class="form-check mb-4">
              <input class="form-check-input" type="checkbox" name="has_ev_charger" id="hasEv">
              <label class="form-check-label" for="hasEv">⚡ This spot has an EV charging station</label>
            </div>
            <div class="alert alert-info small">
              <i class="bi bi-info-circle me-1"></i>
              After submission, you will need to upload a valid ID and utility bill for verification before your spot goes live.
            </div>
            <button type="submit" class="btn btn-primary w-100">Submit Spot for Verification</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
