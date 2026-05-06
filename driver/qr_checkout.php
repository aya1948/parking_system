<?php
// driver/qr_checkout.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/Reservation.php';

$user   = currentUser();
$resObj = new Reservation();
$resId  = (int)($_GET['id'] ?? 0);
$res    = $resObj->getReservationById($resId);

if (!$res || $res['driver_id'] != $user['user_id']) {
    setFlash('error', 'Reservation not found.');
    header('Location: /parking_system/index.php?action=my_reservations'); exit;
}

$result = $resObj->qrCheckOut($res['qr_code']);
$msg    = $result['message'];
if ($result['overstay_minutes'] > 0) {
    $msg .= " Overstay penalty: {$result['penalty_amount']} EGP for {$result['overstay_minutes']} minutes.";
}
setFlash($result['success'] ? 'success' : 'error', $msg);
header('Location: /parking_system/index.php?action=my_reservations');
exit;
