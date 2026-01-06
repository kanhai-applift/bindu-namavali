<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/ho/hostel/includes/config.php');
include('includes/checklogin.php');
check_login();

$user_id = $_SESSION['id']; 

if (isset($_POST['submit'])) {
    $post_text = trim($_POST['post_text']);
    if (!empty($post_text)) {
        // Redirect to notebook page with post
        header("Location: notebook.php?post=" . urlencode($post_text));
        exit();
    }
}

// 🔎 Show user के लिए available posts
$posts = [];
$result = $mysqli->query("SHOW TABLES LIKE 'notebook_".$user_id."_%'");
if ($result) {
    while ($row = $result->fetch_array()) {
        // full table name से postname निकालें
        $table_name = $row[0];
        $post_name = str_replace("notebook_".$user_id."_", "", $table_name);
        $posts[] = $post_name;
    }
}
?>
<!DOCTYPE html>
<html lang="mr">
<head>
<meta charset="UTF-8">
<title>Post Entry</title>
<style>
    body { font-family: Arial; margin: 20px; }
    input, select { padding: 6px; font-size: 14px; width: 300px; margin-bottom: 10px; }
    .submit-btn { background: #ff9933; color: #fff; padding: 8px 20px; border: none; cursor: pointer; }
    .home-btn { background: #0066cc; color: #fff; padding: 8px 16px; border: none; cursor: pointer; text-decoration: none; margin-bottom: 20px; display: inline-block; border-radius: 4px; }
    .home-btn:hover { background: #004d99; }
    ul { margin-top: 10px; }
    li { margin: 5px 0; }
    a.post-link { color: #0066cc; text-decoration: none; }
    a.post-link:hover { text-decoration: underline; }
</style>
</head>
<body>
    <a href="dashboard.php" class="home-btn">🏠 Home</a>
    
    <h2>📌 नवीन पद / Post निवडा</h2>
    <form method="post">
        <input type="text" name="post_text" placeholder="नवीन Post लिहा">
        <button type="submit" name="submit" class="submit-btn">Go</button>
    </form>

    <?php if (!empty($posts)) { ?>
        <h3>📝 तुमचे आधीचे Posts:</h3>
        <ul>
            <?php foreach ($posts as $p) { ?>
                <li>
                    <a class="post-link" href="notebook.php?post=<?php echo urlencode($p); ?>">
                        <?php echo htmlspecialchars($p); ?>
                    </a>
                </li>
            <?php } ?>
        </ul>
    <?php } else { ?>
        <p>अजून कोणतेही posts तयार केलेले नाहीत.</p>
    <?php } ?>
</body>
</html>
