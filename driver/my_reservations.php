<?php
// driver/my_reservations.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/Reservation.php';

$pageTitle   = 'My Reservations — Rakna';
$user        = currentUser();
$resObj      = new Reservation();
$statusFilter = $_GET['status'] ?? '';
$reservations = $resObj->listDriverReservations($user['user_id'], $statusFilter);

require_once __DIR__ . '/../includes/header.php';
?>
<style>
/* تنسيق خاص بصفحة الحجوزات */
.btn-primary {
    background-color: #480959;
    border-color: #480959;
}
.btn-primary:hover {
    background-color: #8A2888;
    border-color: #8A2888;
}
.btn-success {
    background-color: #480959;
    border-color: #480959;
}
.btn-success:hover {
    background-color: #8A2888;
    border-color: #8A2888;
}
.btn-outline-primary {
    color: #480959;
    border-color: #480959;
}
.btn-outline-primary:hover {
    background-color: #480959;
    color: #fff;
}
.btn-outline-danger {
    color: #dc3545;
    border-color: #dc3545;
}
.btn-outline-danger:hover {
    background-color: #dc3545;
    color: #fff;
}
.btn-outline-warning {
    color: #480959;
    border-color: #480959;
}
.btn-outline-warning:hover {
    background-color: #480959;
    color: #fff;
}
.text-primary {
    color: #480959 !important;
}
.badge.bg-dark {
    background-color: #480959 !important;
}
.table-hover tbody tr:hover {
    background-color: #f3e5f5;
}
/* التبويبات - مثل أزرار السايد بار */
.nav-tabs .nav-link {
    background-color: #480959 !important;
    color: #ffffff !important;
    border: none;
    border-radius: 0.4rem;
    padding: 0.5rem 1rem;
    margin: 0 0.2rem;
    transition: 0.2s;
}
.nav-tabs .nav-link:hover {
    background-color: #8A2888 !important;
    color: #ffffff !important;
    border-left: 3px solid #a1abb9;
}
.nav-tabs .nav-link.active {
    background-color: #480959 !important;
    color: #ffffff !important;
    font-weight: bold;
    border-bottom: 3px solid #a1abb9;
}
.btn-sm.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}
.btn-sm.btn-danger:hover {
    background-color: #c82333;
}
</style>

<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-calendar-check me-2"></i>My Reservations</h4>
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
            <?php if (!empty($r['garage_name'])): ?>
            <small class="text-muted d-block"><i class="bi bi-building me-1"></i><?= htmlspecialchars($r['garage_name']) ?></small>
            <?php endif; ?>
            <strong>
              <?php if (!empty($r['spot_number'])): ?>
              <span class="badge bg-dark font-monospace me-1"><?= htmlspecialchars($r['spot_number']) ?></span>
              <?php endif; ?>
              <?= htmlspecialchars($r['spot_title']) ?>
            </strong>
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
              <?php elseif ($r['status'] === 'extended'): ?>
                <a href="/parking_system/index.php?action=qr_checkout&id=<?= $r['reservation_id'] ?>" class="btn btn-sm btn-danger">
                  <i class="bi bi-box-arrow-right me-1"></i>Check Out
                </a>
                <span class="badge bg-info">Extended</span>
              <?php elseif ($r['status'] === 'completed'): ?>
                <a href="/parking_system/index.php?action=submit_review&reservation_id=<?= $r['reservation_id'] ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-star me-1"></i>Review</a>
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