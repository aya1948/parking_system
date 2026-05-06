<?php
// admin/heatmap.php
require_once __DIR__ . '/../config/session.php';
requireRole('admin');
require_once __DIR__ . '/../classes/Report.php';

$pageTitle = 'Revenue Heatmap — CitySlot';
$reportObj = new Report();
$heatData  = $reportObj->getRevenueHeatmapData();

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <h4 class="fw-bold mb-4">🗺️ Revenue Heatmap by City Zone</h4>

  <!-- HEATMAP TABLE -->
  <div class="row g-4">
    <div class="col-md-7">
      <div class="card">
        <div class="card-header">Zone Revenue Rankings</div>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Zone</th><th>Bookings</th><th>Total Revenue</th><th>Avg Transaction</th><th>Heat</th></tr></thead>
            <tbody>
              <?php
              $maxRevenue = max(array_column($heatData, 'total_revenue') ?: [1]);
              foreach ($heatData as $zone): 
                $pct = $maxRevenue > 0 ? ($zone['total_revenue'] / $maxRevenue) * 100 : 0;
                $color = $pct > 75 ? 'danger' : ($pct > 40 ? 'warning' : 'success');
              ?>
              <tr>
                <td><strong><?= htmlspecialchars($zone['city_zone'] ?: 'Unspecified') ?></strong></td>
                <td><?= number_format($zone['total_bookings']) ?></td>
                <td class="fw-bold"><?= number_format($zone['total_revenue'], 2) ?> EGP</td>
                <td><?= number_format($zone['avg_transaction'], 2) ?> EGP</td>
                <td style="width:120px;">
                  <div class="progress" style="height:12px;">
                    <div class="progress-bar bg-<?= $color ?>" style="width:<?= $pct ?>%;"></div>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($heatData)): ?>
              <tr><td colspan="5" class="text-center text-muted py-4">No revenue data yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- TOP ZONES CARDS -->
    <div class="col-md-5">
      <div class="card h-100">
        <div class="card-header">🏆 Top Revenue Zones</div>
        <div class="card-body">
          <?php foreach (array_slice($heatData, 0, 5) as $i => $zone): ?>
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="text-center" style="min-width:36px;">
              <span class="badge bg-<?= ['danger','warning','info','secondary','light text-dark'][$i] ?? 'secondary' ?> rounded-pill"><?= $i+1 ?></span>
            </div>
            <div class="flex-grow-1">
              <strong><?= htmlspecialchars($zone['city_zone'] ?: 'Unknown') ?></strong>
              <div class="text-muted small"><?= $zone['total_bookings'] ?> bookings</div>
            </div>
            <div class="fw-bold text-success"><?= number_format($zone['total_revenue'], 0) ?> EGP</div>
          </div>
          <?php endforeach; ?>
          <?php if (empty($heatData)): ?>
          <p class="text-muted">No data yet.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
