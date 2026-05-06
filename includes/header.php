<?php
// includes/header.php
require_once __DIR__ . '/../config/session.php';
$user  = currentUser();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'CitySlot Parking') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --primary: #1a73e8; --dark: #0d1b2a; }
        body  { background: #f4f6fb; }
        .navbar { background: var(--dark) !important; }
        .navbar-brand { color: #fff !important; font-weight: 700; font-size: 1.4rem; }
        .nav-link { color: rgba(255,255,255,.8) !important; }
        .nav-link:hover, .nav-link.active { color: #fff !important; }
        .sidebar { min-height: 100vh; background: #fff; border-right: 1px solid #e0e0e0; padding-top: 20px; }
        .sidebar .nav-link { color: #555; border-radius: 8px; margin: 2px 8px; padding: 10px 14px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #e8f0fe; color: var(--primary); }
        .sidebar .nav-link i { width: 22px; }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.06); }
        .card-header { background: transparent; border-bottom: 1px solid #f0f0f0; font-weight: 600; }
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .stat-card { border-left: 4px solid var(--primary); }
        .badge-role-driver { background: #e8f5e9; color: #2e7d32; }
        .badge-role-owner  { background: #fff3e0; color: #e65100; }
        .badge-role-admin  { background: #fce4ec; color: #c62828; }
        .spot-card:hover   { transform: translateY(-2px); transition: .2s; box-shadow: 0 6px 20px rgba(0,0,0,.1); }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid px-4">
    <a class="navbar-brand" href="/parking_system/index.php">🅿️ CitySlot</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav ms-auto align-items-center gap-2">
        <?php if ($user): ?>
          <li class="nav-item">
            <span class="badge bg-secondary"><?= ucfirst($user['role']) ?></span>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/parking_system/index.php?action=notifications">
              <i class="bi bi-bell"></i>
            </a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['full_name']) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="/parking_system/index.php?action=profile"><i class="bi bi-person me-2"></i>Profile</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="/parking_system/index.php?action=logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="/parking_system/index.php?action=login">Login</a></li>
          <li class="nav-item"><a class="btn btn-primary btn-sm" href="/parking_system/index.php?action=register">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- FLASH MESSAGES -->
<?php if ($flash): ?>
<div class="container-fluid px-4 mt-3">
  <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'error' ? 'danger' : 'info') ?> alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($flash['msg']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
</div>
<?php endif; ?>
