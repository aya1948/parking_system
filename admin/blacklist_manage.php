<?php
// admin/blacklist_manage.php
require_once __DIR__ . '/../config/session.php';
requireRole('admin');
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Blacklist Management — CitySlot';
$user      = currentUser();
$b         = BASE_URL;
$userObj   = new User();
$db        = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action']  ?? '';
    $targetId = (int)($_POST['user_id'] ?? 0);
    if ($action === 'lift') {
        $userObj->liftBlacklist($targetId, $user['user_id']);
        setFlash('success', 'Blacklist lifted successfully.');
    } elseif ($action === 'blacklist') {
        $stmt = $db->prepare("UPDATE users SET is_blacklisted=1, blacklist_reason=? WHERE user_id=?");
        $stmt->execute([trim($_POST['reason'] ?? 'Admin action'), $targetId]);
        setFlash('success', 'User blacklisted.');
    }
    header("Location:$b/index.php?action=blacklist_manage"); exit;
}

// Get blacklisted users
$blacklisted = $db->query("
    SELECT u.*, COUNT(f.fine_id) AS unpaid_count
    FROM users u
    LEFT JOIN fines f ON u.user_id = f.driver_id AND f.status = 'unpaid'
    WHERE u.is_blacklisted = 1
    GROUP BY u.user_id
    ORDER BY u.updated_at DESC
")->fetchAll();

// Get users with 2+ unpaid fines (at risk)
$atRisk = $db->query("
    SELECT u.user_id, u.full_name, u.email, COUNT(f.fine_id) AS unpaid_count
    FROM users u
    JOIN fines f ON u.user_id = f.driver_id AND f.status = 'unpaid'
    WHERE u.is_blacklisted = 0
    GROUP BY u.user_id
    HAVING unpaid_count >= 2
    ORDER BY unpaid_count DESC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <h4 class="fw-bold mb-4">🚫 Blacklist Management</h4>

  <!-- AT RISK -->
  <?php if (!empty($atRisk)): ?>
  <div class="card mb-4 border-warning">
    <div class="card-header bg-warning fw-bold">
      ⚠️ At-Risk Users (2+ Unpaid Fines)
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>User</th><th>Email</th><th>Unpaid Fines</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach ($atRisk as $u): ?>
          <tr>
            <td><strong><?= htmlspecialchars($u['full_name']) ?></strong></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><span class="badge bg-warning text-dark fs-6"><?= $u['unpaid_count'] ?></span></td>
            <td>
              <form method="POST" class="d-inline">
                <input type="hidden" name="action"  value="blacklist">
                <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                <input type="hidden" name="reason"  value="Manual admin blacklist: <?= $u['unpaid_count'] ?> unpaid fines.">
                <button class="btn btn-sm btn-danger"
                        onclick="return confirm('Blacklist this user?')">
                  Blacklist Now
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- BLACKLISTED USERS -->
  <div class="card">
    <div class="card-header fw-bold">
      🚫 Currently Blacklisted (<?= count($blacklisted) ?>)
    </div>
    <?php if (empty($blacklisted)): ?>
    <div class="card-body text-muted">No blacklisted users.</div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>User</th><th>Email</th><th>Reason</th><th>Unpaid</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach ($blacklisted as $u): ?>
          <tr class="table-danger">
            <td><strong><?= htmlspecialchars($u['full_name']) ?></strong></td>
            <td><small><?= htmlspecialchars($u['email']) ?></small></td>
            <td><small class="text-muted"><?= htmlspecialchars($u['blacklist_reason'] ?? '—') ?></small></td>
            <td><span class="badge bg-danger"><?= $u['unpaid_count'] ?></span></td>
            <td>
              <form method="POST" class="d-inline">
                <input type="hidden" name="action"  value="lift">
                <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                <button class="btn btn-sm btn-success"
                        onclick="return confirm('Lift blacklist for this user?')">
                  ✅ Lift Ban
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
