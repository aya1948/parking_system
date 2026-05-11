<?php
// owner/verify_spot.php — Upload verification documents
require_once __DIR__ . '/../config/session.php';
requireRole('owner');
require_once __DIR__ . '/../classes/ParkingSpot.php';

$pageTitle = 'Spot Verification — Rakna';
$user      = currentUser();
$spotObj   = new ParkingSpot();
$spotId    = (int)($_GET['spot_id'] ?? 0);
$spot      = $spotObj->getSpotById($spotId);

if (!$spot || $spot['owner_id'] != $user['user_id']) {
    setFlash('error', 'Spot not found.'); header('Location: /parking_system/index.php?action=my_spots'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = __DIR__ . '/../uploads/evidence/';
    $idPath    = null;
    $billPath  = null;

    foreach (['id_document' => 'id', 'utility_bill' => 'bill'] as $field => $prefix) {
        if (!empty($_FILES[$field]['name'])) {
            $ext  = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
            $name = $prefix . '_' . $spotId . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES[$field]['tmp_name'], $uploadDir . $name);
            if ($prefix === 'id') $idPath   = 'uploads/evidence/' . $name;
            else                  $billPath  = 'uploads/evidence/' . $name;
        }
    }

    $spotObj->submitVerification($user['user_id'], $spotId, $idPath, $billPath);
    setFlash('success', 'Documents submitted! Awaiting admin approval.');
    header('Location: /parking_system/index.php?action=my_spots'); exit;
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header fw-bold">📋 Verify: <?= htmlspecialchars($spot['title']) ?></div>
        <div class="card-body">
          <div class="alert alert-info">
            To activate your spot, please upload a valid government ID and a utility bill proving ownership/access to this address.
          </div>
          <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
              <label class="form-label fw-semibold">Government Issued ID *</label>
              <input type="file" name="id_document" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
            </div>
            <div class="mb-4">
              <label class="form-label fw-semibold">Utility Bill (electricity/gas) *</label>
              <input type="file" name="utility_bill" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Submit for Verification</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
