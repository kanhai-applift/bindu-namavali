<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/ho/hostel/includes/config.php');
include('includes/checklogin.php');
check_login();

$user_id = $_SESSION['id']; 
$table_name = "notebook_" . $user_id;

// Step 1: Create table if not exists
$sql_create = "
CREATE TABLE IF NOT EXISTS $table_name (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bindu_kramaank VARCHAR(50),
    bindu_namavli VARCHAR(255),
    karmachari_naam VARCHAR(100),
    karmachari_jat VARCHAR(100),
    pad_niyukt_dinank DATE,
    janma_tarik DATE,
    sevaniroti_dinank DATE,
    jat_pramanpatra VARCHAR(255),
    jat_pramanpatra_pradikar VARCHAR(255),
    jat_vaidhta_pramanpatra VARCHAR(255),
    jat_vaidhta_samiti VARCHAR(255),
    shera TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$mysqli->query($sql_create);

// Step 2: Insert form data
if (isset($_POST['submit'])) {
    $sql_insert = "INSERT INTO $table_name 
    (bindu_kramaank, bindu_namavli, karmachari_naam, karmachari_jat, pad_niyukt_dinank, 
     janma_tarik, sevaniroti_dinank, jat_pramanpatra, jat_pramanpatra_pradikar, 
     jat_vaidhta_pramanpatra, jat_vaidhta_samiti, shera) 
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";

    $stmt = $mysqli->prepare($sql_insert);
    $stmt->bind_param("ssssssssssss", 
        $_POST['bindu_kramaank'], $_POST['bindu_namavli'], $_POST['karmachari_naam'], $_POST['karmachari_jat'],
        $_POST['pad_niyukt_dinank'], $_POST['janma_tarik'], $_POST['sevaniroti_dinank'],
        $_POST['jat_pramanpatra'], $_POST['jat_pramanpatra_pradikar'], $_POST['jat_vaidhta_pramanpatra'],
        $_POST['jat_vaidhta_samiti'], $_POST['shera']
    );
    $stmt->execute();
}

// Step 3: Fetch all records for display
$result = $mysqli->query("SELECT * FROM $table_name ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="mr">
<head>
    <meta charset="UTF-8">
    <title>Notebook</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
        table, th, td { border: 1px solid #444; }
        th, td { padding: 6px; text-align: center; }
        th { background: #ff9933; color: white; }
        input, textarea { width: 100%; box-sizing: border-box; padding: 5px; font-size: 13px; }
        .submit-btn { background: #ff9933; color: white; padding: 6px 12px; border: none; cursor: pointer; }
    </style>
</head>
<body>

    <h2>📓 वापरकर्ता Notebook</h2>

    <!-- Display Saved Data with Form Row -->
    <form method="post">
    <table>
        <tr>
            <th>ID</th>
            <th>बिंदू क्रामांक</th>
            <th>बिंदू नामावली</th>
            <th>कर्मचार्यांचे नाव</th>
            <th>कर्मचारी जात</th>
            <th>पद नियुक्त दिनांक</th>
            <th>जन्मतारीख</th>
            <th>सेवानिरुती दिनांक</th>
            <th>जात प्रमाणपत्र</th>
            <th>प्रदिकऱ्याचे पदनाव</th>
            <th>वैधता प्रमानपत्र</th>
            <th>वैधता समिती</th>
            <th>शेरा</th>
            <th>Action</th>
        </tr>

        <!-- Form Input Row -->
        <tr>
            <td>—</td>
            <td><input type="text" name="bindu_kramaank"></td>
            <td><input type="text" name="bindu_namavli"></td>
            <td><input type="text" name="karmachari_naam"></td>
            <td><input type="text" name="karmachari_jat"></td>
            <td><input type="date" name="pad_niyukt_dinank"></td>
            <td><input type="date" name="janma_tarik"></td>
            <td><input type="date" name="sevaniroti_dinank"></td>
            <td><input type="text" name="jat_pramanpatra"></td>
            <td><input type="text" name="jat_pramanpatra_pradikar"></td>
            <td><input type="text" name="jat_vaidhta_pramanpatra"></td>
            <td><input type="text" name="jat_vaidhta_samiti"></td>
            <td><textarea name="shera" rows="1"></textarea></td>
            <td><button type="submit" name="submit" class="submit-btn">Save</button></td>
        </tr>

        <!-- Display Data -->
        <?php while($row = $result->fetch_assoc()): ?>
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
            <td>✔</td>
        </tr>
        <?php endwhile; ?>
    </table>
    </form>

</body>
</html>
