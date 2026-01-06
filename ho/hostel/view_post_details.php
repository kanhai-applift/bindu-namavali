<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/ho/hostel/includes/config.php');
include('includes/checklogin.php');
check_login();

$user_id = $_SESSION['id'];
$post_name = isset($_GET['post_name']) ? urldecode($_GET['post_name']) : '';

if (empty($post_name)) {
    header('Location: view_posts.php');
    exit();
}

// Get all data for this post
$query = $conn->query("SELECT * FROM user_posts 
                      WHERE user_id = '$user_id' AND post_name = '" . $conn->real_escape_string($post_name) . "' 
                      ORDER BY FIELD(category, 'मंजूर_पदे', 'कार्यारत_पदे', 'दिनांक', 'संभाव्य_भरवयाची_पदे', 'एकूण_भरायची_पदे', 'अतिरिक्त_पदे')");

$post_data = [];
while($row = $query->fetch_assoc()) {
    $post_data[$row['category']] = $row;
}

// Get SEBC data if exists
$sebc_query = $conn->query("SELECT * FROM sebc_data 
                           WHERE user_id = '$user_id' AND post_name = '" . $conn->real_escape_string($post_name) . "' 
                           ORDER BY created_at DESC LIMIT 1");
$sebc_data = $sebc_query->fetch_assoc();

// Get EWS data if exists
$ews_query = $conn->query("SELECT * FROM ews_data 
                          WHERE user_id = '$user_id' AND post_name = '" . $conn->real_escape_string($post_name) . "' 
                          ORDER BY created_at DESC LIMIT 1");
$ews_data = $ews_query->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="mr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($post_name); ?> - पद तपशील</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .back-btn {
            background: #3498db;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            display: inline-block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #f2a65a;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .category-cell {
            background-color: #f9e7c4;
            font-weight: bold;
        }
        .total-cell {
            background-color: #e7f3ff;
            font-weight: bold;
        }
        .section-title {
            background: #2c3e50;
            color: white;
            padding: 10px;
            margin: 30px 0 10px 0;
            border-radius: 5px;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="view_posts.php" class="back-btn">⬅️ सर्व पदांकडे परत जा</a>
        <a href="user_post.php?post_name=<?php echo urlencode($post_name); ?>" class="back-btn" style="background: #f39c12;">✏️ हे पद सुधारा</a>
        
        <h1>📋 पद: <?php echo htmlspecialchars($post_name); ?></h1>

        <?php if (!empty($post_data)): ?>
            <!-- Main Posts Table -->
            <div class="section-title">मुख्य पद माहिती</div>
            <table>
                <tr>
                    <th>प्रकार / Category</th>
                    <th>अनुसूचित जाती</th>
                    <th>अनुसूचित जमाती</th>
                    <th>विमुक्त जमाती (अ)</th>
                    <th>भटक्या जमाती (ब)</th>
                    <th>भटक्या जमाती (क)</th>
                    <th>भटक्या जमाती (ड)</th>
                    <th>विशेष मागास प्रवर्ग</th>
                    <th>इतर मागास प्रवर्ग</th>
                    <th>सामाजिक आणि शैक्षणिक मागास वर्ग</th>
                    <th>आर्थिक दृष्ट्या दुर्बल घटक</th>
                    <th>अराखीव</th>
                    <th>Total</th>
                </tr>
                
                <?php
                $categories = ['मंजूर_पदे', 'कार्यारत_पदे', 'दिनांक', 'संभाव्य_भरवयाची_पदे', 'एकूण_भरायची_पदे', 'अतिरिक्त_पदे'];
                foreach ($categories as $category): 
                    if (isset($post_data[$category])): 
                        $data = $post_data[$category];
                ?>
                <tr>
                    <td class="category-cell"><?php echo $category; ?></td>
                    <?php for ($i = 0; $i < 11; $i++): ?>
                        <td><?php echo $data['col' . $i]; ?></td>
                    <?php endfor; ?>
                    <td class="total-cell"><?php echo $data['total']; ?></td>
                </tr>
                <?php endif; endforeach; ?>
            </table>

            <!-- SEBC Table -->
            <?php if ($sebc_data): ?>
            <div class="section-title">एसईबीसी भारती करिता गणना</div>
            <table>
                <tr>
                    <th>पाहिल्या भरती वर्षात भरवयाची पदे</th>
                    <th>एसईबीसी भारती करीता १०% नुसार येणारी पदे</th>
                    <th>भरती वर्षात एसईबीसी प्रवर्गाकरिता उपलब्ध पदे</th>
                    <th>नोंदवणी दिनांक</th>
                </tr>
                <tr>
                    <td><?php echo $sebc_data['first_year_posts']; ?></td>
                    <td><?php echo $sebc_data['sebc_10percent']; ?></td>
                    <td><?php echo $sebc_data['sebc_available']; ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($sebc_data['created_at'])); ?></td>
                </tr>
            </table>
            <?php endif; ?>

            <!-- EWS Table -->
            <?php if ($ews_data): ?>
            <div class="section-title">आर्थिक दृष्ट्या दुर्बल घटक आरक्षण करिता गणना</div>
            <table>
                <tr>
                    <th>रिक्त पदे दिनांक</th>
                    <th>मागील वर्ष पदे</th>
                    <th>चालू वर्ष पदे</th>
                    <th>एकूण पदे</th>
                    <th>१०% नुसार येणारी पदे</th>
                    <th>उपलब्ध पदे</th>
                    <th>नोंदवणी दिनांक</th>
                </tr>
                <tr>
                    <td><?php echo $ews_data['from_date'] . ' ते ' . $ews_data['to_date']; ?></td>
                    <td><?php echo $ews_data['prev_posts']; ?></td>
                    <td><?php echo $ews_data['curr_posts']; ?></td>
                    <td><?php echo $ews_data['total_posts']; ?></td>
                    <td><?php echo $ews_data['sebc_10percent_new']; ?></td>
                    <td><?php echo $ews_data['sebc_available_new']; ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($ews_data['created_at'])); ?></td>
                </tr>
            </table>
            <?php endif; ?>

        <?php else: ?>
            <div class="no-data">
                <h3>❌ या पदासाठी कोणताही डेटा उपलब्ध नाही</h3>
                <p>कृपया पद सुधारित करण्यासाठी <a href="user_post.php?post_name=<?php echo urlencode($post_name); ?>">येथे क्लिक करा</a></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>