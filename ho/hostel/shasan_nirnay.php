<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/ho/hostel/includes/config.php');
include('includes/checklogin.php');
check_login();

$result = $mysqli->query("SELECT * FROM shasan_nirnay ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="mr">
<head>
    <meta charset="UTF-8">
    <title>शासन निर्णय</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>
<body>

<h2>📑 शासन निर्णय यादी</h2>

<table id="grTable" class="display">
    <thead>
        <tr>
            <th>क्र. क्र.</th>
            <th>अंमलबजावणीची तारीख</th>
            <th>सरकारी ठराव/परिपत्रक</th>
            <th>विषय</th>
            <th>PDF</th>
        </tr>
    </thead>
    <tbody>
    <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['kr_no']) ?></td>
            <td><?= htmlspecialchars($row['amal_tarik']) ?></td>
            <td><?= htmlspecialchars($row['gr_no']) ?></td>
            <td><?= htmlspecialchars($row['vishay']) ?></td>
            <td>
                <?php if($row['pdf_file']): ?>
                    <a href="../uploads/gr_pdfs/<?= $row['pdf_file'] ?>" target="_blank">📄 पहा</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(() => $('#grTable').DataTable());
</script>
</body>
</html>
