<?php
// admin/peak_rules.php
require_once __DIR__ . '/../config/session.php';
requireRole('admin');
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Peak Hour Rules — CitySlot';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $stmt = $db->prepare("INSERT INTO peak_hour_rules (day_of_week, start_time, end_time, multiplier, event_name) VALUES (?,?,?,?,?)");
        $day = $_POST['day_of_week'] !== '' ? (int)$_POST['day_of_week'] : null;
        $stmt->execute([$day, $_POST['start_time'], $_POST['end_time'], (float)$_POST['multiplier'], trim($_POST['event_name'])]);
        setFlash('success', 'Peak rule added!');
    } elseif ($action === 'delete') {
        $stmt = $db->prepare("DELETE FROM peak_hour_rules WHERE rule_id = ?");
        $stmt->execute([(int)$_POST['rule_id']]);
        setFlash('success', 'Rule deleted.');
    }
    header('Location: /parking_system/index.php?action=peak_rules'); exit;
}

$rules = $db->query("SELECT * FROM peak_hour_rules ORDER BY ISNULL(day_of_week), day_of_week, start_time")->fetchAll();
$days  = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <h4 class="fw-bold mb-4">⏰ Peak Hour Pricing Rules</h4>
  <div class="row g-4">
    <div class="col-md-4">
      <div class="card">
        <div class="card-header fw-bold">+ Add Rule</div>
        <div class="card-body">
          <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="mb-3">
              <label class="form-label">Rule Name</label>
              <input type="text" name="event_name" class="form-control" placeholder="Morning Rush Hour" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Day of Week</label>
              <select name="day_of_week" class="form-select">
                <option value="">All Days</option>
                <?php foreach ($days as $i => $d): ?>
                <option value="<?= $i ?>"><?= $d ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="row g-2 mb-3">
              <div class="col-6"><label class="form-label">Start</label><input type="time" name="start_time" class="form-control" required></div>
              <div class="col-6"><label class="form-label">End</label><input type="time" name="end_time" class="form-control" required></div>
            </div>
            <div class="mb-3">
              <label class="form-label">Price Multiplier</label>
              <div class="input-group">
                <input type="number" name="multiplier" class="form-control" step="0.05" min="1" max="5" value="1.50" required>
                <span class="input-group-text">x</span>
              </div>
              <small class="text-muted">1.50 = 50% price increase</small>
            </div>
            <button type="submit" class="btn btn-primary w-100">Add Rule</button>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">Current Rules</div>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Rule</th><th>Day</th><th>Time Window</th><th>Multiplier</th><th>Effect</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($rules as $r): ?>
              <tr>
                <td><strong><?= htmlspecialchars($r['event_name'] ?: 'Unnamed') ?></strong></td>
                <td><?= $r['day_of_week'] !== null ? $days[$r['day_of_week']] : '<span class="text-muted">All Days</span>' ?></td>
                <td><?= date('h:i A', strtotime($r['start_time'])) ?> – <?= date('h:i A', strtotime($r['end_time'])) ?></td>
                <td><span class="badge bg-warning text-dark fs-6"><?= $r['multiplier'] ?>x</span></td>
                <td class="text-danger">+<?= round(($r['multiplier']-1)*100) ?>% price</td>
                <td>
                  <form method="POST" class="d-inline" onsubmit="return confirm('Delete this rule?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="rule_id" value="<?= $r['rule_id'] ?>">
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
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
