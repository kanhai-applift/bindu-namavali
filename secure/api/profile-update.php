<?php
require_once 'api-helper.php';

/* 3️⃣ Authorization check */
if (!in_array($_SESSION['role'], ['admin', 'superadmin'], true)) {
  http_response_code(403);
  respond('error', 'Unauthorized');
}

$orgId = (int) $_SESSION['user_id'];

/* ===============================
   4. Input Normalization & Validation
================================ */
$officeName = trim($_POST['office_name'] ?? '');
$contactNo  = trim($_POST['contact_no'] ?? '');

if ($officeName === '' || $contactNo === '') {
    respond('error', 'All fields are required');
}

/* Length limits – prevent abuse */
if (mb_strlen($officeName) > 150 || mb_strlen($contactNo) > 20) {
    respond('error', 'Invalid input length');
}

/* Contact number validation (digits, +, - allowed) */
if (!preg_match('/^[0-9+\-\s]{7,20}$/', $contactNo)) {
    respond('error', 'Invalid contact number');
}

/* ===============================
   5. Update Profile (Prepared)
================================ */
$stmt = $mysqli->prepare(
    "UPDATE users
     SET office_name = ?, contact_no = ?
     WHERE id = ?"
);

$stmt->bind_param(
    'ssi',
    $officeName,
    $contactNo,
    $orgId
);

$stmt->execute();

/* ===============================
   6. Safe Success Handling
================================ */
if ($stmt->errno) {
    respond('error', 'Failed to update profile');
}

$stmt->close();

respond('success', 'Profile updated successfully');
