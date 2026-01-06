<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
check_login();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // जुनी PDF delete करणे (optional)
    $res = $mysqli->query("SELECT pdf_file FROM shasan_nirnay WHERE id=$id");
    $row = $res->fetch_assoc();
    if ($row['pdf_file'] && file_exists("../uploads/gr_pdfs/" . $row['pdf_file'])) {
        unlink("../uploads/gr_pdfs/" . $row['pdf_file']);
    }

    $stmt = $mysqli->prepare("DELETE FROM shasan_nirnay WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

header("Location: shashan_nirnay.php");
exit();
