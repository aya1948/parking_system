<?php
// auth/profile.php
require_once __DIR__ . '/../config/session.php';
requireLogin();
require_once __DIR__ . '/../classes/User.php';

$pageTitle = 'My Profile — Rakna';
$user      = currentUser();
$b         = BASE_URL;
$userObj   = new User();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_profile') {
        $result = $userObj->updateProfile($user['user_id'], [
            'full_name'          => trim($_POST['full_name'] ?? ''),
            'phone'              => trim($_POST['phone'] ?? ''),
            'preferred_language' => $_POST['preferred_language'] ?? 'en',
            'preferred_currency' => $_POST['preferred_currency'] ?? 'EGP',
        ]);
        if ($result) {
            // تحديث بيانات الجلسة
            $updated = $userObj->getUserById($user['user_id']);
            $_SESSION['user'] = array_merge($_SESSION['user'], $updated);
            setFlash('success', 'Profile updated successfully!');
        } else {
            setFlash('error', 'Failed to update profile.');
        }
    } elseif ($action === 'change_password') {
        $result = $userObj->changePassword(
            $user['user_id'],
            $_POST['old_password'] ?? '',
            $_POST['new_password'] ?? ''
        );
        setFlash($result['success'] ? 'success' : 'error', $result['message']);
    }
    header("Location: $b/index.php?action=profile"); exit;
}

$userData = $userObj->getUserById($user['user_id']);
require_once __DIR__ . '/../includes/header.php';
?>
<style>
/* ألوان Rakna */
.btn-primary {
    background-color: #480959;
    border-color: #480959;
}
.btn-primary:hover {
    background-color: #8A2888;
    border-color: #8A2888;
}
.btn-warning {
    background-color: #480959;
    border-color: #480959;
    color: #fff;
}
.btn-warning:hover {
    background-color: #8A2888;
    border-color: #8A2888;
}
.btn-outline-danger {
    color: #dc3545;
    border-color: #dc3545;
}
.btn-outline-danger:hover {
    background-color: #dc3545;
    color: #fff;
}
.card-header {
    background-color: #480959;
    color: #fff;
    font-weight: bold;
}
.badge.bg-success {
    background-color: #480959 !important;
}
</style>

<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <h4 class="fw-bold mb-4"><i class="bi bi-person-circle me-2"></i>My Profile</h4>
  <div class="row g-4">

    <!-- PROFILE INFO -->
    <div class="col-md-6">
      <div class="card">
        <div class="card-header"><i class="bi bi-pencil-square me-1"></i> Personal Information</div>
        <div class="card-body">
          <form method="POST">
            <input type="hidden" name="action" value="update_profile">
            <div class="mb-3">
              <label class="form-label">Full Name</label>
              <input type="text" name="full_name" class="form-control"
                     value="<?= htmlspecialchars($userData['full_name']) ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" class="form-control"
                     value="<?= htmlspecialchars($userData['email']) ?>" disabled>
              <small class="text-muted">Email cannot be changed.</small>
            </div>
            <div class="mb-3">
              <label class="form-label">Phone</label>
              <input type="tel" name="phone" class="form-control"
                     value="<?= htmlspecialchars($userData['phone'] ?? '') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Preferred Language</label>
              <select name="preferred_language" class="form-select">
                <option value="en" <?= ($userData['preferred_language']??'en')==='en'?'selected':'' ?>>English</option>
                <option value="ar" <?= ($userData['preferred_language']??'en')==='ar'?'selected':'' ?>>العربية</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Preferred Currency</label>
              <select name="preferred_currency" class="form-select">
                <?php
                $currencies = [
                    'EGP' => 'EGP (Egyptian Pound)',
                    'USD' => 'USD (US Dollar)',
                    'EUR' => 'EUR (Euro)',
                    'SAR' => 'SAR (Saudi Riyal)',
                    'AED' => 'AED (UAE Dirham)'
                ];
                foreach ($currencies as $code => $label) {
                    $selected = ($userData['preferred_currency'] ?? 'EGP') === $code ? 'selected' : '';
                    echo "<option value=\"$code\" $selected>$label</option>";
                }
                ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Role</label>
              <div><span class="badge bg-secondary fs-6"><?= ucfirst($userData['role']) ?></span></div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Save Changes</button>
          </form>
        </div>
      </div>
    </div>

    <!-- CHANGE PASSWORD -->
    <div class="col-md-6">
      <div class="card">
        <div class="card-header"><i class="bi bi-lock me-1"></i> Change Password</div>
        <div class="card-body">
          <form method="POST">
            <input type="hidden" name="action" value="change_password">
            <div class="mb-3">
              <label class="form-label">Current Password</label>
              <input type="password" name="old_password" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">New Password</label>
              <input type="password" name="new_password" class="form-control" minlength="8" required>
              <small class="text-muted">Minimum 8 characters.</small>
            </div>
            <button type="submit" class="btn btn-warning w-100">Update Password</button>
          </form>
        </div>
      </div>

      <!-- ACCOUNT INFO -->
      <div class="card mt-3">
        <div class="card-header"><i class="bi bi-info-circle me-1"></i> Account Info</div>
        <div class="card-body">
          <p class="mb-1"><small class="text-muted">Member since:</small><br>
            <strong><?= date('F d, Y', strtotime($userData['created_at'])) ?></strong></p>
          <p class="mb-1"><small class="text-muted">Account Status:</small><br>
            <span class="badge bg-<?= $userData['is_blacklisted'] ? 'danger' : 'success' ?>">
              <?= $userData['is_blacklisted'] ? 'Suspended' : 'Active' ?>
            </span></p>
          <?php if ($userData['is_blacklisted']): ?>
          <div class="alert alert-danger mt-2 small">
            Your account is suspended. Please pay all outstanding fines.
          </div>
          <?php endif; ?>
          <hr>
          <a href="<?= $b ?>/index.php?action=logout" class="btn btn-outline-danger w-100">
            <i class="bi bi-box-arrow-right me-1"></i>Logout
          </a>
        </div>
      </div>
    </div>
  </div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>