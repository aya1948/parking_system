<?php
// auth/register.php
$pageTitle = 'Register — Rakna';
require_once __DIR__ . '/../config/session.php';
if (isLoggedIn()) { header('Location: /parking_system/index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
  body {
    background-image: url('/parking_system/assets/Gemini_Generated_Image_41ndl641ndl641nd.png');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    min-height: 100vh;
    display: flex;
    align-items: center;
}
    .card { border:none; border-radius:16px; box-shadow:0 20px 60px rgba(0,0,0,.3); }
    .brand { font-size:2rem; font-weight:800; color:#480959; }
    .btn-primary { background-color: #480959; border-color: #480959; }
    .btn-primary:hover { background-color: #5e2b6d; border-color: #5e2b6d; }
    .btn-outline-primary { color: #480959; border-color: #480959; }
    .btn-outline-primary:hover, .btn-check:checked + .btn-outline-primary { background-color: #480959; border-color: #480959; color: #fff; }
    .btn-outline-warning { color: #480959; border-color: #480959; }
    .btn-outline-warning:hover, .btn-check:checked + .btn-outline-warning { background-color: #480959; border-color: #480959; color: #fff; }
    a { color: #480959; }
    a:hover { color: #5e2b6d; }
  </style>
</head>
<body>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card p-4">
        <div class="text-center mb-4">
          <div class="brand"><i class="bi bi-p-circle-fill me-2"></i>Rakna</div>
          <p class="text-muted">Create Your Account</p>
        </div>
        <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($flash['msg']) ?></div>
        <?php endif; ?>

        <form action="/parking_system/index.php?action=do_register" method="POST">
          <div class="mb-3">
            <label class="form-label fw-semibold">Full Name</label>
            <input type="text" name="full_name" class="form-control" placeholder="Ahmed Mohamed" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Phone Number</label>
            <input type="tel" name="phone" class="form-control" placeholder="+20 1XX XXXX XXX">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Min. 8 characters" minlength="8" required>
          </div>
          <div class="mb-4">
            <label class="form-label fw-semibold">I want to join as</label>
            <div class="row g-2">
              <div class="col-6">
                <input type="radio" class="btn-check" name="role" id="roleDriver" value="driver" checked>
                <label class="btn btn-outline-primary w-100" for="roleDriver"><i class="bi bi-car-front me-1"></i> Driver</label>
              </div>
              <div class="col-6">
                <input type="radio" class="btn-check" name="role" id="roleOwner" value="owner">
                <label class="btn btn-outline-primary w-100" for="roleOwner"><i class="bi bi-building me-1"></i> Space Owner</label>
              </div>
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-lg w-100">Create Account</button>
        </form>
        <hr>
        <p class="text-center mb-0">Already have an account? <a href="/parking_system/index.php?action=login">Login</a></p>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>