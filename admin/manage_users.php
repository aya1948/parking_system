<?php
// admin/manage_users.php
require_once __DIR__ . '/../config/session.php';
requireRole('admin');
require_once __DIR__ . '/../classes/User.php';

$pageTitle = 'Manage Users — Rakna';
$user      = currentUser();
$userObj   = new User();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action     = $_POST['action'] ?? '';
    $targetId   = (int)($_POST['user_id'] ?? 0);
    if ($action === 'deactivate') {
        $userObj->deleteUser($targetId);
        setFlash('success', 'User deactivated.');
    } elseif ($action === 'lift_blacklist') {
        $userObj->liftBlacklist($targetId, $user['user_id']);
        setFlash('success', 'Blacklist lifted.');
    } elseif ($action === 'blacklist') {
        // Manual blacklist
        $db   = getDB();
        $stmt = $db->prepare("UPDATE users SET is_blacklisted = 1, blacklist_reason = ? WHERE user_id = ?");
        $stmt->execute([$_POST['reason'] ?? 'Admin action', $targetId]);
        setFlash('success', 'User blacklisted.');
    }
    header('Location: /parking_system/index.php?action=manage_users'); exit;
}

$search = trim($_GET['q'] ?? '');
$role   = $_GET['role'] ?? '';
$users  = $search ? $userObj->searchUsers($search, $role) : $userObj->listUsers($role, 100);

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <h4 class="fw-bold mb-4">👥 User Management</h4>

  <!-- SEARCH & FILTER -->
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" class="row g-3">
        <input type="hidden" name="action" value="manage_users">
        <div class="col-md-5">
          <input type="text" name="q" class="form-control" placeholder="Search by name or email..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
          <select name="role" class="form-select">
            <option value="">All Roles</option>
            <option value="driver" <?= $role==='driver'?'selected':'' ?>>Drivers</option>
            <option value="owner"  <?= $role==='owner'?'selected':'' ?>>Owners</option>
            <option value="admin"  <?= $role==='admin'?'selected':'' ?>>Admins</option>
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">Search</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr>
            <td><small><?= $u['user_id'] ?></small></td>
            <td><strong><?= htmlspecialchars($u['full_name']) ?></strong></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><span class="badge badge-role-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
            <td>
              <?php if ($u['is_blacklisted']): ?>
                <span class="badge bg-danger">Blacklisted</span>
              <?php elseif (!$u['is_active']): ?>
                <span class="badge bg-secondary">Inactive</span>
              <?php else: ?>
                <span class="badge bg-success">Active</span>
              <?php endif; ?>
            </td>
            <td><small><?= date('M d, Y', strtotime($u['created_at'])) ?></small></td>
            <td>
              <div class="d-flex gap-1 flex-wrap">
                <?php if ($u['is_blacklisted']): ?>
                  <form method="POST" class="d-inline">
                    <input type="hidden" name="action" value="lift_blacklist">
                    <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                    <button class="btn btn-sm btn-success">Lift Ban</button>
                  </form>
                <?php elseif ($u['user_id'] !== $user['user_id']): ?>
                  <form method="POST" class="d-inline">
                    <input type="hidden" name="action" value="deactivate">
                    <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Deactivate this user?')">Deactivate</button>
                  </form>
                  <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#blacklistModal"
                          onclick="document.getElementById('blUserId').value='<?= $u['user_id'] ?>'">Blacklist</button>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($users)): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">No users found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div></div></div>

<!-- BLACKLIST MODAL -->
<div class="modal fade" id="blacklistModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Blacklist User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <form method="POST">
        <div class="modal-body">
          <input type="hidden" name="action" value="blacklist">
          <input type="hidden" name="user_id" id="blUserId">
          <label class="form-label">Reason for blacklisting</label>
          <textarea name="reason" class="form-control" rows="3" required></textarea>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Confirm Blacklist</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
