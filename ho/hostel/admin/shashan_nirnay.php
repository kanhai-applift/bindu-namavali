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
    <style>
        .btn {
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 5px;
            display: inline-block;
        }
        .edit { background: #28a745; color: #fff; }
        .delete { background: #dc3545; color: #fff; }
        .upload { background: #007bff; color: #fff; margin-bottom:10px; }
        .home { background: #6c757d; color: #fff; margin-bottom:10px; }
        .button-group { margin-bottom: 15px; }
    </style>
</head>
<body>
    <h2>📑 शासन निर्णय यादी</h2>

    <div class="button-group">
        <a href="dashboard.php" class="btn home">🏠 Home</a>
        <a href="upload_shasan_nirnay.php" class="btn upload">➕ नवीन जोडा</a>
    </div>

    <table id="grTable" class="display">
        <thead>
            <tr>
                <th>क्र. क्र.</th>
                <th>अंमलबजावणीची तारीख</th>
                <th>सरकारी ठराव/परिपत्रक</th>
                <th>विषय</th>
                <th>PDF</th>
                <th>Action</th>
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
                <td>
                    <a class="btn edit" href="edit_shasan_nirnay.php?id=<?= $row['id'] ?>">✏️ Edit</a>
                    <a class="btn delete" href="delete_shasan_nirnay.php?id=<?= $row['id'] ?>" onclick="return confirm('❗ खात्रीने delete करायचे?')">🗑 Delete</a>
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
