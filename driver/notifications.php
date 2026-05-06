<?php
// driver/notifications.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/Notification.php';

$pageTitle = 'Notifications — CitySlot';
$user      = currentUser();
$notifObj  = new Notification();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notifObj->markAllRead($user['user_id']);
    setFlash('success', 'All notifications marked as read.');
    header('Location: /parking_system/index.php?action=notifications'); exit;
}

$notifications = $notifObj->getUserNotifications($user['user_id']);
require_once __DIR__ . '/../includes/header.php';

$icons = ['expiry_warning'=>'⏰','penalty_alert'=>'⚠️','booking_confirmed'=>'✅','fine_issued'=>'🚨','waitlist_available'=>'🔔','payout_ready'=>'💰','appeal_update'=>'⚖️','extension_approved'=>'⏱️'];
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">🔔 Notifications</h4>
    <?php if (!empty($notifications)): ?>
    <form method="POST">
      <button class="btn btn-sm btn-outline-secondary">Mark All Read</button>
    </form>
    <?php endif; ?>
  </div>

  <?php if (empty($notifications)): ?>
    <div class="text-center py-5">
      <div style="font-size:4rem;">🔕</div>
      <p class="text-muted mt-3">No notifications yet.</p>
    </div>
  <?php else: ?>
  <div class="card">
    <?php foreach ($notifications as $n): ?>
    <div class="p-3 border-bottom <?= !$n['is_read'] ? 'bg-light' : '' ?>">
      <div class="d-flex gap-3 align-items-start">
        <span class="fs-4"><?= $icons[$n['type']] ?? '📢' ?></span>
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
