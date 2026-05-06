<?php
// admin/promo_codes.php
require_once __DIR__ . '/../config/session.php';
requireRole('admin');
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Promo Codes — CitySlot';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $stmt = $db->prepare("INSERT INTO promo_codes (code, discount_type, discount_value, max_uses, valid_from, valid_until) VALUES (?,?,?,?,?,?)");
        $stmt->execute([strtoupper(trim($_POST['code'])), $_POST['discount_type'], (float)$_POST['discount_value'], (int)$_POST['max_uses'], $_POST['valid_from'], $_POST['valid_until']]);
        setFlash('success', 'Promo code created!');
    } elseif ($action === 'toggle') {
        $stmt = $db->prepare("UPDATE promo_codes SET is_active = NOT is_active WHERE promo_id = ?");
        $stmt->execute([(int)$_POST['promo_id']]);
        setFlash('success', 'Promo code status toggled.');
    }
    header('Location: /parking_system/index.php?action=promo_codes'); exit;
}

$codes = $db->query("SELECT * FROM promo_codes ORDER BY created_at DESC")->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <h4 class="fw-bold mb-4">🏷️ Promo Code Manager</h4>
  <div class="row g-4">
    <div class="col-md-4">
      <div class="card">
        <div class="card-header fw-bold">+ Create Promo Code</div>
        <div class="card-body">
          <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="mb-3"><label class="form-label">Code *</label><input type="text" name="code" class="form-control text-uppercase" placeholder="SUMMER20" required></div>
            <div class="mb-3">
              <label class="form-label">Discount Type</label>
              <select name="discount_type" class="form-select">
                <option value="percentage">Percentage (%)</option>
                <option value="fixed">Fixed Amount (EGP)</option>
              </select>
            </div>
            <div class="mb-3"><label class="form-label">Discount Value *</label><input type="number" name="discount_value" class="form-control" step="0.01" min="1" required></div>
            <div class="mb-3"><label class="form-label">Max Uses</label><input type="number" name="max_uses" class="form-control" value="100" min="1"></div>
            <div class="mb-3"><label class="form-label">Valid From</label><input type="datetime-local" name="valid_from" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Valid Until</label><input type="datetime-local" name="valid_until" class="form-control" required></div>
            <button type="submit" class="btn btn-primary w-100">Create Code</button>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-8">
      <div class="card">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Code</th><th>Type</th><th>Value</th><th>Uses</th><th>Valid Until</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
              <?php foreach ($codes as $c): ?>
              <tr>
                <td><strong class="font-monospace"><?= htmlspecialchars($c['code']) ?></strong></td>
                <td><?= ucfirst($c['discount_type']) ?></td>
                <td><?= $c['discount_type'] === 'percentage' ? $c['discount_value'].'%' : $c['discount_value'].' EGP' ?></td>
                <td><?= $c['current_uses'] ?>/<?= $c['max_uses'] ?></td>
                <td><small><?= date('M d, Y', strtotime($c['valid_until'])) ?></small></td>
                <td><span class="badge bg-<?= $c['is_active'] ? 'success' : 'secondary' ?>"><?= $c['is_active'] ? 'Active' : 'Disabled' ?></span></td>
                <td>
                  <form method="POST" class="d-inline">
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="promo_id" value="<?= $c['promo_id'] ?>">
                    <button class="btn btn-sm btn-outline-secondary"><?= $c['is_active'] ? 'Disable' : 'Enable' ?></button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
