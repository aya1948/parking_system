<?php
// admin/appeals.php
require_once __DIR__ . '/../config/session.php';
requireRole('admin');
require_once __DIR__ . '/../classes/Fine.php';
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Fine Appeals — CitySlot';
$user      = currentUser();
$fineObj   = new Fine();
$db        = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $fineObj->reviewAppeal(
        (int)$_POST['appeal_id'],
        $user['user_id'],
        $_POST['decision'],
        trim($_POST['response'] ?? '')
    );
    setFlash($result['success'] ? 'success' : 'error', $result['message']);
    header('Location: /parking_system/index.php?action=appeals'); exit;
}

$stmt = $db->prepare("
    SELECT fa.*, u.full_name AS driver_name, f.amount AS fine_amount, f.fine_type,
           s.title AS spot_title
    FROM fine_appeals fa
    JOIN users u ON fa.driver_id = u.user_id
    JOIN fines f ON fa.fine_id = f.fine_id
    JOIN parking_spots s ON f.spot_id = s.spot_id
    ORDER BY fa.submitted_at DESC
");
$stmt->execute();
$appeals = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <h4 class="fw-bold mb-4">⚖️ Fine Appeals</h4>

  <?php if (empty($appeals)): ?>
    <div class="alert alert-info">No appeals to review.</div>
  <?php else: ?>
  <?php foreach ($appeals as $a): ?>
  <div class="card mb-3">
    <div class="card-body">
      <div class="row">
        <div class="col-md-5">
          <h6 class="fw-bold">Appeal #<?= $a['appeal_id'] ?> — Fine #<?= $a['fine_id'] ?></h6>
          <p class="small mb-1"><strong>Driver:</strong> <?= htmlspecialchars($a['driver_name']) ?></p>
          <p class="small mb-1"><strong>Spot:</strong> <?= htmlspecialchars($a['spot_title']) ?></p>
          <p class="small mb-1"><strong>Fine Amount:</strong> <span class="text-danger"><?= number_format($a['fine_amount'],2) ?> EGP</span></p>
          <p class="small mb-1"><strong>Type:</strong> <?= ucfirst(str_replace('_',' ',$a['fine_type'])) ?></p>
          <p class="small mb-0"><strong>Submitted:</strong> <?= date('M d, Y', strtotime($a['submitted_at'])) ?></p>
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Driver's Statement:</label>
          <p class="bg-light p-2 rounded small"><?= nl2br(htmlspecialchars($a['description'])) ?></p>
          <?php if ($a['evidence_path']): ?>
            <a href="/<?= htmlspecialchars($a['evidence_path']) ?>" target="_blank" class="btn btn-sm btn-outline-info">
              <i class="bi bi-paperclip"></i> View Evidence
            </a>
          <?php endif; ?>
        </div>
        <div class="col-md-3">
          <?php $bc = ['pending'=>'warning','approved'=>'success','rejected'=>'danger'][$a['status']]??'secondary'; ?>
          <span class="badge bg-<?= $bc ?> mb-2"><?= ucfirst($a['status']) ?></span>
          <?php if ($a['status'] === 'pending'): ?>
          <form method="POST">
            <input type="hidden" name="appeal_id" value="<?= $a['appeal_id'] ?>">
            <textarea name="response" class="form-control form-control-sm mb-2" rows="3" placeholder="Admin response..." required></textarea>
            <div class="d-flex gap-2">
              <button type="submit" name="decision" value="approved" class="btn btn-success btn-sm flex-fill">Approve</button>
              <button type="submit" name="decision" value="rejected" class="btn btn-danger btn-sm flex-fill">Reject</button>
            </div>
          </form>
          <?php elseif ($a['admin_response']): ?>
            <p class="small text-muted"><strong>Response:</strong> <?= htmlspecialchars($a['admin_response']) ?></p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
