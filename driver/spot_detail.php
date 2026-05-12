<?php
// driver/spot_detail.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/ParkingSpot.php';
require_once __DIR__ . '/../classes/Vehicle.php';
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Spot Details — Rakna';
$user      = currentUser();
$b         = BASE_URL;
$spotObj   = new ParkingSpot();
$db        = getDB();
$spotId    = (int)($_GET['id'] ?? 0);
$spot      = $spotObj->getSpotById($spotId);

if (!$spot) { setFlash('error','Spot not found.'); header("Location:$b/index.php?action=search_spots"); exit; }

// Handle add to favorites
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action']??'') === 'favorite') {
    $stmt = $db->prepare("INSERT IGNORE INTO favorites (driver_id, spot_id, label) VALUES (?,?,?)");
    $stmt->execute([$user['user_id'], $spotId, $_POST['label'] ?? 'other']);
    setFlash('success','Added to favorites!');
    header("Location:$b/index.php?action=spot_detail&id=$spotId"); exit;
}

// Check if favorited
$stmt = $db->prepare("SELECT 1 FROM favorites WHERE driver_id=? AND spot_id=?");
$stmt->execute([$user['user_id'], $spotId]);
$isFavorited = (bool)$stmt->fetchColumn();

// Get reviews
require_once __DIR__ . '/../classes/Vehicle.php';
$reviewObj = new Review();
$reviews   = $reviewObj->getSpotReviews($spotId, 10);

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
.btn-outline-primary {
    color: #480959;
    border-color: #480959;
}
.btn-outline-primary:hover {
    background-color: #480959;
    color: #fff;
}
.btn-outline-danger {
    color: #dc3545;
    border-color: #dc3545;
}
.btn-outline-danger:hover {
    background-color: #dc3545;
    color: #fff;
}
.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}
.text-primary {
    color: #480959 !important;
}
.card-header {
    background-color: #480959;
    color: #fff;
    font-weight: bold;
}
.badge.bg-dark {
    background-color: #480959 !important;
}
.badge.bg-success {
    background-color: #198754 !important;
}
.breadcrumb .active {
    color: #480959;
}
</style>

<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= $b ?>/index.php?action=search_spots">Find Parking</a></li>
      <li class="breadcrumb-item active"><?= htmlspecialchars($spot['title']) ?></li>
    </ol>
  </nav>

  <div class="row g-4">
    <div class="col-md-7">
      <div class="card mb-4">
        <div class="card-body">
          <?php if (!empty($spot['garage_name'])): ?>
          <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge bg-dark"><i class="bi bi-building me-1"></i><?= htmlspecialchars($spot['garage_name']) ?></span>
            <?php if (!empty($spot['spot_number'])): ?>
            <span class="badge bg-secondary font-monospace fs-6"><?= htmlspecialchars($spot['spot_number']) ?></span>
            <?php endif; ?>
          </div>
          <?php endif; ?>
          <div class="d-flex justify-content-between align-items-start">
            <h4 class="fw-bold"><?= htmlspecialchars($spot['title']) ?></h4>
            <form method="POST" class="d-inline">
              <input type="hidden" name="action" value="favorite">
              <input type="hidden" name="label" value="other">
              <button class="btn btn-<?= $isFavorited?'danger':'outline-danger' ?> btn-sm">
                <i class="bi bi-<?= $isFavorited?'heart-fill':'heart' ?> me-1"></i><?= $isFavorited?'Saved':'Save' ?>
              </button>
            </form>
          </div>
          <p class="text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($spot['address']) ?></p>
          <div class="row g-3 mb-3">
            <div class="col-6 text-center">
              <div class="fw-bold text-primary fs-4"><?= number_format($spot['price_per_hour'],2) ?> EGP</div>
              <small class="text-muted">per hour</small>
            </div>
            <div class="col-6 text-center">
              <div class="fw-bold fs-5"><?= ucfirst($spot['spot_type']) ?></div>
              <small class="text-muted">Type</small>
            </div>
          </div>
          <!-- Trust Score & Reviews -->
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="text-warning fs-5"><?= str_repeat('★', round($spot['trust_score'] ?? 0)) ?><?= str_repeat('☆', 5 - round($spot['trust_score'] ?? 0)) ?></span>
            <span class="text-muted small"><?= number_format($spot['trust_score'] ?? 0, 1) ?>/5 (<?= $spot['total_reviews'] ?? 0 ?> reviews)</span>
          </div>
          <div class="d-flex gap-2 flex-wrap mb-3">
            <span class="badge bg-secondary"><?= ucfirst($spot['spot_type']) ?></span>
            <?php if($spot['has_ev_charger']): ?><span class="badge bg-success"><i class="bi bi-lightning-charge me-1"></i>EV Charger</span><?php endif; ?>
          </div>
          <p class="text-muted"><?= nl2br(htmlspecialchars($spot['description']??'')) ?></p>
          <p class="small text-muted">Owner: <strong><?= htmlspecialchars($spot['owner_name']) ?></strong></p>
          <a href="<?= $b ?>/index.php?action=book_spot&id=<?= $spotId ?>" class="btn btn-primary btn-lg w-100 mt-2">
            <i class="bi bi-calendar-check me-1"></i>Book This Spot
          </a>
        </div>
      </div>

      <!-- REVIEWS -->
      <div class="card">
        <div class="card-header"><i class="bi bi-star-fill me-1"></i> Reviews (<?= count($reviews) ?>)</div>
        <div class="card-body p-0">
          <?php if(empty($reviews)): ?>
          <p class="text-muted p-3">No reviews yet.</p>
          <?php else: foreach($reviews as $r): ?>
          <div class="p-3 border-bottom">
            <div class="d-flex justify-content-between">
              <strong><?= htmlspecialchars($r['reviewer_name']) ?></strong>
              <span class="text-warning"><?= str_repeat('★',$r['rating']) ?><?= str_repeat('☆',5-$r['rating']) ?></span>
            </div>
            <?php if($r['comment']): ?><p class="small text-muted mb-0 mt-1"><?= htmlspecialchars($r['comment']) ?></p><?php endif; ?>
            <small class="text-muted"><?= date('M d, Y',strtotime($r['created_at'])) ?></small>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>
  </div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>