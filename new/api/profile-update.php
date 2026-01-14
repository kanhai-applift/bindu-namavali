<?php
require_once 'api-helper.php';

/* 3️⃣ Authorization check */
if (!in_array($_SESSION['role'], ['admin'], true)) {
  http_response_code(403);
  respond('error', 'Unauthorized');
}

$orgId = $_SESSION['user_id'];

// $registrationNo = trim($_POST['registration_no'] ?? '');
// $email         = trim($_POST['email'] ?? '');
$officeName    = trim($_POST['office_name'] ?? '');
$contactNo     = trim($_POST['contact_no'] ?? '');

if ( $officeName === '' || $contactNo === '' ) {
    respond('error', 'All fields are required');
}

/**
 * Update profile
 */
$stmt = $mysqli->prepare("
    UPDATE users
    SET office_name = ?,
        contact_no = ? 
    WHERE id = ?
");
$stmt->bind_param(
    'ssi',
    $officeName,
    $contactNo,
    $orgId
);

$stmt->execute();

if ($stmt->affected_rows >= 0) {
    respond('success','Profile updated successfully');
} else {
    respond('error', 'Failed to update profile');
}

$stmt->close();
