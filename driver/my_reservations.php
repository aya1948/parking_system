<?php
// driver/my_reservations.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/Reservation.php';

$pageTitle   = 'My Reservations — CitySlot';
$user        = currentUser();
$resObj      = new Reservation();
$statusFilter = $_GET['status'] ?? '';
$reservations = $resObj->listDriverReservations($user['user_id'], $statusFilter);

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">📅 My Reservations</h4>
    <a href="/parking_system/index.php?action=search_spots" class="btn btn-primary btn-sm">+ New Booking</a>
  </div>

  <!-- STATUS FILTER TABS -->
  <ul class="nav nav-tabs mb-3">
    <?php foreach ([''=>'All','confirmed'=>'Upcoming','active'=>'Active','completed'=>'Completed','cancelled'=>'Cancelled'] as $s => $label): ?>
    <li class="nav-item">
      <a class="nav-link <?= $statusFilter === $s ? 'active' : '' ?>" href="/parking_system/index.php?action=my_reservations&status=<?= $s ?>">
        <?= $label ?>
      </a>
    </li>
    <?php endforeach; ?>
  </ul>

  <?php if (empty($reservations)): ?>
    <div class="alert alert-info">No reservations found. <a href="/parking_system/index.php?action=search_spots">Book a spot now!</a></div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th><th>Spot</th><th>Start</th><th>End</th><th>Amount</th><th>Status</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($reservations as $r): ?>
        <tr>
          <td><small class="text-muted"><?= $r['reservation_id'] ?></small></td>
          <td>
            <strong><?= htmlspecialchars($r['spot_title']) ?></strong>
            <br><small class="text-muted"><?= htmlspecialchars($r['address']) ?></small>
          </td>
          <td><?= date('M d, Y h:i A', strtotime($r['start_time'])) ?></td>
          <td><?= date('M d, Y h:i A', strtotime($r['end_time'])) ?></td>
          <td class="text-primary fw-bold"><?= number_format($r['total_amount'], 2) ?> EGP</td>
          <td>
            <?php
            $badgeClass = ['confirmed'=>'warning','active'=>'success','completed'=>'secondary','cancelled'=>'danger','no_show'=>'dark','pending'=>'info'][$r['status']] ?? 'light';
            ?>
            <span class="badge bg-<?= $badgeClass ?>"><?= ucfirst(str_replace('_',' ',$r['status'])) ?></span>
          </td>
          <td>
            <div class="d-flex gap-1 flex-wrap">
              <?php if ($r['status'] === 'confirmed'): ?>
                <a href="/parking_system/index.php?action=qr_checkin&id=<?= $r['reservation_id'] ?>" class="btn btn-sm btn-success">Check In</a>
                <a href="/parking_system/index.php?action=cancel_reservation&id=<?= $r['reservation_id'] ?>" class="btn btn-sm btn-outline-danger"
                   onclick="return confirm('Cancel this reservation?')">Cancel</a>
              <?php elseif ($r['status'] === 'active'): ?>
                <a href="/parking_system/index.php?action=qr_checkout&id=<?= $r['reservation_id'] ?>" class="btn btn-sm btn-danger">Check Out</a>
                <a href="/parking_system/index.php?action=extend_reservation&id=<?= $r['reservation_id'] ?>" class="btn btn-sm btn-outline-primary">Extend</a>
              <?php elseif ($r['status'] === 'completed'): ?>
                <a href="/parking_system/index.php?action=submit_review&reservation_id=<?= $r['reservation_id'] ?>" class="btn btn-sm btn-outline-warning">Review</a>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
