<?php
// admin/dashboard.php
require_once __DIR__ . '/../config/session.php';
requireRole('admin');
require_once __DIR__ . '/../classes/Report.php';

$pageTitle = 'Admin Dashboard — CitySlot';
$user      = currentUser();
$reportObj = new Report();
$stats     = $reportObj->getSystemStats();

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <h4 class="fw-bold mb-4">⚙️ System Administration</h4>

  <!-- SYSTEM STATS -->
  <div class="row g-3 mb-4">
    <?php
    $statCards = [
      ['Active Users',    'total_users',        'primary',   'people'],
      ['Verified Spots',  'total_spots',         'success',   'geo-alt'],
      ['Total Bookings',  'total_reservations',  'info',      'calendar-check'],
      ['Active Now',      'active_now',          'success',   'car-front'],
      ['Total Revenue',   'total_revenue',       'warning',   'cash-coin'],
      ['Pending Verif.',  'pending_verif',       'primary',   'patch-check'],
      ['Open Appeals',    'open_appeals',        'danger',    'shield-check'],
      ['Blacklisted',     'blacklisted_users',   'dark',      'person-x'],
    ];
    foreach ($statCards as [$label, $key, $color, $icon]):
    $val = $key === 'total_revenue' ? number_format((float)($stats[$key]??0), 0) . ' EGP' : ($stats[$key] ?? 0);
    ?>
    <div class="col-md-3">
      <div class="card stat-card p-3 border-<?= $color ?>">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <p class="text-muted small mb-1"><?= $label ?></p>
            <h3 class="fw-bold mb-0 text-<?= $color ?>"><?= $val ?></h3>
          </div>
          <i class="bi bi-<?= $icon ?> fs-2 text-<?= $color ?> opacity-25"></i>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- QUICK ACTIONS -->
  <div class="card mb-4 p-3">
    <h6 class="fw-bold mb-3">Quick Actions</h6>
    <div class="d-flex flex-wrap gap-2">
      <a href="/parking_system/index.php?action=verifications" class="btn btn-primary btn-sm">
        <i class="bi bi-patch-check me-1"></i> Review Verifications
        <?php if ($stats['pending_verif'] > 0): ?><span class="badge bg-danger"><?= $stats['pending_verif'] ?></span><?php endif; ?>
      </a>
      <a href="/parking_system/index.php?action=appeals" class="btn btn-warning btn-sm">
        <i class="bi bi-shield-check me-1"></i> Review Appeals
        <?php if ($stats['open_appeals'] > 0): ?><span class="badge bg-danger"><?= $stats['open_appeals'] ?></span><?php endif; ?>
      </a>
      <a href="/parking_system/index.php?action=event_zones" class="btn btn-danger btn-sm"><i class="bi bi-bounding-box me-1"></i> Manage Event Zones</a>
      <a href="/parking_system/index.php?action=heatmap" class="btn btn-outline-info btn-sm"><i class="bi bi-map me-1"></i> Revenue Heatmap</a>
      <a href="/parking_system/index.php?action=sensor_health" class="btn btn-outline-secondary btn-sm"><i class="bi bi-activity me-1"></i> Sensor Health</a>
      <a href="/parking_system/index.php?action=audit_log" class="btn btn-outline-dark btn-sm"><i class="bi bi-journal-text me-1"></i> Audit Log</a>
    </div>
  </div>

  <!-- NAVIGATION CARDS -->
  <div class="row g-3">
    <?php
    $modules = [
      ['manage_users',    'people',          'primary',   'User Management',       'Register, search, blacklist, deactivate users'],
      ['manage_spots',    'geo-alt',         'success',   'Spot Management',       'View & manage all parking spots'],
      ['manage_fines',    'file-earmark-x',  'danger',    'Fine Management',       'Issue and track digital fines'],
      ['event_zones',     'bounding-box',    'warning',   'Event Zones',           'Lock city zones for events'],
      ['peak_rules',      'clock-history',   'info',      'Peak Hour Rules',       'Configure pricing multipliers'],
      ['promo_codes',     'tag',             'secondary', 'Promo Codes',           'Manage discount codes'],
      ['sensor_health',   'activity',        'dark',      'Sensor Health',         'Monitor virtual IoT sensors'],
      ['heatmap',         'map',             'primary',   'Revenue Heatmap',       'Zone-level revenue analytics'],
    ];
    foreach ($modules as [$action, $icon, $color, $title, $desc]):
    ?>
    <div class="col-md-3">
      <a href="/parking_system/index.php?action=<?= $action ?>" class="text-decoration-none">
        <div class="card h-100 spot-card p-3 text-center">
          <i class="bi bi-<?= $icon ?> text-<?= $color ?>" style="font-size:2.5rem;"></i>
          <h6 class="fw-bold mt-2 mb-1"><?= $title ?></h6>
          <small class="text-muted"><?= $desc ?></small>
        </div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
