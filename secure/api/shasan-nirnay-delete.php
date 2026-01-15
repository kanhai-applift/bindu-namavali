<?php
require_once 'api-helper.php';

/* 3️⃣ Authorization check */
if (!in_array($_SESSION['role'], ['superadmin'], true)) {
  http_response_code(403);
  respond('error', 'Unauthorized');
}

/* 4️⃣ Required field check */
if (empty($_POST['id']) || !is_string($_POST['id'])) {
  respond('error', 'Missing Shasan Nirnay ID');
}

/* 5️⃣ Decode Hashid → numeric ID */
$hash = trim($_POST['id']);
$decoded = $hashids->decode($hash);

if (empty($decoded)) {
  respond('error', 'Invalid Shasan Nirnay');
}

$shasanNirnayID = (int) $decoded[0];

if ($shasanNirnayID <= 0) {
  respond('error', 'Invalid request');
}

try {

  /* ===============================
   3. Fetch Existing Record
================================ */

  $stmt = $mysqli->prepare(
    "SELECT pdf_file FROM shasan_nirnay WHERE id = ?"
  );
  $stmt->bind_param('i', $shasanNirnayID);
  $stmt->execute();
  $stmt->bind_result($pdfPath);
  $stmt->fetch();
  $stmt->close();

  /* 6️⃣ Perform scoped delete (numeric PK) */
  $stmt = $mysqli->prepare("
        DELETE FROM shasan_nirnay
        WHERE id = ?
    ");

  if (!$stmt) {
    throw new Exception($mysqli->error);
  }

  $stmt->bind_param('i', $shasanNirnayID);

  if (!$stmt->execute()) {
    throw new Exception($stmt->error, $mysqli->errno);
  }

  /* 7️⃣ Detect tampering / non-existent record */
  if ($stmt->affected_rows === 0) {
    $stmt->close();
    respond('error', 'Delete failed or unauthorized');
  } else {
    /* Delete old PDF only AFTER successful DB update */
    if (!empty($pdfPath)) {
      $oldPath = realpath(__DIR__ . '/../' . $pdfPath);
      $baseDir = realpath(__DIR__ . '/../uploads/shasan_nirnay/');

      if ($oldPath && $baseDir && strpos($oldPath, $baseDir) === 0) {
        @unlink($oldPath);
      }
    }
  }

  $stmt->close();

  respond('success', 'Shasan Nirnay deleted');
} catch (Exception $e) {

  /* 8️⃣ Log real error internally */
  error_log($e->getMessage());

  /* FK constraint violation */
  if (in_array($e->getCode(), [1451, 1452], true)) {
    respond('error', 'Shasan Nirnay is in use and cannot be deleted');
  }

  respond('error', 'Server error');
}
