<?php
require_once 'api-helper.php';

/* 3️⃣ Authorization */
if (!in_array($_SESSION['role'], ['admin', 'superadmin'], true)) {
  http_response_code(403);
  respond('error', 'Unauthorized');
}

/* 4️⃣ Required fields check */
$required = [
  'designation_hash',
  'bindu_no',
  'bindu_category',
];

foreach ($required as $field) {
  if (empty($_POST[$field])) {
    respond('error', "Missing field: {$field}");
  }
}

/* 5️⃣ Session & org validation */
$orgId = (int) $_SESSION['user_id'];
if ($orgId <= 0) {
  respond('error', 'Invalid session');
}

/* 6️⃣ Decode and validate designation */
$hash = trim($_POST['designation_hash']);
$decoded = $hashids->decode($hash);

if (empty($decoded)) {
  respond('error', 'Invalid designation');
}

$designationId = (int) $decoded[0];

/* 7️⃣ Verify designation belongs to org (IDOR protection) */
$chk = $mysqli->prepare(
  "SELECT id FROM designations WHERE id = ? AND organization_id = ?"
);
$chk->bind_param('ii', $designationId, $orgId);
$chk->execute();
$chk->store_result();

if ($chk->num_rows === 0) {
  respond('error', 'Unauthorized designation access');
}
$chk->close();

/* 8️⃣ Sanitize & validate inputs */
$binduNo        = (int) $_POST['bindu_no'];
$isVacant      = isset($_POST['is_vacant']) ? (int)$_POST['is_vacant'] : 0;
$working       = isset($_POST['working']) ? 1 : 0;

$binduCategory = strip_tags(trim($_POST['bindu_category']));
$empName       = strip_tags(trim($_POST['employee_name']));
$empCaste      = strip_tags(trim($_POST['employee_caste'] ?? ''));
$empCategory   = strip_tags(trim($_POST['employee_category']));
$valCommittee  = strip_tags(trim($_POST['validation_committee_name'] ?? ''));
$dateAppointment = !empty($_POST['date_of_appointment'])
  ? convertToMySQLDate($_POST['date_of_appointment'])
  : null;

$dateBirth = !empty($_POST['date_of_birth'])
  ? convertToMySQLDate($_POST['date_of_birth'])
  : null;

$dateRetirement = !empty($_POST['date_of_retirement'])
  ? convertToMySQLDate($_POST['date_of_retirement'])
  : null;

$casteCertNo = isset($_POST['caste_certificate_no'])
  ? strip_tags(trim($_POST['caste_certificate_no']))
  : null;

$casteCertAuthority = isset($_POST['caste_cert_authority'])
  ? strip_tags(trim($_POST['caste_cert_authority']))
  : null;

$casteValidityNo = isset($_POST['caste_validity_certificate_no'])
  ? strip_tags(trim($_POST['caste_validity_certificate_no']))
  : null;

/* Optional: date validation */
foreach ([$dateAppointment, $dateBirth, $dateRetirement] as $d) {
  if ($d !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
    respond('error', 'Invalid date format');
  }
}

$remarks = trim($_POST['remarks'] ?? '');
if (mb_strlen($remarks) > EMPLOYEE_REMARKS_LENGTH) {
    respond('error', 'Remarks too long');
}

$pdfPath = null;

/* 9️⃣ Secure PDF upload */
if (!empty($_FILES['pdf']['name'])) {

  if ($_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
    respond('error', 'File upload error');
  }

  if ($_FILES['pdf']['size'] > (EMPLOYEE_PDF_SIZE * 1024 * 1024)) {
    respond('error', 'PDF size exceeds '.EMPLOYEE_PDF_SIZE.'Mb');
  }

  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime  = $finfo->file($_FILES['pdf']['tmp_name']);

  if ($mime !== 'application/pdf') {
    respond('error', 'Invalid PDF file');
  }

  $filename = bin2hex(random_bytes(16)) . '.pdf';
  $uploadDir = __DIR__ . '/../uploads/' . $designationId;

  if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
      // die("Failed to create folder: " . $uploadDir);
      respond('error', 'Failed to create upload folder');
    }
  }

  $uploadDir = realpath($uploadDir);

  // respond('error', 'Debug: ('.$uploadDir.') '.__DIR__ . '/../uploads/'.$designationId);

  if ($uploadDir === false) {
    respond('error', 'Upload directory error' . $uploadDir);
  }

  $target = $uploadDir . DIRECTORY_SEPARATOR . $filename;

  if (!move_uploaded_file($_FILES['pdf']['tmp_name'], $target)) {
    respond('error', 'Failed to store PDF');
  }

  $pdfPath = 'uploads/' . $designationId . '/' . $filename;
}

/* 🔟 Transaction-safe insert */
$mysqli->begin_transaction();

try {
  $sql = "INSERT INTO employees (
        organization_id, designation_id,
        bindu_no, bindu_category,
        employee_name, employee_caste, employee_category,
        date_of_appointment, date_of_birth, date_of_retirement,
        caste_certificate_no, caste_cert_authority, caste_validity_certificate_no,
        validation_committee_name,
        working, remarks, pdf
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

  $stmt = $mysqli->prepare($sql);

  $stmt->bind_param(
    "iiisssssssssssiss",
    $orgId,
    $designationId,
    $binduNo,
    $binduCategory,
    $empName,
    $empCaste,
    $empCategory,
    $dateAppointment,
    $dateBirth,
    $dateRetirement,
    $casteCertNo,
    $casteCertAuthority,
    $casteValidityNo,
    $valCommittee,
    $working,
    $remarks,
    $pdfPath
  );


  $stmt->execute();

  if ($stmt->affected_rows === 0) {
    throw new Exception('Insert failed');
  }

  $mysqli->commit();
  respond('success', 'Entry saved successfully');
} catch (Throwable $e) {

  $mysqli->rollback();

  if ($pdfPath && file_exists($target)) {
    unlink($target); // cleanup orphan file
  }

  error_log($e->getMessage());
  respond('error', 'Failed to save Entry');
}
