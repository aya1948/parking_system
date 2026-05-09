<?php
// driver/recurring_booking.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/Reservation.php';
require_once __DIR__ . '/../classes/Vehicle.php';
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Weekly Recurring Booking — Rakna';
$user      = currentUser();
$b         = BASE_URL;
$spotId    = (int)($_GET['spot_id'] ?? 0);
$db        = getDB();

// Get spot info
$stmt = $db->prepare("SELECT * FROM parking_spots WHERE spot_id=? AND status='available' AND is_verified=1");
$stmt->execute([$spotId]);
$spot = $stmt->fetch();

if (!$spot) {
    setFlash('error', 'Spot not found or unavailable.');
    header("Location:$b/index.php?action=search_spots"); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resObj   = new Reservation();
    $daysOfWeek = array_map('intval', $_POST['days'] ?? []);
    if (empty($daysOfWeek)) {
        setFlash('error', 'Please select at least one day.');
        header("Location:$b/index.php?action=recurring_booking&spot_id=$spotId"); exit;
    }

    $vehObj   = new Vehicle();
    $vehicle  = $vehObj->getDefaultVehicle($user['user_id']);
    if (!$vehicle) {
        setFlash('error', 'Please add a vehicle first.');
        header("Location:$b/index.php?action=add_vehicle"); exit;
    }

    $baseStart = $_POST['start_date'] . ' ' . $_POST['start_time'] . ':00';
    $baseEnd   = $_POST['start_date'] . ' ' . $_POST['end_time']   . ':00';

    $result = $resObj->createRecurringReservation([
        'driver_id'  => $user['user_id'],
        'spot_id'    => $spotId,
        'vehicle_id' => $vehicle['vehicle_id'],
        'start_time' => $baseStart,
        'end_time'   => $baseEnd,
    ], $daysOfWeek, (int)($_POST['weeks'] ?? 4));

    if ($result['success']) {
        setFlash('success', "Recurring booking created! {$result['created']} reservations made with 10% bulk discount.");
        header("Location:$b/index.php?action=my_reservations"); exit;
    } else {
        setFlash('error', $result['message']);
    }
}

$vehObj   = new Vehicle();
$vehicles = $vehObj->listUserVehicles($user['user_id']);
$days     = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

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
.btn-outline-primary {
    color: #480959;
    border-color: #480959;
}
.btn-outline-primary:hover,
.btn-check:checked + .btn-outline-primary {
    background-color: #480959;
    border-color: #480959;
    color: #fff;
}
.card-header {
    background-color: #480959;
    color: #fff;
    font-weight: bold;
}
.text-success {
    color: #480959 !important;
}
</style>

<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <h4 class="fw-bold mb-4"><i class="bi bi-arrow-repeat me-2"></i>Weekly Recurring Booking</h4>
  <div class="row justify-content-center">
    <div class="col-md-7">
      <div class="card">
        <div class="card-header"><i class="bi bi-calendar-week me-1"></i> Book <?= htmlspecialchars($spot['title']) ?> Weekly</div>
        <div class="card-body">
          <div class="alert alert-success small mb-4">
            <i class="bi bi-tag-fill me-1"></i>
            <strong>10% Bulk Discount</strong> applied automatically for recurring bookings!
          </div>

          <form method="POST">
            <!-- DAYS SELECTION -->
            <div class="mb-4">
              <label class="form-label fw-semibold">Select Days *</label>
              <div class="d-flex flex-wrap gap-2">
                <?php foreach($days as $i => $day): ?>
                <div>
                  <input type="checkbox" class="btn-check" name="days[]"
                         value="<?= $i ?>" id="day<?= $i ?>"
                         <?= in_array($i,[1,2,3,4,5]) ? 'checked' : '' ?>>
                  <label class="btn btn-outline-primary btn-sm" for="day<?= $i ?>">
                    <?= substr($day,0,3) ?>
                  </label>
                </div>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- TIME -->
            <div class="row g-3 mb-3">
              <div class="col-4">
                <label class="form-label fw-semibold">Start Date *</label>
                <input type="date" name="start_date" class="form-control"
                       min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d', strtotime('next Monday')) ?>" required>
              </div>
              <div class="col-4">
                <label class="form-label fw-semibold">Start Time *</label>
                <input type="time" name="start_time" class="form-control" value="09:00" required>
              </div>
              <div class="col-4">
                <label class="form-label fw-semibold">End Time *</label>
                <input type="time" name="end_time" class="form-control" value="17:00" required>
              </div>
            </div>

            <!-- WEEKS -->
            <div class="mb-3">
              <label class="form-label fw-semibold">Number of Weeks</label>
              <select name="weeks" class="form-select">
                <option value="2">2 Weeks</option>
                <option value="4" selected>4 Weeks (1 Month)</option>
                <option value="8">8 Weeks (2 Months)</option>
                <option value="12">12 Weeks (3 Months)</option>
              </select>
            </div>

            <!-- VEHICLE -->
            <div class="mb-4">
              <label class="form-label fw-semibold">Vehicle</label>
              <select name="vehicle_id" class="form-select">
                <?php foreach($vehicles as $v): ?>
                <option value="<?= $v['vehicle_id'] ?>" <?= $v['is_default']?'selected':'' ?>>
                  <?= htmlspecialchars($v['license_plate'].' — '.$v['make'].' '.$v['model']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- PRICE PREVIEW -->
            <div class="card bg-light mb-4 p-3">
              <div class="d-flex justify-content-between small">
                <span>Price/hr</span>
                <strong><?= number_format($spot['price_per_hour'],2) ?> EGP</strong>
              </div>
              <div class="d-flex justify-content-between small text-success">
                <span>Recurring Discount</span>
                <strong>-10%</strong>
              </div>
              <div class="d-flex justify-content-between small text-muted">
                <span>VAT (14%)</span>
                <strong>included</strong>
              </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-bold btn-lg">
              <i class="bi bi-calendar-week me-1"></i>Create Recurring Booking
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>