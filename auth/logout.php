<?php
// auth/logout.php
require_once __DIR__ . '/../config/session.php';

$userId = $_SESSION['user_id'] ?? null;
if ($userId) {
    require_once __DIR__ . '/../config/db.php';
    try {
        $db   = getDB();
        $stmt = $db->prepare("INSERT INTO audit_log (user_id, action, target_table, target_id, ip_address) VALUES (?,?,?,?,?)");
        $stmt->execute([$userId, 'USER_LOGOUT', 'users', $userId, $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
    } catch (Exception $e) { /* silent */ }
}
session_unset();
session_destroy();
header('Location: ' . BASE_URL . '/index.php?action=login');
exit;
