<?php
date_default_timezone_set('Africa/Cairo');   // ← ضبط التوقيت للقاهرة

// index.php — Main Front Controller / Router
require_once __DIR__ . '/config/session.php';

$action = $_GET['action'] ?? 'home';
$user   = currentUser();
$role   = $user['role'] ?? 'guest';

// ── Route Map ────────────────────────────────────────────────
$publicRoutes = ['home','login','register','do_login','do_register'];

// Protect non-public routes
if (!in_array($action, $publicRoutes) && !$user) {
    setFlash('error', 'Please login to continue.');
    header('Location: /parking_system/index.php?action=login');
    exit;
}

// ── Role-based routing ────────────────────────────────────────
$routeFiles = [
    // Auth
    'login'              => 'auth/login.php',
    'register'           => 'auth/register.php',
    'do_login'           => 'auth/do_login.php',
    'do_register'        => 'auth/do_register.php',
    'logout'             => 'auth/logout.php',
    'profile'            => 'auth/profile.php',
    'change_password'    => 'auth/change_password.php',

    // Driver
    'driver_dashboard'   => 'driver/dashboard.php',
    'search_spots'       => 'driver/search_spots.php',
    'spot_detail'        => 'driver/spot_detail.php',
    'book_spot'          => 'driver/book_spot.php',
    'do_booking'         => 'driver/do_booking.php',
    'price_preview'      => 'driver/price_preview.php',
    'my_reservations'    => 'driver/my_reservations.php',
    'cancel_reservation' => 'driver/cancel_reservation.php',
    'extend_reservation' => 'driver/extend_reservation.php',
    'qr_checkin'         => 'driver/qr_checkin.php',
    'qr_checkout'        => 'driver/qr_checkout.php',
    'my_vehicles'        => 'driver/my_vehicles.php',
    'add_vehicle'        => 'driver/add_vehicle.php',
    'my_fines'           => 'driver/my_fines.php',
    'pay_fine'           => 'driver/pay_fine.php',
    'submit_appeal'      => 'driver/submit_appeal.php',
    'favorites'          => 'driver/favorites.php',
    'submit_review'      => 'driver/submit_review.php',
    'waitlist'           => 'driver/waitlist.php',
    'currency_convert'   => 'driver/currency_convert.php',
    'notifications'      => 'driver/notifications.php',
    'recurring_booking'  => 'driver/recurring_booking.php',

    // Owner
    'owner_dashboard'    => 'owner/dashboard.php',
    'my_spots'           => 'owner/my_spots.php',
    'add_spot'           => 'owner/add_spot.php',
    'do_add_spot'        => 'owner/do_add_spot.php',
    'edit_spot'          => 'owner/edit_spot.php',
    'do_edit_spot'       => 'owner/do_edit_spot.php',
    'delete_spot'        => 'owner/delete_spot.php',
    'spot_status'        => 'owner/spot_status.php',
    'owner_reservations' => 'owner/reservations.php',
    'earnings'           => 'owner/earnings.php',
    'owner_report'       => 'owner/report.php',
    'verify_spot'        => 'owner/verify_spot.php',
    'market_rate'        => 'owner/market_rate.php',
    'add_garage'         => 'owner/add_garage.php',
    'garage_map'         => 'owner/garage_map.php',
    'booking_receipt'    => 'driver/booking_receipt.php',
    'pick_spot'          => 'driver/pick_spot.php',

    // Admin
    'admin_dashboard'    => 'admin/dashboard.php',
    'manage_users'       => 'admin/manage_users.php',
    'manage_spots'       => 'admin/manage_spots.php',
    'verifications'      => 'admin/verifications.php',
    'manage_fines'       => 'admin/manage_fines.php',
    'appeals'            => 'admin/appeals.php',
    'event_zones'        => 'admin/event_zones.php',
    'heatmap'            => 'admin/heatmap.php',
    'audit_log'          => 'admin/audit_log.php',
    'promo_codes'        => 'admin/promo_codes.php',
    'peak_rules'         => 'admin/peak_rules.php',
    'emergency_override' => 'admin/emergency_override.php',
    'blacklist_manage'   => 'admin/blacklist_manage.php',
];

if ($action === 'home') {
    if ($role === 'driver') { header('Location: ' . BASE_URL . '/index.php?action=driver_dashboard'); exit; }
    if ($role === 'owner')  { header('Location: ' . BASE_URL . '/index.php?action=owner_dashboard');  exit; }
    if ($role === 'admin')  { header('Location: ' . BASE_URL . '/index.php?action=admin_dashboard');  exit; }
    // Guest → login page
    require_once 'auth/login.php';
    exit;
}

$file = $routeFiles[$action] ?? null;
if ($file && file_exists(__DIR__ . '/' . $file)) {
    require_once __DIR__ . '/' . $file;
} else {
    http_response_code(404);
    $pageTitle = '404 Not Found';
    require_once 'includes/header.php';
    echo '<div class="container mt-5 text-center"><h1>404</h1><p>Page not found.</p>
          <a href="' . BASE_URL . '/index.php" class="btn btn-primary">Go Home</a></div>';
    require_once 'includes/footer.php';
}