<?php
// admin/manage_fines.php
require_once __DIR__ . '/../config/session.php';
requireRole('admin');
require_once __DIR__ . '/../classes/Fine.php';

$pageTitle = 'Manage Fines — CitySlot';
$user      = currentUser();
$fineObj   = new Fine();

// Issue manual fine
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'issue') {
    $result = $fineObj->generateAutomatedFine((int)$_POST['driver_id'], (int)$_POST['spot_id'], $user['user_id']);
    setFlash($result['success'] ? 'success' : 'error', $result['message']);
    header('Location: /parking_system/index.php?action=manage_fines'); exit;
}

$status = $_GET['status'] ?? '';
$fines  = $fineObj->listAllFines($status, 200);

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <h4 class="fw-bold mb-4">🚨 Fine Management</h4>

  <!-- STATUS FILTER -->
  <ul class="nav nav-tabs mb-3">
    <?php foreach ([''=>'All','unpaid'=>'Unpaid','paid'=>'Paid','appealed'=>'Appealed','waived'=>'Waived'] as $s=>$l): ?>
    <li class="nav-item"><a class="nav-link <?= $status===$s?'active':'' ?>" href="/parking_system/index.php?action=manage_fines&status=<?= $s ?>"><?= $l ?></a></li>
    <?php endforeach; ?>
  </ul>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Fine#</th><th>Driver</th><th>Spot</th><th>Type</th><th>Amount</th><th>Issued</th><th>Status</th></tr>
        </thead>
        <tbody>
          <?php foreach ($fines as $f): ?>
          <tr>
            <td><small>#<?= $f['fine_id'] ?></small></td>
            <td><?= htmlspecialchars($f['driver_name']) ?></td>
            <td><?= htmlspecialchars($f['spot_title']) ?></td>
            <td><small><?= ucfirst(str_replace('_',' ',$f['fine_type'])) ?></small></td>
            <td class="fw-bold text-danger"><?= number_format($f['amount'],2) ?> EGP</td>
            <td><small><?= date('M d, Y', strtotime($f['issued_at'])) ?></small></td>
            <td>
              <?php $bc=['unpaid'=>'danger','paid'=>'success','appealed'=>'warning','waived'=>'secondary'][$f['status']]??'light'; ?>
              <span class="badge bg-<?= $bc ?>"><?= ucfirst($f['status']) ?></span>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($fines)): ?><tr><td colspan="7" class="text-center text-muted py-4">No fines found.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
