<?php
// owner/earnings.php
require_once __DIR__ . '/../config/session.php';
requireRole('owner');
require_once __DIR__ . '/../classes/Report.php';
require_once __DIR__ . '/../classes/Pricing.php';
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Earnings — CitySlot';
$user      = currentUser();
$db        = getDB();

// Get all transactions for owner's spots
$stmt = $db->prepare("
    SELECT t.*, r.start_time, r.end_time, s.title AS spot_title, u.full_name AS driver_name,
           t.created_at AS transaction_date
    FROM transactions t
    JOIN reservations r ON t.reservation_id = r.reservation_id
    JOIN parking_spots s ON r.spot_id = s.spot_id
    JOIN users u ON r.driver_id = u.user_id
    WHERE s.owner_id = ?
    ORDER BY t.created_at DESC
    LIMIT 100
");
$stmt->execute([$user['user_id']]);
$transactions = $stmt->fetchAll();

// Totals
$stmt2 = $db->prepare("
    SELECT SUM(t.owner_earnings) AS total_earned,
           SUM(t.platform_fee) AS total_fees,
           COUNT(*) AS total_txn
    FROM transactions t
    JOIN reservations r ON t.reservation_id = r.reservation_id
    JOIN parking_spots s ON r.spot_id = s.spot_id
    WHERE s.owner_id = ? AND t.payment_status = 'released'
");
$stmt2->execute([$user['user_id']]);
$totals = $stmt2->fetch();

// Pending payouts
$stmt3 = $db->prepare("SELECT * FROM owner_payouts WHERE owner_id = ? ORDER BY initiated_at DESC LIMIT 5");
$stmt3->execute([$user['user_id']]);
$payouts = $stmt3->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <h4 class="fw-bold mb-4">💰 Earnings Overview</h4>

  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card stat-card border-success p-3">
        <p class="text-muted small mb-1">Total Net Earned</p>
        <h3 class="fw-bold text-success"><?= number_format($totals['total_earned'] ?? 0, 2) ?> EGP</h3>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card stat-card p-3">
        <p class="text-muted small mb-1">Platform Fees Paid</p>
        <h3 class="fw-bold"><?= number_format($totals['total_fees'] ?? 0, 2) ?> EGP</h3>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card stat-card p-3">
        <p class="text-muted small mb-1">Completed Transactions</p>
        <h3 class="fw-bold"><?= $totals['total_txn'] ?? 0 ?></h3>
      </div>
    </div>
  </div>

  <!-- RECENT TRANSACTIONS -->
  <div class="card mb-4">
    <div class="card-header">📊 Transaction History</div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Date</th><th>Spot</th><th>Driver</th><th>Gross</th><th>Platform Fee</th><th>Your Earnings</th><th>Status</th></tr>
        </thead>
        <tbody>
          <?php foreach ($transactions as $t): ?>
          <tr>
            <td><small><?= date('M d, Y', strtotime($t['transaction_date'])) ?></small></td>
            <td><?= htmlspecialchars($t['spot_title']) ?></td>
            <td><?= htmlspecialchars($t['driver_name']) ?></td>
            <td><?= number_format($t['amount'], 2) ?></td>
            <td class="text-danger">-<?= number_format($t['platform_fee'], 2) ?></td>
            <td class="text-success fw-bold"><?= number_format($t['owner_earnings'], 2) ?></td>
            <td>
              <?php $bc = ['escrow'=>'warning','released'=>'success','refunded'=>'danger'][$t['payment_status']]??'secondary'; ?>
              <span class="badge bg-<?= $bc ?>"><?= ucfirst($t['payment_status']) ?></span>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($transactions)): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">No transactions yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- PAYOUTS -->
  <?php if (!empty($payouts)): ?>
  <div class="card">
    <div class="card-header">💸 Payout History</div>
    <div class="table-responsive">
      <table class="table mb-0">
        <thead class="table-light">
          <tr><th>Period</th><th>Amount</th><th>Status</th><th>Initiated</th></tr>
        </thead>
        <tbody>
          <?php foreach ($payouts as $p): ?>
          <tr>
            <td><?= date('M d', strtotime($p['period_start'])) ?> – <?= date('M d, Y', strtotime($p['period_end'])) ?></td>
            <td class="fw-bold text-success"><?= number_format($p['amount'], 2) ?> EGP</td>
            <td><span class="badge bg-<?= $p['status']==='processed'?'success':'warning' ?>"><?= ucfirst($p['status']) ?></span></td>
            <td><?= date('M d, Y', strtotime($p['initiated_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
