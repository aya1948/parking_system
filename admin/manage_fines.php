<?php
// admin/manage_fines.php
require_once __DIR__ . '/../config/session.php';
requireRole('admin');
require_once __DIR__ . '/../classes/Fine.php';

$pageTitle = 'Manage Fines — Rakna';
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
<style>
/* ألوان Rakna */
.nav-tabs .nav-link {
    background-color: #fff !important;
    color: #480959 !important;
    border: 1px solid #480959;
    border-radius: 0.4rem;
    padding: 0.5rem 1rem;
    margin: 0 0.2rem;
}
.nav-tabs .nav-link.active {
    background-color: #480959 !important;
    color: #ffffff !important;
    font-weight: bold;
}
.nav-tabs .nav-link:hover {
    /* لا تغيير عند التمرير */
    background-color: #fff !important;
    color: #480959 !important;
}
.btn-primary {
    background-color: #480959;
    border-color: #480959;
}
.btn-primary:hover {
    background-color: #8A2888;
    border-color: #8A2888;
}
.card-header {
    background-color: #480959;
    color: #fff;
    font-weight: bold;
}
</style>

<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <h4 class="fw-bold mb-4"><i class="bi bi-exclamation-triangle-fill me-2"></i>Fine Management</h4>

  <!-- QUICK ISSUE FINE -->
  <div class="card mb-4">
    <div class="card-header"><i class="bi bi-plus-circle me-1"></i> Issue Manual Fine</div>
    <div class="card-body">
      <form method="POST" class="row g-3">
        <input type="hidden" name="action" value="issue">
        <div class="col-md-4">
          <label class="form-label">Driver ID</label>
          <input type="number" name="driver_id" class="form-control" placeholder="User ID of driver" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Spot ID</label>
          <input type="number" name="spot_id" class="form-control" placeholder="Parking spot ID" required>
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-circle me-1"></i> Issue Fine</button>
        </div>
      </form>
    </div>
  </div>

  <!-- STATUS FILTER -->
  <ul class="nav nav-tabs mb-3">
    <?php foreach ([''=>'All','unpaid'=>'Unpaid','paid'=>'Paid','appealed'=>'Appealed','waived'=>'Waived'] as $s=>$l): ?>
    <li class="nav-item"><a class="nav-link <?= $status===$s?'active':'' ?>" href="/parking_system/index.php?action=manage_fines&status=<?= $s ?>"><?= $l ?></a></li>
    <?php endforeach; ?>
  </ul>

  <!-- FINES TABLE -->
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