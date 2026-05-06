<?php
// includes/sidebar.php
require_once __DIR__ . '/../config/session.php';
$user = currentUser();
if (!$user) return;
$role = $user['role'];
?>
<div class="col-md-2 d-none d-md-block sidebar py-3">
  <div class="text-center mb-3">
    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width:50px;height:50px;font-size:1.4rem;">
      <?= strtoupper(substr($user['full_name'],0,1)) ?>
    </div>
    <p class="mb-0 mt-2 small fw-bold"><?= htmlspecialchars($user['full_name']) ?></p>
    <span class="badge badge-role-<?= $role ?> small"><?= ucfirst($role) ?></span>
  </div>
  <hr>

  <?php if ($role === 'driver'): ?>
  <ul class="nav flex-column">
    <li><a class="nav-link" href="/parking_system/index.php?action=driver_dashboard"><i class="bi bi-house-door"></i> Dashboard</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=search_spots"><i class="bi bi-search"></i> Find Parking</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=my_reservations"><i class="bi bi-calendar-check"></i> My Reservations</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=my_vehicles"><i class="bi bi-car-front"></i> My Vehicles</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=my_fines"><i class="bi bi-exclamation-triangle"></i> My Fines</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=favorites"><i class="bi bi-heart"></i> Favorites</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=notifications"><i class="bi bi-bell"></i> Notifications</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=profile"><i class="bi bi-person"></i> Profile</a></li>
  </ul>

  <?php elseif ($role === 'owner'): ?>
  <ul class="nav flex-column">
    <li><a class="nav-link" href="/parking_system/index.php?action=owner_dashboard"><i class="bi bi-house-door"></i> Dashboard</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=my_spots"><i class="bi bi-geo-alt"></i> My Spots</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=add_spot"><i class="bi bi-plus-circle"></i> Add New Spot</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=owner_reservations"><i class="bi bi-calendar3"></i> Reservations</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=earnings"><i class="bi bi-wallet2"></i> Earnings</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=owner_report"><i class="bi bi-file-earmark-bar-graph"></i> Reports</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=messages"><i class="bi bi-chat-dots"></i> Messages</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=profile"><i class="bi bi-person"></i> Profile</a></li>
  </ul>

  <?php elseif ($role === 'admin'): ?>
  <ul class="nav flex-column">
    <li><a class="nav-link" href="/parking_system/index.php?action=admin_dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=manage_users"><i class="bi bi-people"></i> Users</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=manage_spots"><i class="bi bi-geo"></i> All Spots</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=verifications"><i class="bi bi-patch-check"></i> Verifications</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=manage_fines"><i class="bi bi-file-earmark-x"></i> Fines</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=appeals"><i class="bi bi-shield-check"></i> Appeals</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=event_zones"><i class="bi bi-bounding-box"></i> Event Zones</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=heatmap"><i class="bi bi-map"></i> Revenue Heatmap</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=sensor_health"><i class="bi bi-activity"></i> Sensor Health</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=audit_log"><i class="bi bi-journal-text"></i> Audit Log</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=promo_codes"><i class="bi bi-tag"></i> Promo Codes</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=peak_rules"><i class="bi bi-clock"></i> Peak Hour Rules</a></li>
  </ul>
  <?php endif; ?>
</div>
