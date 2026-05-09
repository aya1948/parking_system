<?php
// driver/currency_convert.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/Pricing.php';

$pageTitle  = 'Currency Converter — CitySlot';
$user       = currentUser();
$b          = BASE_URL;
$pricingObj = new Pricing();
$result     = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $pricingObj->convertCurrency(
        (float)($_POST['amount'] ?? 0),
        strtoupper(trim($_POST['currency'] ?? 'USD'))
    );
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <h4 class="fw-bold mb-4">💱 Currency Converter</h4>
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card">
        <div class="card-header fw-bold">Convert from EGP</div>
        <div class="card-body">
          <form method="POST">
            <div class="mb-3">
              <label class="form-label fw-semibold">Amount (EGP)</label>
              <div class="input-group">
                <span class="input-group-text">EGP</span>
                <input type="number" name="amount" class="form-control form-control-lg"
                       step="0.01" min="0" placeholder="100.00"
                       value="<?= htmlspecialchars($_POST['amount'] ?? '') ?>" required>
              </div>
            </div>
            <div class="mb-4">
              <label class="form-label fw-semibold">Convert To</label>
              <select name="currency" class="form-select form-select-lg">
                <?php foreach(['USD'=>'🇺🇸 US Dollar','EUR'=>'🇪🇺 Euro','GBP'=>'🇬🇧 British Pound','SAR'=>'🇸🇦 Saudi Riyal','AED'=>'🇦🇪 UAE Dirham'] as $code=>$label): ?>
                <option value="<?= $code ?>" <?= ($_POST['currency']??'')===$code?'selected':'' ?>><?= $label ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-bold">Convert</button>
          </form>

          <?php if ($result): ?>
          <hr>
          <?php if ($result['success']): ?>
          <div class="text-center">
            <div class="text-muted small mb-1">Result</div>
            <div class="display-6 fw-bold text-primary">
              <?= number_format($result['converted'], 2) ?> <?= $result['currency'] ?>
            </div>
            <div class="text-muted small mt-2">
              <?= number_format($result['original_egp'], 2) ?> EGP
              × <?= $result['rate'] ?> = <?= number_format($result['converted'], 2) ?> <?= $result['currency'] ?>
            </div>
            <div class="alert alert-info mt-3 small">
              <i class="bi bi-info-circle me-1"></i>
              Rates are simulated for demonstration. Real rates may vary.
            </div>
          </div>
          <?php else: ?>
          <div class="alert alert-danger"><?= htmlspecialchars($result['message']) ?></div>
          <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- RATES TABLE -->
      <div class="card mt-3">
        <div class="card-header fw-bold small">Current Simulated Rates (1 EGP =)</div>
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>Currency</th><th>Rate</th></tr></thead>
            <tbody>
              <?php foreach(['USD'=>['0.032','🇺🇸'],'EUR'=>['0.029','🇪🇺'],'GBP'=>['0.025','🇬🇧'],'SAR'=>['0.120','🇸🇦'],'AED'=>['0.117','🇦🇪']] as $c=>[$r,$flag]): ?>
              <tr><td><?= $flag ?> <?= $c ?></td><td class="fw-bold"><?= $r ?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
