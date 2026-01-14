<?php
require_once 'api-helper.php';

/* 3️⃣ Authorization check */
if (!in_array($_SESSION['role'], ['admin', 'superadmin'], true)) {
  http_response_code(403);
  respond('error', 'Unauthorized');
}

$orgId = $_SESSION['user_id'];

$currentPassword = $_POST['current_password'] ?? '';
$newPassword     = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
    respond('error', 'All fields are required');
}

if ($newPassword !== $confirmPassword) {
    respond('error', 'New passwords do not match');
}

if (strlen($newPassword) < 8) {
    respond('error', 'Password must be at least 8 characters long');
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
