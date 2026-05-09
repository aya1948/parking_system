<?php
// driver/notifications.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/Notification.php';

$pageTitle = 'Notifications — Rakna';
$user      = currentUser();
$notifObj  = new Notification();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notifObj->markAllRead($user['user_id']);
    setFlash('success', 'All notifications marked as read.');
    header('Location: /parking_system/index.php?action=notifications'); exit;
}

$notifications = $notifObj->getUserNotifications($user['user_id']);
require_once __DIR__ . '/../includes/header.php';

// أيقونات Bootstrap Icons المناسبة لكل نوع إشعار
$icons = [
    'expiry_warning'       => 'bi-clock',
    'penalty_alert'        => 'bi-exclamation-triangle',
    'booking_confirmed'    => 'bi-check-circle',
    'fine_issued'          => 'bi-exclamation-diamond',
    'waitlist_available'   => 'bi-bell',
    'payout_ready'         => 'bi-cash-coin',
    'appeal_update'        => 'bi-shield-check',
    'extension_approved'   => 'bi-hourglass-split',
];
$defaultIcon = 'bi-megaphone';
?>
<style>
/* تنسيقات Rakna */
.btn-outline-secondary {
    color: #480959;
    border-color: #480959;
}
.btn-outline-secondary:hover {
    background-color: #480959;
    color: #fff;
}
.text-primary {
    color: #480959 !important;
}
.badge.bg-primary {
    background-color: #480959 !important;
}
.bg-light {
    background-color: #f3e5f5 !important; /* خلفية فاتحة من نفس تدرج الموف */
}
</style>

<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-bell-fill me-2"></i>Notifications</h4>
    <?php if (!empty($notifications)): ?>
    <form method="POST">
      <button class="btn btn-sm btn-outline-secondary">Mark All Read</button>
    </form>
    <?php endif; ?>
  </div>

  <?php if (empty($notifications)): ?>
    <div class="text-center py-5">
      <div style="font-size:4rem;"><i class="bi bi-bell-slash"></i></div>
      <p class="text-muted mt-3">No notifications yet.</p>
    </div>
  <?php else: ?>
  <div class="card">
    <?php foreach ($notifications as $n): ?>
    <div class="p-3 border-bottom <?= !$n['is_read'] ? 'bg-light' : '' ?>">
      <div class="d-flex gap-3 align-items-start">
        <span class="fs-4"><i class="bi <?= $icons[$n['type']] ?? $defaultIcon ?>"></i></span>
        <div class="flex-grow-1">
          <div class="d-flex justify-content-between">
            <strong class="<?= !$n['is_read'] ? 'text-primary' : '' ?>"><?= htmlspecialchars($n['title']) ?></strong>
            <div class="d-flex gap-2 align-items-center">
              <span class="badge bg-<?= $n['channel']==='email'?'info':($n['channel']==='sms'?'warning':'secondary') ?> small"><?= strtoupper($n['channel']) ?></span>
              <small class="text-muted"><?= date('M d, h:i A', strtotime($n['created_at'])) ?></small>
              <?php if (!$n['is_read']): ?><span class="badge bg-primary rounded-pill" style="width:8px;height:8px;padding:0;">&nbsp;</span><?php endif; ?>
            </div>
          </div>
          <p class="mb-0 small text-muted mt-1"><?= htmlspecialchars($n['message']) ?></p>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>