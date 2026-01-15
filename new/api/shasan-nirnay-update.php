<?php
require_once 'api-helper.php';

/* Authorization check */
if (!in_array($_SESSION['role'], ['superadmin'], true)) {
    http_response_code(403);
    respond('error', 'Unauthorized');
}

/* ===============================
   2. Input Normalization & Validation
================================ */

$hash = trim($_POST['shasan_nirnay_hashed']);
/* 2️⃣ Decode Hashid → numeric ID */
$decoded = $hashids->decode($hash);

if (empty($decoded)) {
    respond('error','Invalid designation');
}

$userId    = (int) $_SESSION['user_id'];
$id        = (int) $decoded[0];
$krNo      = trim($_POST['kr_no'] ?? '');
$amalTarik = trim($_POST['amal_tarik'] ?? '');
$grNo      = trim($_POST['gr_no'] ?? '');
$vishay    = trim($_POST['vishay'] ?? '');

if ($id <= 0 || $krNo === '' || $amalTarik === '' || $grNo === '' || $vishay === '') {
    respond('error', 'Invalid input');
}

/* Length validation (VAPT critical) */
if (
    mb_strlen($krNo) > 50 ||
    mb_strlen($grNo) > 150 ||
    mb_strlen($vishay) > SN_VISHANY_LENGTH
) {
    respond('error', 'Input length exceeded');
}

/* Date validation */
$date = DateTime::createFromFormat('Y-m-d', $amalTarik);
if (!$date || $date->format('Y-m-d') !== $amalTarik) {
    respond('error', 'Invalid date format');
}

/* ===============================
   3. Fetch Existing Record
================================ */

$stmt = $mysqli->prepare(
    "SELECT pdf_file FROM shasan_nirnay WHERE id = ?"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($oldPdf);
$stmt->fetch();
$stmt->close();

$pdfPath = $oldPdf;

/* ===============================
   4. Secure Optional PDF Replacement
================================ */

if (
    isset($_FILES['pdf_file']) &&
    $_FILES['pdf_file']['error'] !== UPLOAD_ERR_NO_FILE
) {

    if ($_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
        respond('error', 'Error uploading PDF');
    }

    /* MIME validation (mandatory for VAPT) */
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($_FILES['pdf_file']['tmp_name']);

    if ($mime !== 'application/pdf') {
        respond('error', 'Invalid PDF file');
    }

    /* Size limit: SN_VISHANY_PDF_SIZE MB */
    if ($_FILES['pdf_file']['size'] > SN_VISHANY_PDF_SIZE * 1024 * 1024) {
        respond('error', 'PDF file too large');
    }

    $uploadDir = __DIR__ . '/../uploads/shasan_nirnay/';

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        respond('error', 'Upload directory error');
    }

    $filename    = uniqid('sn_', true) . '.pdf';
    $destination = $uploadDir . $filename;

    if (!move_uploaded_file($_FILES['pdf_file']['tmp_name'], $destination)) {
        respond('error', 'Failed to upload PDF');
    }

    $pdfPath = 'uploads/shasan_nirnay/' . $filename;
}

/* ===============================
   5. Transactional Update
================================ */

$mysqli->begin_transaction();

try {

    $stmt = $mysqli->prepare("
        UPDATE shasan_nirnay
        SET
            kr_no = ?,
            amal_tarik = ?,
            gr_no = ?,
            vishay = ?,
            pdf_file = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        'sssssi',
        $krNo,
        $amalTarik,
        $grNo,
        $vishay,
        $pdfPath,
        $id
    );

    $stmt->execute();

    if ($stmt->affected_rows < 0) {
        throw new Exception('Update failed');
    }

    $stmt->close();

    /* Delete old PDF only AFTER successful DB update */
    if ($pdfPath !== $oldPdf && $oldPdf) {
        $oldPath = realpath(__DIR__ . '/../' . $oldPdf);
        $baseDir = realpath(__DIR__ . '/../uploads/shasan_nirnay/');

        if ($oldPath && $baseDir && strpos($oldPath, $baseDir) === 0) {
            @unlink($oldPath);
        }
    }

    $mysqli->commit();
    respond('success', 'Shasan Nirnay updated successfully');

} catch (Throwable $e) {

    $mysqli->rollback();
    error_log($e->getMessage());
    respond('error', 'Failed to update record');
}
