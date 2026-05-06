<?php
// driver/my_fines.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/Fine.php';

$pageTitle = 'My Fines — CitySlot';
$user      = currentUser();
$fineObj   = new Fine();
$fines     = $fineObj->listDriverFines($user['user_id']);

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <h4 class="fw-bold mb-4">⚠️ My Fines</h4>

  <?php if (empty($fines)): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>No fines on your record. Keep parking responsibly!</div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead class="table-light">
        <tr><th>Fine #</th><th>Spot</th><th>Type</th><th>Amount</th><th>Date</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($fines as $f): ?>
        <tr>
          <td><small class="text-muted">#<?= $f['fine_id'] ?></small></td>
          <td><?= htmlspecialchars($f['spot_title']) ?></td>
          <td><?= ucfirst(str_replace('_',' ',$f['fine_type'])) ?></td>
          <td class="text-danger fw-bold"><?= number_format($f['amount'],2) ?> EGP</td>
          <td><?= date('M d, Y', strtotime($f['issued_at'])) ?></td>
          <td>
            <?php $bc = ['unpaid'=>'danger','paid'=>'success','appealed'=>'warning','waived'=>'secondary'][$f['status']] ?? 'light'; ?>
            <span class="badge bg-<?= $bc ?>"><?= ucfirst($f['status']) ?></span>
          </td>
          <td>
            <?php if ($f['status'] === 'unpaid'): ?>
              <a href="/parking_system/index.php?action=pay_fine&id=<?= $f['fine_id'] ?>" class="btn btn-sm btn-danger"
                 onclick="return confirm('Pay <?= number_format($f['amount'],2) ?> EGP?')">Pay Now</a>
              <a href="/parking_system/index.php?action=submit_appeal&fine_id=<?= $f['fine_id'] ?>" class="btn btn-sm btn-outline-warning">Appeal</a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
