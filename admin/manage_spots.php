<?php
// admin/manage_spots.php
require_once __DIR__ . '/../config/session.php';
requireRole('admin');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/ParkingSpot.php';
require_once __DIR__ . '/../classes/Fine.php';

$pageTitle = 'Manage Spots — Rakna';
$user      = currentUser();
$db        = getDB();
$b         = BASE_URL;

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $spotId = (int)($_POST['spot_id'] ?? 0);

    if ($action === 'toggle_verify') {
        $stmt = $db->prepare("UPDATE parking_spots SET is_verified = NOT is_verified WHERE spot_id = ?");
        $stmt->execute([$spotId]);
        setFlash('success', 'Spot verification status updated.');

    } elseif ($action === 'set_status') {
        $status = $_POST['status'] ?? 'unavailable';
        // Check: cannot change status if spot has active/confirmed reservations
        $checkStmt = $db->prepare("
            SELECT COUNT(*) FROM reservations
            WHERE spot_id = ? AND status IN ('confirmed','active','extended','pending')
        ");
        $checkStmt->execute([$spotId]);
        $activeCount = (int)$checkStmt->fetchColumn();

        if ($activeCount > 0 && in_array($status, ['unavailable','maintenance','owner_use'])) {
            setFlash('error', "Cannot change status: this spot has {$activeCount} active/upcoming reservation(s). Use Emergency Override to force cancel.");
        } else {
            $stmt = $db->prepare("UPDATE parking_spots SET status = ? WHERE spot_id = ?");
            $stmt->execute([$status, $spotId]);
            setFlash('success', 'Spot status updated.');
        }

    } elseif ($action === 'emergency') {
        $fineObj = new Fine();
        $result  = $fineObj->emergencyOverride($spotId, $user['user_id'], $_POST['reason'] ?? 'Admin action');
        setFlash($result['success'] ? 'success' : 'error', $result['message']);

    } elseif ($action === 'delete') {
        $stmt = $db->prepare("DELETE FROM parking_spots WHERE spot_id = ?");
        $stmt->execute([$spotId]);
        setFlash('success', 'Spot deleted.');
    }

    header("Location: $b/index.php?action=manage_spots");
    exit;
}

// Search & Filter
$search  = trim($_GET['q']      ?? '');
$status  = $_GET['status']      ?? '';
$zone    = trim($_GET['zone']   ?? '');
$verified = $_GET['verified']   ?? '';

$sql    = "
    SELECT s.*, u.full_name AS owner_name, u.email AS owner_email,
           COUNT(r.reservation_id) AS total_bookings
    FROM parking_spots s
    JOIN users u ON s.owner_id = u.user_id
    LEFT JOIN reservations r ON s.spot_id = r.spot_id AND r.status = 'completed'
    WHERE 1=1
";
$params = [];

