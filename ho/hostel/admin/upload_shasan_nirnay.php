<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
check_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kr_no      = trim($_POST['kr_no']);
    $amal_tarik = trim($_POST['amal_tarik']);
    $gr_no      = trim($_POST['gr_no']);
    $vishay     = trim($_POST['vishay']);
    $pdf_file   = null;

    // File Upload
    if (!empty($_FILES['pdf_file']['name'])) {
        $targetDir = "../uploads/gr_pdfs/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = time() . "_" . basename($_FILES['pdf_file']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $targetFile)) {
            $pdf_file = $fileName;
        }
    }

    // Insert Query
    $stmt = $mysqli->prepare("INSERT INTO shasan_nirnay (kr_no, amal_tarik, gr_no, vishay, pdf_file) VALUES (?, ?, ?, ?, ?)");
    if ($stmt === false) {
        die("MySQL Error: " . $mysqli->error);
    }
    $stmt->bind_param("sssss", $kr_no, $amal_tarik, $gr_no, $vishay, $pdf_file);

    if ($stmt->execute()) {
        echo "<script>alert('✅ शासन निर्णय यशस्वीरित्या जतन झाला'); window.location='shashan_nirnay.php';</script>";
        exit();
    } else {
        echo "<script>alert('❌ Error: डेटा जतन झाला नाही');</script>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="mr">
<head>
<meta charset="UTF-8">
<title>नवीन शासन निर्णय जोडा</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
<div class="container">
    <h2>➕ नवीन शासन निर्णय जोडा</h2>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">क्र. क्र.</label>
            <input type="text" name="kr_no" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">अंमलबजावणीची तारीख</label>
            <input type="date" name="amal_tarik" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">शासन निर्णय / परिपत्रक</label>
            <input type="text" name="gr_no" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">विषय</label>
            <textarea name="vishay" class="form-control" required></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">PDF Upload</label>
            <input type="file" name="pdf_file" accept="application/pdf" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">💾 Save</button>
    </form>
</div>
</body>
</html>
