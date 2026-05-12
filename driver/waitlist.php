<?php
// driver/waitlist.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/Notification.php';
require_once __DIR__ . '/../classes/Vehicle.php';
require_once __DIR__ . '/../config/db.php';

$pageTitle  = 'Spot Waitlist — Rakna';
$user       = currentUser();
$b          = BASE_URL;
$notifObj   = new Notification();
$vehObj     = new Vehicle();
$db         = getDB();

$garageId   = (int)($_GET['garage_id'] ?? 0);
$spotId     = (int)($_GET['spot_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $spotIdPost   = ($_POST['spot_id'] === '') ? 0 : (int)$_POST['spot_id'];
    $garageIdPost = (int)($_POST['garage_id'] ?? 0);
    $driverId     = $user['user_id'];
    $vehicleId    = (int)$_POST['vehicle_id'];
    $start        = $_POST['desired_start'];
    $end          = $_POST['desired_end'];

    $result = $notifObj->addToWaitlist($spotIdPost, $driverId, $vehicleId, $start, $end, $garageIdPost);
    setFlash($result['success'] ? 'success' : 'error',
        $result['success'] ? "You're watching this spot! We'll notify you when it's free." : $result['message']);
    header("Location:$b/index.php?action=search_spots");
    exit;
}

$garage = null;
if ($garageId > 0) {
    $stmt = $db->prepare("SELECT * FROM garages WHERE garage_id = ?");
    $stmt->execute([$garageId]);
    $garage = $stmt->fetch();
}

$vehicles = $vehObj->listUserVehicles($user['user_id']);

$stmt = $db->prepare("
    SELECT w.*, 
           COALESCE(s.title, g.name) AS title, 
           COALESCE(s.address, g.address) AS address,
           g.name AS garage_name
    FROM waitlist w
    LEFT JOIN parking_spots s ON w.spot_id = s.spot_id
    LEFT JOIN garages g ON w.garage_id = g.garage_id
    WHERE w.driver_id = ? AND w.status = 'watching'
    ORDER BY w.added_at DESC
");
$stmt->execute([$user['user_id']]);
$watchlist = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<style>
.btn-primary { background-color: #480959; border-color: #480959; }
.btn-primary:hover { background-color: #8A2888; border-color: #8A2888; }
.card-header { background-color: #480959; color: #fff; font-weight: bold; }
.badge.bg-warning { background-color: #480959 !important; color: #fff; }
</style>

<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <h4 class="fw-bold mb-4"><i class="bi bi-bell me-2"></i>Spot Watchlist</h4>
  <div class="row g-4">

    <?php if ($garage): ?>
    <div class="col-md-5">
      <div class="card">
        <div class="card-header"><i class="bi bi-building me-1"></i> Watch Entire Garage</div>
        <div class="card-body">
          <h6><?= htmlspecialchars($garage['name']) ?></h6>
          <p class="small text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($garage['address']) ?></p>
          <p class="text-muted small">You will be notified when <strong>any spot</strong> becomes available in this garage.</p>
          <form method="POST">
            <input type="hidden" name="spot_id" value=""> <!-- تغيير هنا: نرسل فارغاً -->
            <input type="hidden" name="garage_id" value="<?= $garageId ?>">
            <div class="mb-3">
              <label class="form-label">Vehicle</label>
              <select name="vehicle_id" class="form-select" required>
                <?php foreach($vehicles as $v): ?>
                <option value="<?= $v['vehicle_id'] ?>" <?= $v['is_default']?'selected':'' ?>>
                  <?= htmlspecialchars($v['license_plate'].' — '.$v['make'].' '.$v['model']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Desired Start</label>
              <input type="datetime-local" name="desired_start" class="form-control" required min="<?= date('Y-m-d\TH:i') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Desired End</label>
              <input type="datetime-local" name="desired_end" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-bold">
              <i class="bi bi-bell me-1"></i>Watch This Garage
            </button>
          </form>
        </div>
      </div>
    </div>
    <?php elseif ($spotId > 0): ?>
    <?php
    $stmt = $db->prepare("SELECT * FROM parking_spots WHERE spot_id=?");
    $stmt->execute([$spotId]);
    $spot = $stmt->fetch();
    if ($spot):
    ?>
    <div class="col-md-5">
      <div class="card">
        <div class="card-header"><i class="bi bi-eye me-1"></i> Watch This Spot</div>
        <div class="card-body">
          <h6><?= htmlspecialchars($spot['title']) ?></h6>
          <p class="small text-muted"><?= htmlspecialchars($spot['address']) ?></p>
          <form method="POST">
            <input type="hidden" name="spot_id" value="<?= $spotId ?>">
            <div class="mb-3">
              <label class="form-label">Vehicle</label>
              <select name="vehicle_id" class="form-select" required>
                <?php foreach($vehicles as $v): ?>
                <option value="<?= $v['vehicle_id'] ?>" <?= $v['is_default']?'selected':'' ?>>
                  <?= htmlspecialchars($v['license_plate'].' — '.$v['make'].' '.$v['model']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Desired Start</label>
              <input type="datetime-local" name="desired_start" class="form-control" required min="<?= date('Y-m-d\TH:i') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Desired End</label>
              <input type="datetime-local" name="desired_end" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-bold">
              <i class="bi bi-bell me-1"></i>Watch This Spot
            </button>
          </form>
        </div>
      </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <div class="<?= ($garage || ($spotId>0 && isset($spot))) ? 'col-md-7' : 'col-md-12' ?>">
      <div class="card">
        <div class="card-header"><i class="bi bi-list-ul me-1"></i> My Watchlist (<?= count($watchlist) ?>)</div>
        <?php if(empty($watchlist)): ?>
        <div class="card-body text-muted">Not watching any spots yet.</div>
        <?php else: ?>
        <div class="table-responsive">
          <table class="table mb-0">
            <thead class="table-light"><tr><th>Spot / Garage</th><th>Desired Time</th><th>Status</th></tr></thead>
            <tbody>
              <?php foreach($watchlist as $w): ?>
              <tr>
                <td>
                  <?php if (empty($w['spot_id']) && !empty($w['garage_name'])): ?>
                    <strong><i class="bi bi-building me-1"></i> <?= htmlspecialchars($w['garage_name']) ?></strong>
                  <?php else: ?>
                    <strong><?= htmlspecialchars($w['title']) ?></strong><br>
                    <small class="text-muted"><?= htmlspecialchars($w['address']) ?></small>
                  <?php endif; ?>
                </td>
                <td><small><?= date('M d h:i A',strtotime($w['desired_start'])) ?> → <?= date('h:i A',strtotime($w['desired_end'])) ?></small></td>
                <td><span class="badge bg-warning">Watching</span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>