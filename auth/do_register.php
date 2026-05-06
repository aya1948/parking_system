<?php
// auth/do_register.php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../classes/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('register'); }

$name     = trim($_POST['full_name'] ?? '');
$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');
$phone    = trim($_POST['phone']    ?? '');
$role     = in_array($_POST['role'] ?? '', ['driver','owner']) ? $_POST['role'] : 'driver';

if (!$name || !$email || !$password) {
    setFlash('error', 'Please fill in all required fields.');
    redirect('register');
}
if (strlen($password) < 8) {
    setFlash('error', 'Password must be at least 8 characters.');
    redirect('register');
}

$userObj = new User();
$result  = $userObj->register($name, $email, $password, $role, $phone);

if ($result['success']) {
    $loginResult = $userObj->login($email, $password);
    if ($loginResult['success']) {
        setFlash('success', 'Welcome to CitySlot, ' . $name . '!');
        redirect($role === 'owner' ? 'owner_dashboard' : 'driver_dashboard');
    } else {
        redirect('login');
    }
} else {
    setFlash('error', $result['message']);
    redirect('register');
}
