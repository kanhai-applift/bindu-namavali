<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
check_login();

$id = intval($_GET['id']);
$result = $mysqli->query("SELECT * FROM shasan_nirnay WHERE id=$id");
$row = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kr_no      = $_POST['kr_no'];
    $amal_tarik = $_POST['amal_tarik'];
    $gr_no      = $_POST['gr_no'];
    $vishay     = $_POST['vishay'];
    $pdf_file   = $row['pdf_file'];

    // File Upload (replace old)
    if (!empty($_FILES['pdf_file']['name'])) {
        $targetDir = "../uploads/gr_pdfs/";
        $fileName = time() . "_" . basename($_FILES['pdf_file']['name']);
        $targetFile = $targetDir . $fileName;
        if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $targetFile)) {
            $pdf_file = $fileName;
        }
    }

    $stmt = $mysqli->prepare("UPDATE shasan_nirnay SET kr_no=?, amal_tarik=?, gr_no=?, vishay=?, pdf_file=? WHERE id=?");
    $stmt->bind_param("sssssi", $kr_no, $amal_tarik, $gr_no, $vishay, $pdf_file, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: shashan_nirnay.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="mr">
<head>
<meta charset="UTF-8">
<title>शासन निर्णय Edit</title>
</head>
<body>
<h2>✏️ Edit शासन निर्णय</h2>
<form method="post" enctype="multipart/form-data">
    क्र. क्र.: <input type="text" name="kr_no" value="<?= htmlspecialchars($row['kr_no']) ?>" required><br><br>
    अंमलबजावणीची तारीख: <input type="date" name="amal_tarik" value="<?= htmlspecialchars($row['amal_tarik']) ?>" required><br><br>
    शासन निर्णय / परिपत्रक: <input type="text" name="gr_no" value="<?= htmlspecialchars($row['gr_no']) ?>" required><br><br>
    विषय: <textarea name="vishay" required><?= htmlspecialchars($row['vishay']) ?></textarea><br><br>
    सध्याचा PDF: 
    <?php if ($row['pdf_file']): ?>
        <a href="../uploads/gr_pdfs/<?= $row['pdf_file'] ?>" target="_blank">📄 पहा</a>
    <?php else: ?>
        नाही
    <?php endif; ?>
    <br><br>
    नवीन PDF Upload: <input type="file" name="pdf_file" accept="application/pdf"><br><br>
    <button type="submit">Update</button>
</form>
</body>
</html>
