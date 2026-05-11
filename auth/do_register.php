<?php
// auth/do_register.php
session_start();
if (!defined('BASE_URL')) define('BASE_URL', '/parking_system');
$base = BASE_URL;

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: $base/index.php?action=register"); exit;
}

$name     = trim($_POST['full_name'] ?? '');
$email    = trim($_POST['email']     ?? '');
$password = trim($_POST['password']  ?? '');
$phone    = trim($_POST['phone']     ?? '');
$role     = in_array($_POST['role'] ?? '', ['driver','owner']) ? $_POST['role'] : 'driver';

if (!$name || !$email || !$password) {
    $_SESSION['flash'] = ['type'=>'error', 'msg'=>'Please fill in all required fields.'];
    header("Location: $base/index.php?action=register"); exit;
}
if (strlen($password) < 8) {
    $_SESSION['flash'] = ['type'=>'error', 'msg'=>'Password must be at least 8 characters.'];
    header("Location: $base/index.php?action=register"); exit;
}

$userObj = new User();
$result  = $userObj->register($name, $email, $password, $role, $phone);

if ($result['success']) {
    $login = $userObj->login($email, $password);
    if ($login['success']) {
        $_SESSION['flash'] = ['type'=>'success', 'msg'=>'Welcome to Rakna, ' . $name . '!'];
        header("Location: $base/index.php?action=" . ($role === 'owner' ? 'owner_dashboard' : 'driver_dashboard'));
    } else {
        header("Location: $base/index.php?action=login");
    }
} else {
    $_SESSION['flash'] = ['type'=>'error', 'msg'=>$result['message']];
    header("Location: $base/index.php?action=register");
}
exit;
