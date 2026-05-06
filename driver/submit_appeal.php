<?php
// driver/submit_appeal.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/Fine.php';

$pageTitle = 'Submit Appeal — CitySlot';
$user      = currentUser();
$fineObj   = new Fine();
$fineId    = (int)($_GET['fine_id'] ?? 0);
$fine      = $fineObj->getFineById($fineId);

if (!$fine || $fine['driver_id'] != $user['user_id']) {
    setFlash('error', 'Fine not found.');
    header('Location: /parking_system/index.php?action=my_fines'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description   = trim($_POST['description'] ?? '');
    $evidencePath  = null;

    // Handle file upload
    if (!empty($_FILES['evidence']['name'])) {
        $uploadDir    = __DIR__ . '/../uploads/evidence/';
        $ext          = pathinfo($_FILES['evidence']['name'], PATHINFO_EXTENSION);
        $safeExts     = ['jpg','jpeg','png','pdf'];
        if (in_array(strtolower($ext), $safeExts)) {
            $filename     = 'appeal_' . $fineId . '_' . time() . '.' . $ext;
            $evidencePath = 'uploads/evidence/' . $filename;
            move_uploaded_file($_FILES['evidence']['tmp_name'], $uploadDir . $filename);
        }
    }

    $result = $fineObj->submitAppeal($fineId, $user['user_id'], $description, $evidencePath);
    setFlash($result['success'] ? 'success' : 'error', $result['message']);
    header('Location: /parking_system/index.php?action=my_fines'); exit;
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header fw-bold">⚖️ Appeal Fine #<?= $fineId ?></div>
        <div class="card-body">
          <!-- Fine summary -->
          <div class="alert alert-warning">
            <strong>Fine Amount:</strong> <?= number_format($fine['amount'],2) ?> EGP<br>
            <strong>Spot:</strong> <?= htmlspecialchars($fine['spot_title']) ?><br>
            <strong>Type:</strong> <?= ucfirst(str_replace('_',' ',$fine['fine_type'])) ?><br>
            <strong>Issued:</strong> <?= date('M d, Y', strtotime($fine['issued_at'])) ?>
          </div>

          <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
              <label class="form-label fw-semibold">Describe your appeal *</label>
              <textarea name="description" class="form-control" rows="5"
                placeholder="Explain why this fine should be waived. Include any relevant details..." required></textarea>
            </div>
            <div class="mb-4">
              <label class="form-label fw-semibold">Upload Evidence <span class="text-muted">(optional)</span></label>
              <input type="file" name="evidence" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
              <small class="text-muted">Accepted: JPG, PNG, PDF. Max 5MB.</small>
            </div>
            <button type="submit" class="btn btn-warning w-100 fw-bold">Submit Appeal</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
