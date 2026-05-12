<?php
// driver/favorites.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Favorites — Rakna';
$user      = currentUser();
$b         = BASE_URL;
$db        = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action']??'') === 'remove') {
    $stmt = $db->prepare("DELETE FROM favorites WHERE driver_id=? AND spot_id=?");
    $stmt->execute([$user['user_id'], (int)$_POST['spot_id']]);
    setFlash('success','Removed from favorites.');
    header("Location:$b/index.php?action=favorites"); exit;
}

$stmt = $db->prepare("
    SELECT f.*, s.title, s.address, s.price_per_hour,
           s.spot_type, s.status, s.has_ev_charger, s.spot_id
    FROM favorites f
    JOIN parking_spots s ON f.spot_id = s.spot_id
    WHERE f.driver_id = ?
    ORDER BY f.created_at DESC
");
$stmt->execute([$user['user_id']]);
$favorites = $stmt->fetchAll();

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
.btn-outline-danger {
    color: #dc3545;
    border-color: #dc3545;
}
.btn-outline-danger:hover {
    background-color: #dc3545;
    color: #fff;
}
.text-primary {
    color: #480959 !important;
}
.badge.bg-primary {
    background-color: #480959 !important;
}
.badge.bg-success {
    background-color: #198754 !important;
}
</style>

<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <h4 class="fw-bold mb-4"><i class="bi bi-heart-fill me-2"></i>Favorite Spots</h4>
  <?php if(empty($favorites)): ?>
  <div class="text-center py-5">
    <div style="font-size:4rem;"><i class="bi bi-heartbreak"></i></div>
    <p class="text-muted mt-3">No favorites yet. Save spots you like for quick rebooking!</p>
    <a href="<?= $b ?>/index.php?action=search_spots" class="btn btn-primary">Find Parking</a>
  </div>
  <?php else: ?>
  <div class="row g-3">
    <?php foreach($favorites as $f): ?>
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between mb-2">
            <span class="badge bg-<?= $f['label']==='home'?'primary':($f['label']==='work'?'success':'secondary') ?>">
              <?= $f['label']==='home'?'<i class="bi bi-house-door me-1"></i> Home':($f['label']==='work'?'<i class="bi bi-briefcase me-1"></i> Work':'<i class="bi bi-geo-alt me-1"></i> Other') ?>
            </span>
            <span class="badge bg-<?= $f['status']==='available'?'success':'secondary' ?>"><?= ucfirst($f['status']) ?></span>
          </div>
          <h6 class="fw-bold"><?= htmlspecialchars($f['title']) ?></h6>
          <p class="small text-muted mb-2"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($f['address']) ?></p>
          <div class="mb-3">
            <span class="fw-bold text-primary"><?= number_format($f['price_per_hour'],2) ?> EGP/hr</span>
          </div>
          <div class="d-flex gap-2">
            <a href="<?= $b ?>/index.php?action=book_spot&id=<?= $f['spot_id'] ?>" class="btn btn-primary btn-sm flex-fill">Book Now</a>
            <form method="POST" class="d-inline">
              <input type="hidden" name="action" value="remove">
              <input type="hidden" name="spot_id" value="<?= $f['spot_id'] ?>">
              <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash3"></i></button>
            </form>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>