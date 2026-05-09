<?php
// owner/reservations.php
require_once __DIR__ . '/../config/session.php';
requireRole('owner');
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Spot Reservations — CitySlot';
$user      = currentUser();
$db        = getDB();
$status    = $_GET['status'] ?? '';

$sql = "
    SELECT r.*, u.full_name AS driver_name, u.phone AS driver_phone,
           s.title AS spot_title, s.spot_number, g.name AS garage_name,
           v.license_plate, v.make, v.model
    FROM reservations r
    JOIN users u ON r.driver_id = u.user_id
    JOIN parking_spots s ON r.spot_id = s.spot_id
    LEFT JOIN vehicles v ON r.vehicle_id = v.vehicle_id
    LEFT JOIN garages g ON s.garage_id = g.garage_id
    WHERE s.owner_id = ?
";
$params = [$user['user_id']];
if ($status) { $sql .= " AND r.status = ?"; $params[] = $status; }
$sql .= " ORDER BY r.start_time DESC LIMIT 100";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$reservations = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <h4 class="fw-bold mb-4">📅 Reservations on My Spots</h4>

  <!-- STATUS TABS -->
  <ul class="nav nav-tabs mb-3">
    <?php foreach ([''=>'All','confirmed'=>'Upcoming','active'=>'Active Now','completed'=>'Completed','cancelled'=>'Cancelled','no_show'=>'No-Show'] as $s=>$l): ?>
    <li class="nav-item">
      <a class="nav-link <?= $status===$s?'active':'' ?>" href="/parking_system/index.php?action=owner_reservations&status=<?= $s ?>">
        <?= $l ?>
      </a>
    </li>
    <?php endforeach; ?>
  </ul>

  <?php if (empty($reservations)): ?>
    <div class="alert alert-info">No reservations found for this filter.</div>
  <?php else: ?>
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Spot</th><th>Driver</th><th>Vehicle</th><th>Start</th><th>End</th><th>Amount</th><th>Status</th></tr>
        </thead>
        <tbody>
          <?php foreach ($reservations as $r): ?>
          <tr>
            <td>
              <?php if (!empty($r['garage_name'])): ?>
              <small class="text-muted d-block"><i class="bi bi-building me-1"></i><?= htmlspecialchars($r['garage_name']) ?></small>
              <?php endif; ?>
              <strong>
                <?php if (!empty($r['spot_number'])): ?>
                <span class="badge bg-dark font-monospace me-1"><?= htmlspecialchars($r['spot_number']) ?></span>
                <?php endif; ?>
                <?= htmlspecialchars($r['spot_title']) ?>
              </strong>
            </td>
            <td>
              <?= htmlspecialchars($r['driver_name']) ?>
              <br><small class="text-muted"><?= htmlspecialchars($r['driver_phone'] ?? '') ?></small>
            </td>
            <td>
              <span class="font-monospace"><?= htmlspecialchars($r['license_plate'] ?? '—') ?></span>
              <br><small class="text-muted"><?= htmlspecialchars(($r['make']??'').' '.($r['model']??'')) ?></small>
            </td>
            <td><small><?= date('M d, h:i A', strtotime($r['start_time'])) ?></small></td>
            <td><small><?= date('M d, h:i A', strtotime($r['end_time'])) ?></small></td>
            <td class="fw-bold text-success"><?= number_format($r['total_amount'],2) ?> EGP</td>
            <td>
              <?php $bc=['confirmed'=>'warning','active'=>'success','completed'=>'secondary','cancelled'=>'danger','no_show'=>'dark'][$r['status']]??'light'; ?>
              <span class="badge bg-<?= $bc ?>"><?= ucfirst(str_replace('_',' ',$r['status'])) ?></span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
