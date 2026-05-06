<?php
// owner/do_edit_spot.php
require_once __DIR__ . '/../config/session.php';
requireRole('owner');
require_once __DIR__ . '/../classes/ParkingSpot.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /parking_system/index.php?action=my_spots'); exit;
}

$user    = currentUser();
$spotObj = new ParkingSpot();
$spotId  = (int)($_POST['spot_id'] ?? 0);

$result = $spotObj->updateSpot($spotId, [
    'title'          => trim($_POST['title'] ?? ''),
    'description'    => trim($_POST['description'] ?? ''),
    'address'        => trim($_POST['address'] ?? ''),
    'price_per_hour' => (float)($_POST['price_per_hour'] ?? 0),
    'max_height_cm'  => $_POST['max_height_cm'] ?: null,
    'max_width_cm'   => $_POST['max_width_cm']  ?: null,
    'has_ev_charger' => isset($_POST['has_ev_charger']) ? 1 : 0,
], $user['user_id']);

if ($result) {
    setFlash('success', 'Spot updated successfully!');
} else {
    setFlash('error', 'Could not update spot. Please try again.');
}
header('Location: /parking_system/index.php?action=my_spots');
exit;
