<?php
require_once __DIR__."/api-helper.php";

// Fetch inputs
$registration_no = trim($_POST['registration_no'] ?? '');
$office_name    = trim($_POST['office_name'] ?? '');
$head_name      = trim($_POST['head_name'] ?? '');
$district       = trim($_POST['district'] ?? '');
$contact_no     = trim($_POST['contact_no'] ?? '');
$email          = trim($_POST['email'] ?? '');
$password       = $_POST['password'] ?? '';
$confirm        = $_POST['confirm_password'] ?? '';

// Server-side validation
if (
    !$registration_no || !$office_name || !$head_name ||
    !$district || !$contact_no || !$email || !$password
) {
    respond('error', 'All fields are required.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond('error', 'Invalid email address.');
}

if ($password !== $confirm) {
    respond('error', 'Passwords do not match.');
}

if (!preg_match('/^[0-9]{10}$/', $contact_no)) {
    respond('error', 'Invalid contact number.');
}

// Check duplicates
$stmt = $mysqli->prepare("SELECT id FROM users WHERE email=? OR registration_no=?");
$stmt->bind_param("ss", $email, $registration_no);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    respond('error', 'Email or Registration No already exists.');
}
$stmt->close();

// Hash password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insert
$stmt = $mysqli->prepare("
    INSERT INTO users
    (registration_no, office_name, head_name, district, contact_no, email, password_hash)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sssssss",
    $registration_no,
    $office_name,
    $head_name,
    $district,
    $contact_no,
    $email,
    $password_hash
);

if ($stmt->execute()) {
    respond('success', 'Registration successful.');
}

respond('error', 'Registration failed.');
