<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
check_login();

$user_id = $_SESSION['id'];

// pattern: notebook_userid_*
$pattern = "notebook_" . $user_id . "_%";

$stmt = $conn->prepare("SHOW TABLES LIKE ?");
$stmt->bind_param("s", $pattern);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="mr">
<head>
  <meta charset="UTF-8">
  <title>माझ्या तयार केलेल्या टेबल्स</title>
  <style>
    table { border-collapse: collapse; width: 60%; margin-top: 20px; font-size: 15px; }
    th, td { border: 1px solid #333; padding: 8px; text-align: left; }
    th { background: #ffcc80; }
    tr:nth-child(even) { background: #f9f9f9; }
    .btn-home {
        padding: 6px 15px;
        background-color: #28a745;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        margin-bottom: 15px;
    }
    .btn-home:hover { background-color: #218838; }
  </style>
</head>
<body>
  <h2>🗂 माझ्या तयार केलेल्या टेबल्स</h2>

  <!-- Home Button -->
  <button class="btn-home" onclick="window.location.href='dashboard.php'">🏠 Home</button>

  <table>
    <tr>
      <th>Sr.No</th>
      <th>Table Name</th>
    </tr>
    <?php
    if ($result->num_rows > 0) {
        $i = 1;
        while ($row = $result->fetch_array()) {
            echo "<tr>
                    <td>".$i++."</td>
                    <td>".$row[0]."</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='2'>⚠️ अजून कोणतेही टेबल तयार केलेले नाही.</td></tr>";
    }
    ?>
  </table>
</body>
</html>
