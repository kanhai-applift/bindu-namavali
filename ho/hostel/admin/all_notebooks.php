<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/ho/hostel/includes/config.php');
include('../includes/checklogin.php');
check_login();

$users = $mysqli->query("SELECT id, firstName, lastName, email FROM userregistration");
?>
<!DOCTYPE html>
<html lang="mr">
<head>
<meta charset="UTF-8">
<title>सर्व वापरकर्ते</title>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
    .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    .home-btn {
        background: #007bff;
        color: white;
        padding: 8px 15px;
        border-radius: 5px;
        text-decoration: none;
        font-size: 14px;
    }
    .home-btn:hover {
        background: #0056b3;
    }
</style>
</head>
<body>

<div class="top-bar">
    <h2>👤 सर्व वापरकर्त्यांची यादी</h2>
    <a href="dashboard.php" class="home-btn">🏠 Home (Dashboard)</a>
</div>

<table id="userTable" class="display">
    <thead>
        <tr>
            <th>ID</th>
            <th>नाव</th>
            <th>ईमेल</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php while($u = $users->fetch_assoc()): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['firstName'].' '.$u['lastName']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><a href="user_posts.php?uid=<?= $u['id'] ?>">📂 Posts पहा</a></td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(() => $('#userTable').DataTable());
</script>
</body>
</html>
