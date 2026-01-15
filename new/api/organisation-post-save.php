<?php
require_once 'api-helper.php';

$orgId = (int) $_SESSION['user_id'];

/* 3️⃣ Authorization check */
if (!in_array($_SESSION['role'], ['admin', 'superadmin'], true)) {
  http_response_code(403);
  respond('error', 'Unauthorized');
}


/* ===============================
  2. Decode & Validate Designation
================================ */

$designationHash = trim($_POST['designation_hash'] ?? '');

if ($designationHash === '') {
  respond('error', 'Designation missing');
}

$decoded = $hashids->decode($designationHash);

if (empty($decoded) || (int)$decoded[0] <= 0) {
  respond('error', 'Invalid designation');
}

$designationId = (int) $decoded[0];

/* ===============================
  3. Authorization Check
================================ */

$sql = "
SELECT d.id, d.designation_name
FROM designations d
INNER JOIN goshwara g
    ON g.designation_id = d.id
   AND g.is_deleted = 0
WHERE d.id = ?
  AND d.organization_id = ?
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('ii', $designationId, $orgId);
$stmt->execute();
$stmt->store_result();

$desig_id = '';
$desig_name = '';
if ($stmt->num_rows === 0) {
  respond('error', 'Designation not eligible for post registration');
}
else {
  $stmt->bind_result($desig_id, $desig_name);
  $stmt->fetch();
}
$stmt->close();

/* ===============================
  4. Secure Optional PDF Upload
================================ */

function uploadPdfOptional(string $field, string $prefix, int $pdfSize): ?string
{
  if (
    !isset($_FILES[$field]) ||
    $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE
  ) {
    return null;
  }

  if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
    respond('error', 'File upload error');
  }

  // MIME validation (VAPT critical)
  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime  = $finfo->file($_FILES[$field]['tmp_name']);

  if ($mime !== 'application/pdf') {
    respond('error', 'Invalid file type');
  }

  if ($_FILES[$field]['size'] > $pdfSize * 1024 * 1024) {
    respond('error', 'PDF exceeds size limit');
  }

  $uploadDir = __DIR__ . '/../uploads/organisation_post/';

  if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
    respond('error', 'Upload directory error');
  }

  $filename    = uniqid($prefix . '_', true) . '.pdf';
  $destination = $uploadDir . $filename;

  if (!move_uploaded_file($_FILES[$field]['tmp_name'], $destination)) {
    respond('error', 'File upload failed');
  }

  return 'uploads/organisation_post/' . $filename;
}

/* ===============================
  5. Handle Inputs
================================ */

$remarks = trim($_POST['remarks'] ?? '');

if (mb_strlen($remarks) > 2000) {
  respond('error', 'Remarks too long');
}

$servicePdf = uploadPdfOptional('service_rules_pdf', 'service', 10);
$layoutPdf  = uploadPdfOptional('layout_pdf', 'layout', 10);
$goshPdf    = uploadPdfOptional('goshwara_pdf', 'goshwara', 10);

/* ===============================
  6. Transactional Insert
================================ */

$mysqli->begin_transaction();

try {
    $inserted = false;
    $maxAttempts = 10;
    $attempts = 0;

    while (!$inserted && $attempts < $maxAttempts) {
        try {
            // 1. Generate the 9-digit unique integer
            $uniqueIntId = random_int(100000000, 999999999);

            $insertSql = "
                INSERT INTO organisations_post (
                    organization_id,
                    designation_id,
                    designation_name,
                    post_hash, -- Using the unique integer here
                    remarks,
                    service_rules_pdf,
                    layout_pdf,
                    goshwara_pdf
                ) VALUES (?,?,?,?,?,?,?,?)
            ";

            $stmt = $mysqli->prepare($insertSql);
            $stmt->bind_param(
                'iissssss',
                $orgId,
                $designationId,
                $desig_name,
                $uniqueIntId, // The 9-digit ID
                $remarks,
                $servicePdf,
                $layoutPdf,
                $goshPdf
            );

            $stmt->execute();
            $postId = $mysqli->insert_id;
            $stmt->close();
            
            $inserted = true; // Success! Exit the loop.
            
        } catch (mysqli_sql_exception $e) {
            $attempts++;
            // Check if error is 'Duplicate entry' (MySQL Error 1062)
            if ($e->getCode() === 1062 && $attempts < $maxAttempts) {
                continue; // Try again with a new random number
            }
            throw $e; // Rethrow if it's a different error or too many attempts
        }
    }

    $mysqli->commit();

    respond('success', 'Organisation post registered successfully', [
        'post_hash' => $uniqueIntId 
    ]);

} catch (Throwable $e) {
    $mysqli->rollback();
    error_log("Post Insert Error: " . $e->getMessage());
    respond('error', 'Failed to save: ' . $e->getMessage());
}
