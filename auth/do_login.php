<?php
// auth/do_login.php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../classes/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login');
}

$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');

if (!$email || !$password) {
    setFlash('error', 'Please fill in all fields.');
    redirect('login');
}

$userObj = new User();
$result  = $userObj->login($email, $password);

if ($result['success']) {
    $role = $result['role'];
    if ($role === 'admin')       redirect('admin_dashboard');
    elseif ($role === 'owner')   redirect('owner_dashboard');
    else                         redirect('driver_dashboard');
} else {
    setFlash('error', $result['message']);
    redirect('login');
}
