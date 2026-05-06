<?php
// owner/do_add_spot.php
require_once __DIR__ . '/../config/session.php';
requireRole('owner');
require_once __DIR__ . '/../classes/ParkingSpot.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /parking_system/index.php?action=add_spot'); exit; }

$user    = currentUser();
$spotObj = new ParkingSpot();

$result = $spotObj->createSpot([
    'owner_id'      => $user['user_id'],
    'title'         => trim($_POST['title'] ?? ''),
    'description'   => trim($_POST['description'] ?? ''),
    'address'       => trim($_POST['address'] ?? ''),
    'latitude'      => $_POST['latitude']  ?: null,
    'longitude'     => $_POST['longitude'] ?: null,
    'spot_type'     => $_POST['spot_type'] ?? 'driveway',
    'price_per_hour'=> (float)($_POST['price_per_hour'] ?? 0),
    'max_height_cm' => $_POST['max_height_cm'] ?: null,
    'max_width_cm'  => $_POST['max_width_cm']  ?: null,
    'has_ev_charger'=> isset($_POST['has_ev_charger']) ? 1 : 0,
    'city_zone'     => trim($_POST['city_zone'] ?? ''),
]);

if ($result['success']) {
    setFlash('success', 'Spot listed! Please submit verification documents to activate it.');
    header("Location: /parking_system/index.php?action=verify_spot&spot_id={$result['spot_id']}");
} else {
    setFlash('error', 'Failed to create spot. Please try again.');
    header('Location: /parking_system/index.php?action=add_spot');
}
exit;
