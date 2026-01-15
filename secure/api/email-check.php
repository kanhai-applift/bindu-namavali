<?php
require_once 'api-helper.php';

/* 3️⃣ Authorization */
if (!in_array($_SESSION['role'], ['superadmin'], true)) {
  http_response_code(403);
  respond('error', 'Unauthorized');
}

/* ===============================
   3. Rate Limiting (Recommended)
================================ */
/*
if (!rateLimit('email_check', 10, 60)) {
    http_response_code(429);
    echo json_encode(['status' => 'error']);
    exit;
}
*/

/* ===============================
   4. Input Normalization
================================ */

$email = strtolower(trim($_POST['email'] ?? ''));

/* Basic validation */
if ($email === '' || strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'invalid']);
    exit;
}

/* ===============================
   5. Safe Database Lookup
================================ */

$stmt = $mysqli->prepare(
    "SELECT 1 FROM users WHERE email = ? LIMIT 1"
);

$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();

/* ===============================
   6. Response Normalization
   (Prevents user enumeration)
================================ */

$status = ($stmt->num_rows > 0) ? 'unavailable' : 'available';

$stmt->close();

echo json_encode([
    'status' => $status
]);