if ($search) {
    $sql   .= " AND (s.title LIKE ? OR s.address LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($status)   { $sql .= " AND s.status = ?";      $params[] = $status; }
if ($zone)     { $sql .= " AND s.city_zone LIKE ?"; $params[] = "%$zone%"; }
if ($verified !== '') { $sql .= " AND s.is_verified = ?"; $params[] = (int)$verified; }

$sql .= " GROUP BY s.spot_id ORDER BY s.created_at DESC LIMIT 200";

$stmt  = $db->prepare($sql);
$stmt->execute($params);
$spots = $stmt->fetchAll();

// Stats
$statsRow = $db->query("
    SELECT 
        COUNT(*) AS total,
        SUM(is_verified) AS verified,
        SUM(status = 'available') AS available,
        SUM(status = 'pending_verification') AS pending
    FROM parking_spots
")->fetch();

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">

  <h4 class="fw-bold mb-4">🅿️ Spot Management</h4>

  <!-- STATS -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card stat-card p-3">
        <p class="text-muted small mb-1">Total Spots</p>
        <h3 class="fw-bold mb-0"><?= $statsRow['total'] ?></h3>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card stat-card border-success p-3">
        <p class="text-muted small mb-1">Verified</p>
        <h3 class="fw-bold mb-0 text-success"><?= $statsRow['verified'] ?></h3>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card stat-card border-primary p-3">
        <p class="text-muted small mb-1">Available Now</p>
        <h3 class="fw-bold mb-0 text-primary"><?= $statsRow['available'] ?></h3>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card stat-card border-warning p-3">
        <p class="text-muted small mb-1">Pending Verification</p>
        <h3 class="fw-bold mb-0 text-warning"><?= $statsRow['pending'] ?></h3>
      </div>
    </div>
  </div>

  <!-- SEARCH & FILTER -->
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" class="row g-3">
        <input type="hidden" name="action" value="manage_spots">
        <div class="col-md-3">
          <input type="text" name="q" class="form-control" placeholder="Search title or address..."
                 value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-2">
          <select name="status" class="form-select">
            <option value="">All Statuses</option>
            <?php foreach (['available','unavailable','maintenance','owner_use','pending_verification'] as $s): ?>
            <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <select name="verified" class="form-select">
            <option value="">All</option>
            <option value="1" <?= $verified==='1'?'selected':'' ?>>Verified Only</option>
            <option value="0" <?= $verified==='0'?'selected':'' ?>>Unverified Only</option>
          </select>
        </div>
        <div class="col-md-2">
          <input type="text" name="zone" class="form-control" placeholder="Zone..."
                 value="<?= htmlspecialchars($zone) ?>">
        </div>
        <div class="col-md-1">
          <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
        <div class="col-md-1">
          <a href="<?= $b ?>/index.php?action=manage_spots" class="btn btn-outline-secondary w-100">Clear</a>
        </div>
      </form>
    </div>
  </div>

  <!-- SPOTS TABLE -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span>Showing <strong><?= count($spots) ?></strong> spots</span>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Spot</th>
            <th>Owner</th>
            <th>Price/hr</th>
            <th>Bookings</th>
            <th>Trust</th>
            <th>Status</th>
            <th>Verified</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($spots as $s): ?>
          <tr>
            <td><small class="text-muted"><?= $s['spot_id'] ?></small></td>
            <td>
              <strong><?= htmlspecialchars($s['title']) ?></strong>
              <br><small class="text-muted"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($s['address']) ?></small>
              <?php if ($s['city_zone']): ?>
              <br><span class="badge bg-light text-dark small"><?= htmlspecialchars($s['city_zone']) ?></span>
              <?php endif; ?>
            </td>
            <td>
              <small><?= htmlspecialchars($s['owner_name']) ?></small>
              <br><small class="text-muted"><?= htmlspecialchars($s['owner_email']) ?></small>
            </td>
            <td class="text-primary fw-bold"><?= number_format($s['price_per_hour'],2) ?> EGP</td>
            <td class="text-center"><?= $s['total_bookings'] ?></td>
            <td>
              <span class="text-warning"><?= number_format($s['trust_score'],1) ?>★</span>
              <br><small class="text-muted"><?= $s['total_reviews'] ?> reviews</small>
            </td>
            <td>
              <?php
              $bc = [
                'available'            => 'success',
                'unavailable'          => 'secondary',
                'maintenance'          => 'warning',
                'owner_use'            => 'info',
                'pending_verification' => 'primary',
              ][$s['status']] ?? 'light';
              ?>
              <span class="badge bg-<?= $bc ?>"><?= ucfirst(str_replace('_',' ',$s['status'])) ?></span>
            </td>
            <td class="text-center">
              <?php if ($s['is_verified']): ?>
                <span class="badge bg-success">✅ Verified</span>
              <?php else: ?>
                <span class="badge bg-danger">❌ No</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="d-flex gap-1 flex-wrap">
                <!-- Toggle Verify -->
                <form method="POST" class="d-inline">
                  <input type="hidden" name="action"  value="toggle_verify">
                  <input type="hidden" name="spot_id" value="<?= $s['spot_id'] ?>">
                  <button class="btn btn-sm btn-<?= $s['is_verified'] ? 'outline-danger' : 'outline-success' ?>"
                          title="<?= $s['is_verified'] ? 'Revoke Verification' : 'Approve Spot' ?>">
                    <?= $s['is_verified'] ? '🔒' : '✅' ?>
                  </button>
                </form>

                <!-- Change Status -->
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                        data-bs-target="#statusModal"
                        onclick="document.getElementById('statusSpotId').value='<?= $s['spot_id'] ?>'">
                  🔄
                </button>

                <!-- Emergency Override -->
                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                        data-bs-target="#emergencyModal"
                        onclick="document.getElementById('emergSpotId').value='<?= $s['spot_id'] ?>'">
                  🚨
                </button>

                <!-- Delete -->
                <form method="POST" class="d-inline"
                      onsubmit="return confirm('Delete spot: <?= addslashes($s['title']) ?>? This cannot be undone.')">
                  <input type="hidden" name="action"  value="delete">
                  <input type="hidden" name="spot_id" value="<?= $s['spot_id'] ?>">
                  <button class="btn btn-sm btn-danger" title="Delete">🗑️</button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($spots)): ?>
          <tr><td colspan="9" class="text-center text-muted py-5">No spots found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div></div></div>

<!-- STATUS MODAL -->
<div class="modal fade" id="statusModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Change Spot Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input type="hidden" name="action"   value="set_status">
        <input type="hidden" name="spot_id"  id="statusSpotId">
        <label class="form-label">New Status</label>
        <select name="status" class="form-select">
          <option value="available">✅ Available</option>
          <option value="unavailable">🔒 Unavailable</option>
          <option value="maintenance">🔧 Maintenance</option>
          <option value="owner_use">🏠 Owner Use</option>
        </select>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Update</button>
      </div>
    </form>
  </div>
</div>

<!-- EMERGENCY MODAL -->
<div class="modal fade" id="emergencyModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">🚨 Emergency Override</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="action"   value="emergency">
        <input type="hidden" name="spot_id"  id="emergSpotId">
        <div class="alert alert-danger small">This will cancel ALL active reservations and issue full refunds.</div>
        <label class="form-label fw-semibold">Reason *</label>
        <textarea name="reason" class="form-control" rows="3" required
                  placeholder="Emergency vehicle access, fire evacuation..."></textarea>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger">Execute Override</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
