<?php
// owner/report.php
require_once __DIR__ . '/../config/session.php';
requireRole('owner');
require_once __DIR__ . '/../classes/Report.php';

$pageTitle  = 'Monthly Report — Rakna';
$user       = currentUser();
$reportObj  = new Report();
$month      = (int)($_GET['month'] ?? date('n'));
$year       = (int)($_GET['year']  ?? date('Y'));
$data       = $reportObj->getOwnerMonthlyReport($user['user_id'], $month, $year);
$monthName  = date('F', mktime(0,0,0,$month,1,$year));

require_once __DIR__ . '/../includes/header.php';
?>
<style>
.btn-success { background-color:#480959; border-color:#480959; }
.btn-success:hover { background-color:#8A2888; }
</style>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-bar-chart-fill me-2"></i>Monthly Report — <?= $monthName ?> <?= $year ?></h4>
    <div class="d-flex gap-2">
      <form method="GET" class="d-flex gap-2">
        <input type="hidden" name="action" value="owner_report">
        <select name="month" class="form-select form-select-sm">
          <?php for ($m = 1; $m <= 12; $m++): ?>
          <option value="<?= $m ?>" <?= $m === $month ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
          <?php endfor; ?>
        </select>
        <select name="year" class="form-select form-select-sm">
          <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
          <option value="<?= $y ?>" <?= $y === $year ? 'selected' : '' ?>><?= $y ?></option>
          <?php endfor; ?>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">View</button>
      </form>
      <a href="/parking_system/index.php?action=owner_report&month=<?= $month ?>&year=<?= $year ?>&download=1" class="btn btn-success btn-sm">
        <i class="bi bi-download me-1"></i>Download HTML
      </a>
    </div>
  </div>

  <!-- SUMMARY CARDS -->
  <div class="row g-3 mb-4">
    <?php
    $cards = [
      ['Total Bookings','total_reservations','primary','calendar-check'],
      ['Net Earnings (EGP)','total_earnings','success','wallet2'],
      ['Avg Booking Value','avg_booking_value','info','bar-chart'],
      ['No-Shows','no_shows','warning','person-x'],
      ['Cancellations','cancellations','danger','x-circle'],
    ];
    foreach ($cards as [$label, $key, $color, $icon]):
    $val = is_numeric($data[$key]) ? number_format((float)($data[$key]??0), 2) : ($data[$key]??0);
    ?>
    <div class="col">
      <div class="card stat-card p-3 border-<?= $color ?>">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <p class="text-muted small mb-1"><?= $label ?></p>
            <h4 class="fw-bold mb-0 text-<?= $color ?>"><?= $val ?></h4>
          </div>
          <i class="bi bi-<?= $icon ?> fs-2 text-<?= $color ?> opacity-25"></i>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="row g-4">
    <!-- SPOT PERFORMANCE -->
    <div class="col-md-6">
      <div class="card">
        <div class="card-header"><i class="bi bi-p-circle me-1"></i> Spot Performance</div>
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>Spot</th><th>Bookings</th><th>Trust</th></tr></thead>
            <tbody>
              <?php foreach ($data['spots'] as $s): ?>
              <tr>
                <td><?= htmlspecialchars($s['title']) ?></td>
                <td><?= $s['bookings'] ?></td>
                <td class="text-warning"><?= number_format($s['trust_score'],1) ?>★</td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($data['spots'])): ?><tr><td colspan="3" class="text-muted text-center">No data</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- TOP HOURS -->
    <div class="col-md-6">
      <div class="card">
        <div class="card-header"><i class="bi bi-clock-fill me-1"></i> Busiest Booking Hours</div>
        <div class="card-body">
          <?php if (empty($data['top_hours'])): ?>
            <p class="text-muted small">No data for this period.</p>
          <?php else: ?>
          <?php foreach ($data['top_hours'] as $h): ?>
            <div class="mb-2">
              <div class="d-flex justify-content-between small mb-1">
                <span><?= $h['hour'] ?>:00 — <?= $h['hour']+1 ?>:00</span>
                <strong><?= $h['count'] ?> bookings</strong>
              </div>
              <div class="progress" style="height:8px;">
                <div class="progress-bar bg-primary" style="width:<?= min(100, $h['count'] * 10) ?>%"></div>
              </div>
            </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div></div></div>
<?php
// Handle download
if (isset($_GET['download'])) {
    $file = $reportObj->generateOwnerPDF($user['user_id'], $month, $year);
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="report_'.$monthName.'_'.$year.'.html"');
    readfile($file);
    exit;
}
require_once __DIR__ . '/../includes/footer.php';
?>