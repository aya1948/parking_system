<?php
// driver/do_booking.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/Reservation.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /parking_system/index.php?action=search_spots'); exit; }

$user   = currentUser();
$resObj = new Reservation();

$data = [
    'driver_id'  => $user['user_id'],
    'spot_id'    => (int)($_POST['spot_id']    ?? 0),
    'vehicle_id' => (int)($_POST['vehicle_id'] ?? 0),
    'start_time' => $_POST['start_time'] ?? '',
    'end_time'   => $_POST['end_time']   ?? '',
    'promo_code' => trim($_POST['promo_code'] ?? '') ?: null,
];

if (!$data['spot_id'] || !$data['vehicle_id'] || !$data['start_time'] || !$data['end_time']) {
    setFlash('error', 'Missing booking information.');
    header('Location: /parking_system/index.php?action=search_spots'); exit;
}

$result = $resObj->createReservation($data);

if ($result['success']) {
    setFlash('success', "Booking confirmed! Total: {$result['total']} EGP. Your QR code is ready.");
    header("Location: /parking_system/index.php?action=my_reservations");
} else {
    setFlash('error', $result['message']);
    header("Location: /parking_system/index.php?action=book_spot&id={$data['spot_id']}");
}
exit;
