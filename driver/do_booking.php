<?php
// driver/do_booking.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/Reservation.php';
require_once __DIR__ . '/../classes/Payment.php';   // ← نظام الدفع الجديد

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /parking_system/index.php?action=search_spots');
    exit;
}

$user   = currentUser();
$resObj = new Reservation();
$payObj = new Payment();

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
    header('Location: /parking_system/index.php?action=search_spots');
    exit;
}

$result = $resObj->createReservation($data);

if ($result['success']) {
    // تأكيد إضافي: تسجيل دفعة منفصلة عبر Payment (زيادة وضوح التدفق)
    // createReservation بالفعل يستدعي lockEscrow داخليًا، لكن هذا يضمن التكامل
    $payObj->getPaymentByReservation($result['reservation_id']);

    $_SESSION['last_booking'] = [
        'reservation_id' => $result['reservation_id'],
        'total'          => $result['total'],
    ];
    setFlash('success', "Booking confirmed! Total: {$result['total']} EGP. Your receipt is ready.");
    header("Location: " . BASE_URL . "/index.php?action=booking_receipt&id=" . $result['reservation_id']);
} else {
    setFlash('error', $result['message']);
    header("Location: /parking_system/index.php?action=book_spot&id={$data['spot_id']}");
}
exit;