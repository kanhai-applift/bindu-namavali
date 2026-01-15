<?php
require_once "api-helper.php";

/* ===============================
   3. Input Normalization
================================ */
$registrationNo = trim($_POST['registration_no'] ?? '');
$officeName     = trim($_POST['office_name'] ?? '');
$headName       = trim($_POST['head_name'] ?? '');
$districtId     = (int)trim($_POST['district_id'] ?? '');
$contactNo      = trim($_POST['contact_no'] ?? '');
$email          = trim($_POST['email'] ?? '');
$password       = trim($_POST['password']) ?? '';
$confirm        = trim($_POST['confirm_password']) ?? '';

/* ===============================
   4. Required Fields Validation
================================ */
if (
    $registrationNo === '' ||
    $officeName === '' ||
    $headName === '' ||
    $districtId === '' ||
    $contactNo === '' ||
    $email === '' ||
    $password === ''
) {
    respond('error', 'All fields are required');
}

/* ===============================
   5. Length & Format Validation
================================ */
if (
    mb_strlen($registrationNo) > 50 ||
    mb_strlen($officeName) > 150 ||
    mb_strlen($headName) > 150
) {
    respond('error', 'Invalid input length');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond('error', 'Invalid email address');
}

if (!preg_match('/^[0-9]{10}$/', $contactNo)) {
    respond('error', 'Invalid contact number');
}

if (mb_strlen($password) < 8) {
    respond('error', 'Password must be at least 8 characters long');
}

if ($password !== $confirm) {
    respond('error', 'Passwords do not match');
}

/* ===============================
   6. Duplicate Check (Safe)
================================ */
$stmt = $mysqli->prepare(
    "SELECT id FROM users WHERE email = ? OR registration_no = ? LIMIT 1"
);
$stmt->bind_param("ss", $email, $registrationNo);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    respond('error', 'Email or Registration Number already exists');
}
$stmt->close();

/* ===============================
   7. Password Hashing (Strong)
================================ */
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

if ($passwordHash === false) {
    respond('error', 'Password processing failed');
}

/* ===============================
   8. Insert User (Prepared)
================================ */
$stmt = $mysqli->prepare(
    "INSERT INTO users
     (registration_no, office_name, head_name, district_id, contact_no, email, password_hash, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, 'inactive')"
);

$stmt->bind_param(
    "sssisss",
    $registrationNo,
    $officeName,
    $headName,
    $districtId,
    $contactNo,
    $email,
    $passwordHash
);

$stmt->execute();

if ($stmt->errno) {
    respond('error', 'Registration failed');
}

$stmt->close();

/* ===============================
   9. Success Response
================================ */
respond('success', 'Registration successful.');
