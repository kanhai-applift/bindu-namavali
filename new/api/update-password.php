<?php
require_once 'api-helper.php';

/* 3️⃣ Authorization check */
if (!in_array($_SESSION['role'], ['admin', 'superadmin'], true)) {
  http_response_code(403);
  respond('error', 'Unauthorized');
}

$orgId = $_SESSION['user_id'];

$currentPassword = trim($_POST['current_password']) ?? '';
$newPassword     = trim($_POST['new_password']) ?? '';
$confirmPassword = trim($_POST['confirm_password']) ?? '';

// 1. Basic empty check
if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
    respond('error', 'All fields are required');
}

// 2. Matching check
if ($newPassword !== $confirmPassword) {
    respond('error', 'New passwords do not match');
}

// 3. Prevent using the same password
if ($currentPassword === $newPassword) {
    respond('error', 'New password cannot be the same as current password');
}

/** * 4. Strong Password Enforcement
 * Logic: Min 8 chars, at least 1 Uppercase, 1 Lowercase, 1 Number, 1 Special Char
 */
$regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

if (!preg_match($regex, $newPassword)) {
    respond('error', 'Password must be at least 8 characters and include uppercase, lowercase, a number, and a special character (@$!%*?&)');
}


/**
 * Fetch existing password hash
 */
$stmt = $mysqli->prepare("
    SELECT password_hash
    FROM users
    WHERE id = ?
");
$stmt->bind_param('i', $orgId);
$stmt->execute();
$stmt->bind_result($passwordHash);
$stmt->fetch();
$stmt->close();

if (!$passwordHash || !password_verify($currentPassword, $passwordHash)) {
    respond('error', 'Current password is incorrect');
}

/**
 * Update password
 */
$newHash = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $mysqli->prepare("
    UPDATE users
    SET password_hash = ?
    WHERE id = ?
");
$stmt->bind_param('si', $newHash, $orgId);
$stmt->execute();

if ($stmt->affected_rows === 1) {
    respond('success', 'Password updated successfully');
} else {
    respond('error', 'Failed to update password');
}

$stmt->close();
