<?php
session_start();
require_once __DIR__."/api-helper.php";

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    respond('error', 'Email and password required');
}

$stmt = $mysqli->prepare("
    SELECT id, office_name, head_name, password_hash, role, status
    FROM users
    WHERE email = ?
    LIMIT 1
");

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    respond('error', 'Invalid login credentials');
}

$user = $result->fetch_assoc();

if ($user['status'] !== 'active') {
    respond('error', 'Account not active');
}

if (!password_verify($password, $user['password_hash'])) {
    respond('error', 'Invalid login credentials');
}


function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
}

if ($user['role'] === 'admin') {

    $ip = getUserIP();
    $city = null;
    $state = null;

    $logStmt = $mysqli->prepare("
        INSERT INTO user_access_logs
        (user_id, email, ip_address, city, state)
        VALUES (?, ?, ?, ?, ?)
    ");

    $logStmt->bind_param(
        "issss",
        $user['id'],
        $email,
        $ip,
        $city,
        $state
    );

    $logStmt->execute();
}


/* SESSION CREATION */
$_SESSION['user_id'] = $user['id'];
$_SESSION['name'] = $user['head_name'];
$_SESSION['role'] = $user['role'];
$_SESSION['office_name'] = $user['office_name'];
$_SESSION['logged_in'] = true;

/* Redirect logic */
$redirect = 'dashboard';

respond('success', 'Login successful', [
    'redirect' => $redirect
]);


