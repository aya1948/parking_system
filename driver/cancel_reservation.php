<?php
// driver/cancel_reservation.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/Reservation.php';

$user   = currentUser();
$resObj = new Reservation();
$id     = (int)($_GET['id'] ?? 0);
$result = $resObj->cancelReservation($id, $user['user_id']);
setFlash($result['success'] ? 'success' : 'error', $result['message']);
header('Location: /parking_system/index.php?action=my_reservations');
exit;
