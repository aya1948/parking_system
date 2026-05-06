<?php
// auth/login.php
$pageTitle = 'Login — CitySlot';
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
  <style>
    body { background: linear-gradient(135deg, #0d1b2a 0%, #1a73e8 100%); min-height: 100vh; display: flex; align-items: center; }
    .card { border: none; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,.3); }
    .brand { font-size: 2rem; font-weight: 800; color: #1a73e8; }
  </style>
</head>
<body>
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-4">
      <div class="card p-4">
        <div class="text-center mb-4">
          <div class="brand">🅿️ CitySlot</div>
          <p class="text-muted">Smart Urban Parking</p>
        </div>
        <?php
        $flash = getFlash();
        if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?>">
          <?= htmlspecialchars($flash['msg']) ?>
        </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <form action="/parking_system/index.php?action=do_login" method="POST">
          <div class="mb-3">
            <label class="form-label fw-semibold">Email Address</label>
            <input type="email" name="email" class="form-control form-control-lg" placeholder="you@example.com" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Password</label>
            <input type="password" name="password" class="form-control form-control-lg" placeholder="••••••••" required>
          </div>
          <button type="submit" class="btn btn-primary btn-lg w-100">Login</button>
        </form>
        <hr>
        <p class="text-center mb-0">Don't have an account? <a href="/parking_system/index.php?action=register">Register</a></p>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
