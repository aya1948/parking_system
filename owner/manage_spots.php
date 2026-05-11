<?php
// owner/manage_spots.php
require_once __DIR__ . '/../config/session.php';
requireRole('owner');
require_once __DIR__ . '/../classes/Garage.php';
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Manage Spots — Rakna';
$user      = currentUser();
$b         = BASE_URL;
$garageObj = new Garage();
$db        = getDB();

$garageId  = (int)($_GET['garage_id'] ?? 0);

// ── إذا لم يتم تحديد جراج، نعرض قائمة الجراجات ─────────────────
if ($garageId === 0) {
    $garages = $garageObj->listOwnerGarages($user['user_id']);
    require_once __DIR__ . '/../includes/header.php';
    ?>
    <style>
    .btn-primary { background-color:#480959; border-color:#480959; }
    .btn-primary:hover { background-color:#8A2888; border-color:#8A2888; }
    .card-header { background-color:#480959; color:#fff; font-weight:bold; }
    </style>

    <div class="container-fluid px-0"><div class="row g-0">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="col-md-10 p-4">
        <h4 class="fw-bold mb-4"><i class="bi bi-sliders me-2"></i>Manage Spots</h4>
        <p class="text-muted mb-4">Select a garage to manage its spots.</p>

        <?php if (empty($garages)): ?>
            <div class="text-center py-5">
                <div style="font-size:4rem;"><i class="bi bi-building"></i></div>
                <h5 class="mt-3 fw-bold">No Garages Yet</h5>
                <p class="text-muted">Add your first garage to start managing spots.</p>
                <a href="<?= $b ?>/index.php?action=add_garage" class="btn btn-primary btn-lg">Add New Garage</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($garages as $g): ?>
                <div class="col-md-4">
                    <a href="<?= $b ?>/index.php?action=manage_spots&garage_id=<?= $g['garage_id'] ?>" class="text-decoration-none">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <i class="bi bi-building fs-1" style="color:#480959;"></i>
                                <h5 class="fw-bold mt-2"><?= htmlspecialchars($g['name']) ?></h5>
                                <p class="text-muted small"><?= htmlspecialchars($g['address']) ?></p>
                                <span class="badge bg-primary"><?= $g['total_spots'] ?> spots</span>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div></div></div>
    <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    <?php
    exit;
}

// ── التحقق من ملكية الجراج ────────────────────────────────────
$garage = $garageObj->getGarageById($garageId);
if (!$garage || $garage['owner_id'] != $user['user_id']) {
    setFlash('error', 'Garage not found.');
    header("Location: $b/index.php?action=my_spots"); exit;
}

// ─── معالجة الإجراءات ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actionType = $_POST['action_type'] ?? '';

    if ($actionType === 'bulk_update' && !empty($_POST['spot_ids'])) {
        $spotIds     = array_map('intval', $_POST['spot_ids']);
        $newPrice    = $_POST['new_price'] !== '' ? (float)$_POST['new_price'] : null;
        $newStatus   = $_POST['new_status'] ?? null;
        $updated     = 0;

        foreach ($spotIds as $spotId) {
            $stmt = $db->prepare("SELECT spot_id FROM parking_spots WHERE spot_id = ? AND garage_id = ?");
            $stmt->execute([$spotId, $garageId]);
            if (!$stmt->fetch()) continue;

            if ($newPrice !== null) {
                $stmt = $db->prepare("UPDATE parking_spots SET price_per_hour = ? WHERE spot_id = ?");
                $stmt->execute([$newPrice, $spotId]);
                $updated++;
            }
            if ($newStatus !== null && in_array($newStatus, ['available','unavailable','maintenance','owner_use'])) {
                $stmt = $db->prepare("UPDATE parking_spots SET status = ? WHERE spot_id = ?");
                $stmt->execute([$newStatus, $spotId]);
                $updated++;
            }
        }
        setFlash('success', "Updated $updated spot(s).");
        header("Location: $b/index.php?action=manage_spots&garage_id=$garageId"); exit;
    }

    if ($actionType === 'delete_spots' && !empty($_POST['spot_ids'])) {
        $spotIds = array_map('intval', $_POST['spot_ids']);
        $deleted = 0;
        foreach ($spotIds as $spotId) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM reservations WHERE spot_id = ? AND status IN ('confirmed','active','extended')");
            $stmt->execute([$spotId]);
            $activeBookings = (int)$stmt->fetchColumn();
            if ($activeBookings === 0) {
                // تغيير الحالة إلى inactive بدلاً من الحذف الفعلي
                $stmt = $db->prepare("UPDATE parking_spots SET status = 'inactive' WHERE spot_id = ?");
                $stmt->execute([$spotId]);
                $deleted++;
            }
        }
        setFlash($deleted > 0 ? 'success' : 'error', $deleted > 0 ? "Deactivated $deleted spot(s)." : "Could not deactivate spots with active reservations.");
        header("Location: $b/index.php?action=manage_spots&garage_id=$garageId"); exit;
    }

    if ($actionType === 'add_spot') {
        $spotNumber    = trim($_POST['spot_number'] ?? '');
        $pricePerHour  = (float)($_POST['price_per_hour'] ?? 0);
        $spotType      = $_POST['spot_type'] ?? 'standard';
        $hasEV         = isset($_POST['has_ev_charger']) ? 1 : 0;
        $maxHeight     = $_POST['max_height_cm'] ?: null;
        $maxWidth      = $_POST['max_width_cm'] ?: null;

        if ($spotNumber === '') {
            setFlash('error', 'Spot number is required.');
        } else {
            $stmt = $db->prepare("INSERT INTO parking_spots (garage_id, owner_id, spot_number, title, spot_type, price_per_hour, has_ev_charger, max_height_cm, max_width_cm, status) VALUES (?,?,?,?,?,?,?,?,?,'available')");
            $stmt->execute([$garageId, $user['user_id'], $spotNumber, "Spot $spotNumber", $spotType, $pricePerHour, $hasEV, $maxHeight, $maxWidth]);
            setFlash('success', 'Spot added successfully.');
        }
        header("Location: $b/index.php?action=manage_spots&garage_id=$garageId"); exit;
    }
}

// ── جلب مواقف الجراج ─────────────────────────────────────────
$stmt = $db->prepare("
    SELECT s.*, 
           CASE 
             WHEN s.status = 'inactive' THEN 'inactive'
             WHEN EXISTS (SELECT 1 FROM reservations r WHERE r.spot_id = s.spot_id AND r.status IN ('confirmed','active','extended')) THEN 'occupied'
             ELSE s.status
           END AS display_status
    FROM parking_spots s
    WHERE s.garage_id = ? AND s.status != 'inactive'
    ORDER BY s.spot_number ASC
");
$stmt->execute([$garageId]);
$spots = $stmt->fetchAll();

$statuses = [
    'available'   => 'Available',
    'maintenance' => 'Maintenance',
    'unavailable' => 'Unavailable',
    'owner_use'   => 'Owner Use',
];

require_once __DIR__ . '/../includes/header.php';
?>
<style>
.btn-primary { background-color:#480959; border-color:#480959; }
.btn-primary:hover { background-color:#8A2888; border-color:#8A2888; }
.btn-outline-primary { color:#480959; border-color:#480959; }
.btn-outline-primary:hover { background-color:#480959; color:#fff; }
.btn-danger { background-color:#dc3545; border-color:#dc3545; }
.btn-success { background-color:#198754; border-color:#198754; }
.card-header { background-color:#480959; color:#fff; font-weight:bold; }
.table-hover tbody tr:hover { background-color:#f3e5f5; }
.badge.bg-dark { background-color:#480959 !important; }
</style>

<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-0"><i class="bi bi-grid-3x3-gap me-2"></i>Manage Spots</h4>
      <p class="text-muted mb-0"><i class="bi bi-building me-1"></i><?= htmlspecialchars($garage['name']) ?></p>
    </div>
    <a href="<?= $b ?>/index.php?action=garage_map&id=<?= $garageId ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-map me-1"></i> View Map</a>
  </div>

  <!-- ── إضافة موقف جديد ── -->
  <div class="card mb-4">
    <div class="card-header"><i class="bi bi-plus-circle me-1"></i> Add Single Spot</div>
    <div class="card-body">
      <form method="POST" class="row g-3">
        <input type="hidden" name="action_type" value="add_spot">
        <div class="col-md-2">
          <label class="form-label">Spot Number *</label>
          <input type="text" name="spot_number" class="form-control" placeholder="e.g. A12" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Type</label>
          <select name="spot_type" class="form-select">
            <option value="standard">Standard</option>
            <option value="compact">Compact</option>
            <option value="large">Large</option>
            <option value="motorcycle">Motorcycle</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Price / Hour</label>
          <input type="number" name="price_per_hour" class="form-control" value="25.00" step="0.50" min="1" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Max H (cm)</label>
          <input type="number" name="max_height_cm" class="form-control" placeholder="Optional">
        </div>
        <div class="col-md-2">
          <label class="form-label">Max W (cm)</label>
          <input type="number" name="max_width_cm" class="form-control" placeholder="Optional">
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="has_ev_charger" id="newEv">
            <label class="form-check-label" for="newEv">EV Charger</label>
          </div>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Add Spot</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ── جدول المواقف ── -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span><i class="bi bi-list-ul me-1"></i> Garage Spots (<?= count($spots) ?>)</span>
      <span class="small text-white">Select spots to apply bulk actions</span>
    </div>

    <?php if (empty($spots)): ?>
      <div class="card-body text-muted text-center py-4">No spots in this garage yet. Add one above or <a href="<?= $b ?>/index.php?action=add_garage&step=2&garage_id=<?= $garageId ?>">generate batch</a>.</div>
    <?php else: ?>

    <form method="POST" id="bulkForm">
      <input type="hidden" name="action_type" id="bulkAction" value="bulk_update">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th><input type="checkbox" id="selectAll" class="form-check-input"></th>
              <th>Spot Number</th>
              <th>Type</th>
              <th>Price (EGP/hr)</th>
              <th>Dimensions</th>
              <th>EV</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($spots as $s): ?>
            <tr>
              <td><input type="checkbox" name="spot_ids[]" value="<?= $s['spot_id'] ?>" class="form-check-input spot-check"></td>
              <td><span class="badge bg-dark font-monospace"><?= htmlspecialchars($s['spot_number']) ?></span></td>
              <td><?= ucfirst($s['spot_type'] ?? 'standard') ?></td>
              <td><?= number_format($s['price_per_hour'], 2) ?> EGP</td>
              <td><?= ($s['max_height_cm'] ?: '—') ?> x <?= ($s['max_width_cm'] ?: '—') ?> cm</td>
              <td><?= $s['has_ev_charger'] ? '<i class="bi bi-lightning-charge text-success"></i> Yes' : '<span class="text-muted">No</span>' ?></td>
              <td>
                <?php
                $statusClass = match($s['display_status']) {
                    'occupied'    => 'danger',
                    'maintenance' => 'warning',
                    'unavailable' => 'secondary',
                    'owner_use'   => 'info',
                    default       => 'success',
                };
                ?>
                <span class="badge bg-<?= $statusClass ?>"><?= ucfirst(str_replace('_',' ',$s['display_status'])) ?></span>
              </td>
              <td>
                <a href="<?= $b ?>/index.php?action=edit_spot&id=<?= $s['spot_id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                <a href="<?= $b ?>/index.php?action=delete_spot&id=<?= $s['spot_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Deactivate this spot?')"><i class="bi bi-trash"></i></a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- شريط الإجراءات الجماعية -->
      <div class="card-footer bg-light d-flex flex-wrap gap-3 align-items-center">
        <div class="d-flex align-items-center gap-2">
          <label class="form-label mb-0 fw-semibold">New Price:</label>
          <input type="number" name="new_price" class="form-control form-control-sm" style="width:120px;" step="0.50" min="0" placeholder="Leave empty">
          <span class="text-muted">EGP/hr</span>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label class="form-label mb-0 fw-semibold">New Status:</label>
          <select name="new_status" class="form-select form-select-sm" style="width:150px;">
            <option value="">— Keep —</option>
            <?php foreach ($statuses as $key => $label): ?>
              <option value="<?= $key ?>"><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="button" class="btn btn-primary btn-sm" onclick="submitBulk('bulk_update')"><i class="bi bi-check-all me-1"></i> Apply to Selected</button>
        <button type="button" class="btn btn-outline-danger btn-sm" onclick="submitBulk('delete_spots')"><i class="bi bi-trash3 me-1"></i> Delete Selected</button>
        <small class="text-muted ms-auto">Select spots and choose an action</small>
      </div>
    </form>
    <?php endif; ?>
  </div>

</div></div></div>

<script>
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.spot-check').forEach(cb => cb.checked = this.checked);
});

function submitBulk(actionType) {
    const checked = document.querySelectorAll('.spot-check:checked');
    if (checked.length === 0) {
        alert('Please select at least one spot.');
        return;
    }
    if (actionType === 'delete_spots' && !confirm('Are you sure you want to delete the selected spots?')) {
        return;
    }
    document.getElementById('bulkAction').value = actionType;
    document.getElementById('bulkForm').submit();
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>