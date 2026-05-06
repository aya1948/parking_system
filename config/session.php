<?php
// config/session.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// BASE_URL: change this if your folder name is different
if (!defined('BASE_URL')) {
    define('BASE_URL', '/parking_system');
}

function redirect(string $action): void {
    header('Location: ' . BASE_URL . '/index.php?action=' . $action);
    exit;
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/index.php?action=login');
        exit;
    }
}

function requireRole(string ...$roles): void {
    requireLogin();
    $user = currentUser();
    if (!in_array($user['role'], $roles)) {
        header('Location: ' . BASE_URL . '/index.php?action=login&error=unauthorized');
        exit;
    }
}

function setFlash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): ?array {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}
