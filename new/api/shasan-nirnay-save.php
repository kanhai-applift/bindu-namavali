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

$krNo      = trim($_POST['kr_no'] ?? '');
$amalTarik = trim($_POST['amal_tarik'] ?? '');
$grNo      = trim($_POST['gr_no'] ?? '');
$vishay    = trim($_POST['vishay'] ?? '');

if ($krNo === '' || $amalTarik === '' || $grNo === '' || $vishay === '') {
    respond('error', 'All fields except PDF are required');
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
   3. Secure Optional PDF Upload
================================ */

$pdfPath = null;

if (
    isset($_FILES['pdf_file']) &&
    $_FILES['pdf_file']['error'] !== UPLOAD_ERR_NO_FILE
) {

    if ($_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
        respond('error', 'Error uploading PDF');
    }

    /* MIME validation (extension alone is NOT enough) */
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($_FILES['pdf_file']['tmp_name']);

    if ($mime !== 'application/pdf') {
        respond('error', 'Invalid PDF file');
    }

    /* Size limit SN_VISHANY_PDF_SIZE MB */
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
   4. Secure Insert
================================ */

try {

    $stmt = $mysqli->prepare("
        INSERT INTO shasan_nirnay
            (kr_no, amal_tarik, gr_no, vishay, pdf_file)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        'sssss',
        $krNo,
        $amalTarik,
        $grNo,
        $vishay,
        $pdfPath
    );

    $stmt->execute();

    if ($stmt->affected_rows !== 1) {
        throw new Exception('Insert failed');
    }

    respond('success', 'Shasan Nirnay added successfully');

} catch (Throwable $e) {

    error_log($e->getMessage());
    respond('error', 'Failed to save record');

} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}
