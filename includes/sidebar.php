<?php
// includes/sidebar.php
require_once __DIR__ . '/../config/session.php';
$user = currentUser();
if (!$user) return;
$role = $user['role'];
?>
<style>
/* تنسيق السايد بار مباشرة */
.sidebar {
    background-color: #480959;  /* خلفية داكنة واضحة */
    color: #ffffff;
    min-height: 100vh;
    padding-top: 1rem;
    padding-bottom: 1rem;
}
.sidebar .nav-link {
    color: #b0c4de;
    font-size: 0.95rem;
    border-radius: 0.4rem;
    margin: 0.15rem 0.5rem;
    transition: 0.2s;
}
.sidebar .nav-link i {
    margin-right: 0.5rem;
}
.sidebar .nav-link:hover {
    background-color: #8A2888;
    color: #ffffff;
    border-left: 3px solid #a1abb9;
}
/* تم حذف تنسيق الدائرة لأنها أزيلت */
.sidebar hr {
    background-color: rgba(255,255,255,0.2);
}
.badge-role-admin { background-color: #480959; }
.badge-role-driver { background-color: #480959; }
.badge-role-owner { background-color: #480959; }
</style>

<div class="col-md-2 d-none d-md-block sidebar py-3">
  <div class="text-center mb-3">
    <!-- تمت إزالة الدائرة التي كانت تعرض الحرف الأول من الاسم -->
    <p class="mb-0 mt-2 small fw-bold"><?= htmlspecialchars($user['full_name']) ?></p>
    <span class="badge badge-role-<?= $role ?> small"><?= ucfirst($role) ?></span>
  </div>
  <hr>

  <?php if ($role === 'driver'): ?>
  <ul class="nav flex-column">
    <li><a class="nav-link" href="/parking_system/index.php?action=driver_dashboard"><i class="bi bi-house-door"></i> Dashboard</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=search_spots"><i class="bi bi-building me-1"></i> Find Garage</a></li>
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
    <li><a class="nav-link" href="/parking_system/index.php?action=add_garage"><i class="bi bi-building"></i> Add Garage</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=manage_spots"><i class="bi bi-sliders"></i> Manage Spots</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=owner_reservations"><i class="bi bi-calendar3"></i> Reservations</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=earnings"><i class="bi bi-wallet2"></i> Earnings</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=owner_report"><i class="bi bi-file-earmark-bar-graph"></i> Reports</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=profile"><i class="bi bi-person"></i> Profile</a></li>
  </ul>

  <?php elseif ($role === 'admin'): ?>
  <ul class="nav flex-column">
    <li><a class="nav-link" href="/parking_system/index.php?action=admin_dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=manage_users"><i class="bi bi-people"></i> Users</a></li>
    <!-- تم تعديل المسار هنا إلى admin_manage_spots ليتوافق مع ملف index.php -->
    <li><a class="nav-link" href="/parking_system/index.php?action=admin_manage_spots"><i class="bi bi-geo"></i> All Spots</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=verifications"><i class="bi bi-patch-check"></i> Verifications</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=manage_fines"><i class="bi bi-file-earmark-x"></i> Fines</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=appeals"><i class="bi bi-shield-check"></i> Appeals</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=event_zones"><i class="bi bi-bounding-box"></i> Event Zones</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=heatmap"><i class="bi bi-map"></i> Revenue Heatmap</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=audit_log"><i class="bi bi-journal-text"></i> Audit Log</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=promo_codes"><i class="bi bi-tag"></i> Promo Codes</a></li>
    <li><a class="nav-link" href="/parking_system/index.php?action=peak_rules"><i class="bi bi-clock"></i> Peak Hour Rules</a></li>
  </ul>
  <?php endif; ?>
</div>