<?php
session_start();
include('../includes/config.php');
include('../includes/checklogin.php');
check_login();

$uid = intval($_GET['uid']);
$prefix = "notebook_" . $uid . "_";
$tables = $mysqli->query("SHOW TABLES LIKE '{$prefix}%'");
?>
<!DOCTYPE html>
<html lang="mr">
<head>
<meta charset="UTF-8">
<title>User Posts</title>
</head>
<body>
<h2>📂 वापरकर्त्याचे Posts</h2>
<?php if ($tables->num_rows > 0): ?>
    <ul>
    <?php while ($tbl = $tables->fetch_array()): 
        $table_name = $tbl[0];
        $post_name = str_replace($prefix, '', $table_name);
    ?>
        <li>
            <a href="notebook.php?uid=<?= $uid ?>&post=<?= urlencode($post_name) ?>">
                <?= htmlspecialchars($post_name) ?>
            </a>
        </li>
    <?php endwhile; ?>
    </ul>
<?php else: ?>
    <p>⚠️ या वापरकर्त्याचे कोणतेही Posts नाहीत.</p>
<?php endif; ?>
<p><a href="all_notebooks.php">⬅️ Back to Users</a></p>
</body>
</html>
