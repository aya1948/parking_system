<?php
// driver/submit_review.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/Vehicle.php';

// Vehicle.php includes the Review class too
$pageTitle = 'Leave a Review — Rakna';
$user      = currentUser();
$resId     = (int)($_GET['reservation_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../classes/Vehicle.php';
    $review = new Review();
    $result = $review->submitReview(
        (int)$_POST['reservation_id'],
        $user['user_id'],
        (int)$_POST['rating'],
        $_POST['difficulty_rating'] ? (int)$_POST['difficulty_rating'] : null,
        trim($_POST['comment'] ?? '')
    );
    setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Review submitted!' : $result['message']);
    header('Location: /parking_system/index.php?action=my_reservations'); exit;
}

require_once __DIR__ . '/../includes/header.php';
?>
<style>
/* ألوان Rakna */
.btn-warning {
    background-color: #480959;
    border-color: #480959;
    color: #fff;
}
.btn-warning:hover {
    background-color: #8A2888;
    border-color: #8A2888;
    color: #fff;
}
.card-header {
    background-color: #480959;
    color: #fff;
    font-weight: bold;
}
</style>

<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card">
        <div class="card-header"><i class="bi bi-star-fill me-1"></i> Leave a Review</div>
        <div class="card-body">
          <form method="POST">
            <input type="hidden" name="reservation_id" value="<?= $resId ?>">
            <div class="mb-4">
              <label class="form-label fw-semibold">Overall Rating *</label>
              <div class="d-flex gap-2">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="rating" value="<?= $i ?>" id="r<?= $i ?>" required>
                  <label class="form-check-label fs-5" for="r<?= $i ?>"><?= str_repeat('★', $i) ?></label>
                </div>
                <?php endfor; ?>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Parking Difficulty</label>
              <select name="difficulty_rating" class="form-select">
                <option value="">Skip</option>
                <option value="1">1 — Very Easy</option>
                <option value="2">2 — Easy</option>
                <option value="3">3 — Moderate</option>
                <option value="4">4 — Difficult</option>
                <option value="5">5 — Very Difficult</option>
              </select>
            </div>
            <div class="mb-4">
              <label class="form-label fw-semibold">Comment</label>
              <textarea name="comment" class="form-control" rows="4" placeholder="Share your experience..."></textarea>
            </div>
            <button type="submit" class="btn btn-warning w-100 fw-bold"><i class="bi bi-send-check me-1"></i>Submit Review</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>