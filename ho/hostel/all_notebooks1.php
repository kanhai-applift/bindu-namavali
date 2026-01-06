<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/ho/hostel/includes/config.php');
include('../includes/checklogin.php');
check_login();

$users = $mysqli->query("SELECT id, firstName, lastName, email FROM userregistration");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Users & Posts Notebooks</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { color: #333; }
        h3 { color: #444; margin-top: 30px; }
        h4 { color: #006699; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid #666; }
        th, td { padding: 8px; text-align: center; }
        th { background: #006699; color: white; }
        .no-data { color: gray; }
    </style>
</head>
<body>
    <h2>📑 All Users' Notebook Entries (Per Post)</h2>

    <?php while ($user = $users->fetch_assoc()): ?>
        <h3>
            👤 <?= htmlspecialchars($user['firstName'].' '.$user['lastName']) ?>
            (<?= htmlspecialchars($user['email']) ?>)
        </h3>

        <?php
        // Find all notebook tables for this user (pattern match)
        $prefix = "notebook_" . $user['id'] . "_";
        $tables = $mysqli->query("SHOW TABLES LIKE '{$prefix}%'");

        if ($tables->num_rows > 0) {
            while ($tbl = $tables->fetch_array()) {
                $table_name = $tbl[0];
                $post_name = str_replace($prefix, '', $table_name); // extract post

                echo "<h4>📌 Post: " . htmlspecialchars($post_name) . "</h4>";

                $entries = $mysqli->query("SELECT * FROM `$table_name` ORDER BY id DESC");
                if ($entries->num_rows > 0) {
                    echo "<table>
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
                            </tr>";
                    while ($row = $entries->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['bindu_kramaank']}</td>
                                <td>{$row['bindu_namavli']}</td>
                                <td>{$row['karmachari_naam']}</td>
                                <td>{$row['karmachari_jat']}</td>
                                <td>{$row['pad_niyukt_dinank']}</td>
                                <td>{$row['janma_tarik']}</td>
                                <td>{$row['sevaniroti_dinank']}</td>
                                <td>{$row['jat_pramanpatra']}</td>
                                <td>{$row['jat_pramanpatra_pradikar']}</td>
                                <td>{$row['jat_vaidhta_pramanpatra']}</td>
                                <td>{$row['jat_vaidhta_samiti']}</td>
                                <td>{$row['shera']}</td>
                              </tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p class='no-data'>No entries found in this notebook.</p>";
                }
            }
        } else {
            echo "<p class='no-data'>❌ No notebooks found for this user.</p>";
        }
        ?>
        <hr>
    <?php endwhile; ?>

</body>
</html>
