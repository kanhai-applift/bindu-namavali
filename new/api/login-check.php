<?php
declare(strict_types=1);

require_once 'api-helper.php';

/* ===============================
   3. Input Normalization
================================ */
$email    = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

if (
    $email === '' ||
    strlen($email) > 255 ||
    $password === ''
) {
    respond('error', 'Invalid login credentials');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond('error', 'Invalid login credentials');
}

/* ===============================
   4. Rate Limiting (Critical)
================================ */
/*
if (!rateLimit('login_' . $email, 5, 300)) {
    respond('error', 'Too many login attempts. Try again later.');
}
*/

/* ===============================
   5. Fetch User (Prepared Statement)
================================ */
$stmt = $mysqli->prepare(
    "SELECT id, office_name, head_name, password_hash, role, status
     FROM users
     WHERE email = ?
     LIMIT 1"
);

$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    respond('error', 'Invalid login credentials');
}

$user = $result->fetch_assoc();
$stmt->close();

/* ===============================
   6. Account Status Check
================================ */
if ($user['status'] !== 'active') {
    respond('error', 'Account not active');
}

/* ===============================
   7. Password Verification
================================ */
if (!password_verify($password, $user['password_hash'])) {
    respond('error', 'Invalid login credentials');
}

/* ===============================
   8. Secure Session Handling
================================ */
session_regenerate_id(true);

$_SESSION['user_id']     = (int) $user['id'];
$_SESSION['name']        = $user['head_name'];
$_SESSION['role']        = $user['role'];
$_SESSION['office_name'] = $user['office_name'];
$_SESSION['logged_in']   = true;
$_SESSION['last_active'] = time();

/* ===============================
   9. Secure IP Logging (Admin Only)
================================ */
if ($user['role'] === 'admin') {

    function getClientIP(): string
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    }

    $ip = getClientIP();

    $logStmt = $mysqli->prepare(
        "INSERT INTO user_access_logs
         (user_id, email, ip_address)
         VALUES (?, ?, ?)"
    );

    $logStmt->bind_param(
        'iss',
        $user['id'],
        $email,
        $ip
    );

    $logStmt->execute();
    $logStmt->close();
}

/* ===============================
   10. Unified Response
================================ */
respond('success', 'Login successful', [
    'redirect' => 'dashboard'
]);
