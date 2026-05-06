<?php
// driver/pay_fine.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/Fine.php';
require_once __DIR__ . '/../classes/User.php';

$user    = currentUser();
$fineObj = new Fine();
$userObj = new User();
$fineId  = (int)($_GET['id'] ?? 0);

$result  = $fineObj->markFinePaid($fineId, $user['user_id']);
if ($result) {
    // Re-check blacklist — if fines drop below 3, lift might be needed
    $userObj->checkAndApplyBlacklist($user['user_id']);
    setFlash('success', 'Fine paid successfully. Thank you!');
} else {
    setFlash('error', 'Could not process payment. Please try again.');
}
header('Location: /parking_system/index.php?action=my_fines');
exit;
