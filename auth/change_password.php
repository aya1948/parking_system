<?php
// auth/change_password.php — redirects to profile
require_once __DIR__ . '/../config/session.php';
requireLogin();
header('Location: ' . BASE_URL . '/index.php?action=profile');
exit;
