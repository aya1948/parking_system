<?php
// driver/price_preview.php — AJAX endpoint for live price calculation
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../classes/Pricing.php';

header('Content-Type: application/json');

// Must be logged in (any role can preview)
if (!currentUser()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$spotId    = (int)($_POST['spot_id']    ?? 0);
$startTime = trim($_POST['start_time'] ?? '');
$endTime   = trim($_POST['end_time']   ?? '');
$driverId  = (int)(currentUser()['user_id'] ?? 0);
$promoCode = trim($_POST['promo_code'] ?? '') ?: null;

if (!$spotId || !$startTime || !$endTime) {
    echo json_encode(['error' => 'Missing fields']);
    exit;
}

// Basic time validation
$start = strtotime($startTime);
$end   = strtotime($endTime);
if (!$start || !$end || $end <= $start) {
    echo json_encode(['error' => 'Invalid time range']);
    exit;
}

try {
    $pricing = new Pricing();
    // incrementPromo = false — preview only, do NOT consume promo usage
    $result  = $pricing->calculateTotal($spotId, $startTime, $endTime, $driverId, $promoCode, false);
    echo json_encode(['success' => true, 'breakdown' => $result]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Calculation failed']);
}
exit;
