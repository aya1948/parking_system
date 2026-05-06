<?php
// owner/edit_spot.php
require_once __DIR__ . '/../config/session.php';
requireRole('owner');
require_once __DIR__ . '/../classes/ParkingSpot.php';

$pageTitle = 'Edit Spot — CitySlot';
$user      = currentUser();
$spotObj   = new ParkingSpot();
$spotId    = (int)($_GET['id'] ?? 0);
$spot      = $spotObj->getSpotById($spotId);

if (!$spot || $spot['owner_id'] != $user['user_id']) {
    setFlash('error', 'Spot not found.');
    header('Location: /parking_system/index.php?action=my_spots'); exit;
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <div class="row justify-content-center">
    <div class="col-md-7">
      <div class="card">
        <div class="card-header fw-bold">✏️ Edit: <?= htmlspecialchars($spot['title']) ?></div>
        <div class="card-body">
          <form action="/parking_system/index.php?action=do_edit_spot" method="POST">
            <input type="hidden" name="spot_id" value="<?= $spotId ?>">
            <div class="mb-3">
              <label class="form-label fw-semibold">Spot Title *</label>
              <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($spot['title']) ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Description</label>
              <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($spot['description']) ?></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Address *</label>
              <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($spot['address']) ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Price per Hour (EGP) *</label>
              <div class="input-group">
                <span class="input-group-text">EGP</span>
                <input type="number" name="price_per_hour" class="form-control" step="0.50" min="1"
                       value="<?= $spot['price_per_hour'] ?>" required>
                <span class="input-group-text">/hr</span>
              </div>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label">Max Vehicle Height (cm)</label>
                <input type="number" name="max_height_cm" class="form-control" step="0.1"
                       value="<?= $spot['max_height_cm'] ?>" placeholder="Leave blank = any">
              </div>
              <div class="col-6">
                <label class="form-label">Max Vehicle Width (cm)</label>
                <input type="number" name="max_width_cm" class="form-control" step="0.1"
                       value="<?= $spot['max_width_cm'] ?>" placeholder="Leave blank = any">
              </div>
            </div>
            <div class="form-check mb-4">
              <input class="form-check-input" type="checkbox" name="has_ev_charger" id="hasEv"
                     <?= $spot['has_ev_charger'] ? 'checked' : '' ?>>
              <label class="form-check-label" for="hasEv">⚡ This spot has an EV charging station</label>
            </div>
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary flex-fill">Save Changes</button>
              <a href="/parking_system/index.php?action=my_spots" class="btn btn-outline-secondary flex-fill">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
