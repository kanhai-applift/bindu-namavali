<?php
require_once 'api-helper.php';

/* 3️⃣ Authorization check */
if (!in_array($_SESSION['role'], ['admin', 'superadmin'], true)) {
  http_response_code(403);
  respond('error', 'Unauthorized');
}

/* 4️⃣ Required fields check */
if (!isset($_POST['designation_name'], $_POST['description'])) {
  respond('error', 'Missing fields');
}

/* 5️⃣ Input sanitization */
$orgId = (int) $_SESSION['user_id'];
$name  = strip_tags(trim($_POST['designation_name']));
$desc  = strip_tags(trim($_POST['description']));

/* 6️⃣ Input validation */
if ($orgId <= 0) {
  respond('error', 'Invalid session');
}

if ($name === '' || mb_strlen($name) > 150) {
  respond('error', 'Invalid designation name');
}

if (mb_strlen($desc) > 500) {
  respond('error', 'Description too long');
}

/* 7️⃣ Database insert */
try {

  $stmt = $mysqli->prepare("
      INSERT INTO designations (
          organization_id, designation_name, description
      )
      VALUES (?, ?, ?)
  ");

  if (!$stmt) {
    throw new Exception($mysqli->error);
  }

  $stmt->bind_param(
    'iss',        // i = int, s = string, s = string
    $orgId,
    $name,
    $desc
  );

  if (!$stmt->execute()) {
    throw new Exception($stmt->error);
  }

  $stmt->close();

  respond('success', 'Designation added');
} catch (Exception $e) {

  /* 8️⃣ Log real error internally */
  error_log($e->getMessage());

  /* 9️⃣ Safe client response */
  respond('error', 'Designation already exists or invalid data');
}
