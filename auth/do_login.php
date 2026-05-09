<?php
// auth/do_login.php
session_start();
if (!defined('BASE_URL')) define('BASE_URL', '/parking_system');
$base = BASE_URL;

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: $base/index.php?action=login"); exit;
}

$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');

if (!$email || !$password) {
    $_SESSION['flash'] = ['type'=>'error', 'msg'=>'Please fill in all fields.'];
    header("Location: $base/index.php?action=login"); exit;
}

$userObj = new User();
$result  = $userObj->login($email, $password);

if ($result['success']) {
    switch ($result['role']) {
        case 'admin': header("Location: $base/index.php?action=admin_dashboard"); break;
        case 'owner': header("Location: $base/index.php?action=owner_dashboard"); break;
        default:      header("Location: $base/index.php?action=driver_dashboard"); break;
    }
} else {
    $_SESSION['flash'] = ['type'=>'error', 'msg'=>$result['message']];
    header("Location: $base/index.php?action=login");
}
exit;
