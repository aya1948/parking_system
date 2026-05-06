<?php
// admin/verifications.php
require_once __DIR__ . '/../config/session.php';
requireRole('admin');
require_once __DIR__ . '/../classes/ParkingSpot.php';
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Owner Verifications — CitySlot';
$user      = currentUser();
$spotObj   = new ParkingSpot();
$db        = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $verifId = (int)$_POST['verification_id'];
    $action  = $_POST['action'];
    if ($action === 'approve') {
        $spotObj->approveVerification($verifId, $user['user_id']);
        setFlash('success', 'Spot approved and activated!');
    } else {
        $stmt = $db->prepare("UPDATE owner_verifications SET status = 'rejected', reviewed_by = ?, reviewed_at = NOW(), admin_notes = ? WHERE verification_id = ?");
        $stmt->execute([$user['user_id'], $_POST['notes'] ?? '', $verifId]);
        setFlash('error', 'Verification rejected.');
    }
    header('Location: /parking_system/index.php?action=verifications'); exit;
}

$stmt = $db->prepare("
    SELECT ov.*, u.full_name AS owner_name, u.email AS owner_email,
           s.title AS spot_title, s.address AS spot_address
    FROM owner_verifications ov
    JOIN users u ON ov.owner_id = u.user_id
    JOIN parking_spots s ON ov.spot_id = s.spot_id
    ORDER BY ov.submitted_at DESC
");
$stmt->execute();
$verifications = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <h4 class="fw-bold mb-4">✅ Owner Verification Requests</h4>

  <?php if (empty($verifications)): ?>
    <div class="alert alert-success">No pending verification requests.</div>
  <?php else: ?>
  <?php foreach ($verifications as $v): ?>
  <div class="card mb-3">
    <div class="card-body">
      <div class="row align-items-center">
        <div class="col-md-4">
          <h6 class="fw-bold mb-1"><?= htmlspecialchars($v['spot_title']) ?></h6>
          <p class="small text-muted mb-1"><?= htmlspecialchars($v['spot_address']) ?></p>
          <p class="small mb-0">Owner: <strong><?= htmlspecialchars($v['owner_name']) ?></strong> (<?= htmlspecialchars($v['owner_email']) ?>)</p>
          <small class="text-muted">Submitted: <?= date('M d, Y', strtotime($v['submitted_at'])) ?></small>
        </div>
        <div class="col-md-3">
          <?php if ($v['id_document']): ?>
            <a href="/<?= htmlspecialchars($v['id_document']) ?>" target="_blank" class="btn btn-sm btn-outline-primary mb-1 d-block">
              <i class="bi bi-file-person me-1"></i>View ID Document
            </a>
          <?php endif; ?>
          <?php if ($v['utility_bill']): ?>
            <a href="/<?= htmlspecialchars($v['utility_bill']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary d-block">
              <i class="bi bi-file-text me-1"></i>View Utility Bill
            </a>
          <?php endif; ?>
        </div>
        <div class="col-md-2 text-center">
          <?php $bc = ['pending'=>'warning','approved'=>'success','rejected'=>'danger'][$v['status']]??'secondary'; ?>
          <span class="badge bg-<?= $bc ?> fs-6"><?= ucfirst($v['status']) ?></span>
        </div>
        <div class="col-md-3">
          <?php if ($v['status'] === 'pending'): ?>
          <form method="POST">
            <input type="hidden" name="verification_id" value="<?= $v['verification_id'] ?>">
            <div class="d-flex gap-2 mb-2">
              <button type="submit" name="action" value="approve" class="btn btn-success btn-sm flex-fill">✅ Approve</button>
              <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm flex-fill">❌ Reject</button>
            </div>
            <input type="text" name="notes" class="form-control form-control-sm" placeholder="Notes (if rejecting)">
          </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
