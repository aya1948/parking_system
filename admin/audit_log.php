<?php
// admin/audit_log.php
require_once __DIR__ . '/../config/session.php';
requireRole('admin');
require_once __DIR__ . '/../classes/Fine.php';

$pageTitle = 'Audit Log — Rakna';
$fineObj   = new Fine();
$filter    = $_GET['action_filter'] ?? '';
$logs      = $fineObj->getAuditLog(200, $filter ?: null);

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <h4 class="fw-bold mb-4">📋 Non-Repudiable Audit Log</h4>
  <div class="card mb-3 p-3">
    <form method="GET" class="d-flex gap-2">
      <input type="hidden" name="action" value="audit_log">
      <input type="text" name="action_filter" class="form-control" placeholder="Filter by action (e.g. USER_LOGIN)" value="<?= htmlspecialchars($filter) ?>">
      <button class="btn btn-primary">Filter</button>
      <a href="/parking_system/index.php?action=audit_log" class="btn btn-outline-secondary">Clear</a>
    </form>
  </div>
  <div class="card">
    <div class="table-responsive">
      <table class="table table-sm table-hover font-monospace mb-0">
        <thead class="table-dark"><tr><th>Time</th><th>User</th><th>Action</th><th>Table</th><th>Target</th><th>IP</th></tr></thead>
        <tbody>
          <?php foreach ($logs as $log): ?>
          <tr>
            <td><small><?= date('M d H:i:s', strtotime($log['logged_at'])) ?></small></td>
            <td><small><?= htmlspecialchars($log['full_name'] ?? 'System') ?></small></td>
            <td>
              <?php
              $logColor = str_contains($log['action'],'LOGIN')?'success':(str_contains($log['action'],'DELETE')||str_contains($log['action'],'BLACKLIST')?'danger':(str_contains($log['action'],'OVERRIDE')?'warning':'info'));
              ?>
              <span class="badge bg-<?= $logColor ?>"><?= htmlspecialchars($log['action']) ?></span>
            </td>
            <td><small><?= htmlspecialchars($log['target_table'] ?? '—') ?></small></td>
            <td><small>#<?= $log['target_id'] ?? '—' ?></small></td>
            <td><small class="text-muted"><?= htmlspecialchars($log['ip_address']) ?></small></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
