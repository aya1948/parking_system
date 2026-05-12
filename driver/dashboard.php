<?php
// driver/dashboard.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/Reservation.php';
require_once __DIR__ . '/../classes/Fine.php';
require_once __DIR__ . '/../classes/Notification.php';

$pageTitle  = 'My Dashboard — Rakna';
$user       = currentUser();
$resObj     = new Reservation();
$fineObj    = new Fine();
$notifObj   = new Notification();

$activeRes  = $resObj->listDriverReservations($user['user_id'], 'active');
$upcomingRes= $resObj->listDriverReservations($user['user_id'], 'confirmed');
$unpaidFines= $fineObj->listDriverFines($user['user_id'], 'unpaid');
$notifs     = $notifObj->getUserNotifications($user['user_id'], true);

require_once __DIR__ . '/../includes/header.php';
?>

<style>
/* ========== تنسيق ألوان الموف للوحة تحكم السائق ========== */
/* أزرار أساسية */
.btn-primary {
    background-color: #480959;
    border-color: #480959;
    color: #fff;
}
.btn-primary:hover {
    background-color: #8A2888;
    border-color: #8A2888;
    border-left: 3px solid #a1abb9;
}

/* أزرار الحدود */
.btn-outline-secondary {
    color: #480959;
    border-color: #480959;
}
.btn-outline-secondary:hover {
    background-color: #480959;
    color: #fff;
    border-left: 3px solid #a1abb9;
}

/* زر الخطر (Pay Fines) */
.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}
.btn-danger:hover {
    background-color: #c82333;
}

/* زر Check Out */
.btn-sm.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}

/* تأثير hover على صفوف القوائم */
.border-bottom:hover {
    background-color: #f3e5f5;
}

/* لون النصوص الأساسية */
.text-primary {
    color: #480959 !important;
}

/* حدود يسارية للبطاقات الإحصائية */
.stat-card {
    border-left: 4px solid #480959;
}
.stat-card.border-danger {
    border-left-color: #dc3545 !important;
}

/* رؤوس الكروت */
.card-header {
    background-color: #480959;
    color: #fff;
}
.card-header a.small {
    color: rgba(255,255,255,0.85);
}
.card-header a.small:hover {
    color: #fff;
}

/* شارة Active */
.badge.bg-success {
    background-color: #480959 !important;
}
</style>

<div class="container-fluid px-0">
<div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

<div class="col-md-10 p-4">
  <h4 class="fw-bold mb-4">Welcome back, <?= htmlspecialchars($user['full_name']) ?>!</h4>

  <!-- STAT CARDS -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card stat-card p-3">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <p class="text-muted small mb-1">Active Parking</p>
            <h3 class="fw-bold mb-0" style="color: #480959;"><?= count($activeRes) ?></h3>
          </div>
          <i class="bi bi-car-front fs-2 opacity-50" style="color: #480959;"></i>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card stat-card p-3">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <p class="text-muted small mb-1">Upcoming</p>
            <h3 class="fw-bold mb-0" style="color: #480959;"><?= count($upcomingRes) ?></h3>
          </div>
          <i class="bi bi-calendar-check fs-2 opacity-50" style="color: #480959;"></i>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card stat-card border-danger p-3">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <p class="text-muted small mb-1">Unpaid Fines</p>
            <h3 class="fw-bold mb-0 text-danger"><?= count($unpaidFines) ?></h3>
          </div>
          <i class="bi bi-exclamation-triangle fs-2 text-danger opacity-50"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- QUICK ACTIONS -->
  <div class="row g-3 mb-4">
    <div class="col">
      <div class="card p-3">
        <h6 class="fw-bold mb-3">Quick Actions</h6>
        <div class="d-flex flex-wrap gap-2">
          <a href="/parking_system/index.php?action=search_spots" class="btn btn-primary"><i class="bi bi-building me-1"></i> Find Garage</a>
          <a href="/parking_system/index.php?action=my_reservations" class="btn btn-outline-secondary"><i class="bi bi-calendar3 me-1"></i> All Reservations</a>
          <a href="/parking_system/index.php?action=my_vehicles" class="btn btn-outline-secondary"><i class="bi bi-car-front me-1"></i> Manage Vehicles</a>
          <?php if (count($unpaidFines) > 0): ?>
          <a href="/parking_system/index.php?action=my_fines" class="btn btn-danger"><i class="bi bi-exclamation-triangle me-1"></i> Pay Fines (<?= count($unpaidFines) ?>)</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <!-- ACTIVE RESERVATIONS -->
    <div class="col-md-6">
      <div class="card">
        <div class="card-header d-flex justify-content-between">
          <span><i class="bi bi-car-front me-1"></i> Active Parking</span>
          <a href="/parking_system/index.php?action=my_reservations" class="small">View All</a>
        </div>
        <div class="card-body p-0">
          <?php if (empty($activeRes)): ?>
            <p class="text-muted p-3 mb-0">No active parking sessions.</p>
          <?php else: ?>
            <?php foreach ($activeRes as $r): ?>
            <div class="p-3 border-bottom">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <strong><?= htmlspecialchars($r['spot_title']) ?></strong>
                  <p class="small text-muted mb-1"><?= htmlspecialchars($r['address']) ?></p>
                  <small>Ends: <strong><?= date('h:i A', strtotime($r['end_time'])) ?></strong></small>
                </div>
                <div class="text-end">
                  <span class="badge bg-success">Active</span>
                  <br><a href="/parking_system/index.php?action=qr_checkout&id=<?= $r['reservation_id'] ?>" class="btn btn-sm btn-danger mt-1">Check Out</a>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- NOTIFICATIONS -->
    <div class="col-md-6">
      <div class="card">
        <div class="card-header d-flex justify-content-between">
          <span><i class="bi bi-bell me-1"></i> Notifications</span>
          <a href="/parking_system/index.php?action=notifications" class="small">View All</a>
        </div>
        <div class="card-body p-0">
          <?php if (empty($notifs)): ?>
            <p class="text-muted p-3 mb-0">No new notifications.</p>
          <?php else: ?>
            <?php foreach (array_slice($notifs, 0, 5) as $n): ?>
            <div class="p-3 border-bottom">
              <div class="d-flex gap-2">
                <span><?= $n['type'] === 'penalty_alert' ? '<i class="bi bi-exclamation-triangle text-danger"></i>' : '<i class="bi bi-info-circle" style="color: #480959;"></i>' ?></span>
                <div>
                  <strong class="small"><?= htmlspecialchars($n['title']) ?></strong>
                  <p class="small text-muted mb-0"><?= htmlspecialchars($n['message']) ?></p>
                  <small class="text-muted"><?= date('M d, h:i A', strtotime($n['created_at'])) ?></small>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div><!-- /.col -->
</div><!-- /.row -->
</div><!-- /.container-fluid -->
<?php require_once __DIR__ . '/../includes/footer.php'; ?>