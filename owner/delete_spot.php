<?php
// owner/delete_spot.php
require_once __DIR__ . '/../config/session.php';
requireRole('owner');
require_once __DIR__ . '/../config/db.php';

$user = currentUser();
$id   = (int)($_GET['id'] ?? 0);

if (!$id) {
    setFlash('error', 'Invalid spot.');
    header('Location: ' . BASE_URL . '/index.php?action=my_spots');
    exit;
}

$db   = getDB();

// Verify ownership
$stmt = $db->prepare("SELECT owner_id FROM parking_spots WHERE spot_id = ?");
$stmt->execute([$id]);
$spot = $stmt->fetch();

if (!$spot || $spot['owner_id'] != $user['user_id']) {
    setFlash('error', 'Spot not found or access denied.');
    header('Location: ' . BASE_URL . '/index.php?action=my_spots');
    exit;
}

// Soft-delete: set status to inactive (preserves historical reservations/transactions)
$stmt = $db->prepare("UPDATE parking_spots SET status = 'inactive' WHERE spot_id = ? AND owner_id = ?");
$ok   = $stmt->execute([$id, $user['user_id']]);

if ($ok && $stmt->rowCount() > 0) {
    setFlash('success', 'Spot deactivated successfully.');
} else {
    setFlash('error', 'Could not deactivate spot. It may have active reservations.');
}
header('Location: ' . BASE_URL . '/index.php?action=my_spots');
exit;
