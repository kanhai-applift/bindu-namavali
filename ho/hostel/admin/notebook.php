<?php
session_start();
include('../includes/config.php');
include('../includes/checklogin.php');
check_login();

$uid = intval($_GET['uid']);
$post = $_GET['post'];
$table_name = "notebook_" . $uid . "_" . $mysqli->real_escape_string($post);

$entries = $mysqli->query("SELECT * FROM `$table_name` ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="mr">
<head>
<meta charset="UTF-8">
<title>Notebook</title>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>
<body>
<h2>📑 Notebook: <?= htmlspecialchars($post) ?></h2>
<?php if ($entries && $entries->num_rows > 0): ?>
<table id="notebookTable" class="display">
    <thead>
        <tr>
            <th>ID</th>
            <th>बिंदू क्रामांक</th>
            <th>बिंदू नामावली</th>
            <th>कर्मचार्यांचे नाव</th>
            <th>कर्मचारी जात</th>
            <th>पद नियुक्त दिनांक</th>
            <th>जन्मतारीख</th>
            <th>सेवानिवृत्ती दिनांक</th>
            <th>जात प्रमाणपत्र</th>
            <th>प्राधिकाऱ्याचे पदनाव</th>
            <th>वैधता प्रमाणपत्र</th>
            <th>वैधता समिती</th>
            <th>शेरा</th>
            <th>कार्यरत</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $entries->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['bindu_kramaank'] ?></td>
            <td><?= $row['bindu_namavli'] ?></td>
            <td><?= $row['karmachari_naam'] ?></td>
            <td><?= $row['karmachari_jat'] ?></td>
            <td><?= $row['pad_niyukt_dinank'] ?></td>
            <td><?= $row['janma_tarik'] ?></td>
            <td><?= $row['sevaniroti_dinank'] ?></td>
            <td><?= $row['jat_pramanpatra'] ?></td>
            <td><?= $row['jat_pramanpatra_pradikar'] ?></td>
            <td><?= $row['jat_vaidhta_pramanpatra'] ?></td>
            <td><?= $row['jat_vaidhta_samiti'] ?></td>
            <td><?= $row['shera'] ?></td>
            <td><?= ($row['karyarat'] ? "✅ होय" : "❌ नाही") ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<?php else: ?>
<p>⚠️ या Notebook मध्ये कोणतेही Entries नाहीत.</p>
<?php endif; ?>
<p><a href="user_posts.php?uid=<?= $uid ?>">⬅️ Back to Posts</a></p>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(() => $('#notebookTable').DataTable());
</script>
</body>
</html>
