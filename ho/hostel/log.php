<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
check_login();

$user_id = $_SESSION['id'];

// Fetch user's posts
$query = "SELECT DISTINCT post_name, 
          MAX(created_at) as last_updated
          FROM user_posts 
          WHERE user_id = ? 
          GROUP BY post_name 
          ORDER BY last_updated DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="mr">
<head>
    <meta charset="UTF-8">
    <title>माझ्या सेव्ह केलेल्या पोस्ट्स</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header-controls {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            font-size: 14px;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-edit {
            background: #17a2b8;
            color: white;
        }
        .post-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        .post-item {
            padding: 15px;
            border: 1px solid #ddd;
            margin-bottom: 10px;
            border-radius: 5px;
            background: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }
        .post-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .post-info {
            flex-grow: 1;
        }
        .post-name {
            font-weight: bold;
            color: #007bff;
            font-size: 16px;
        }
        .post-date {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .post-actions {
            display: flex;
            gap: 10px;
        }
        .no-posts {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 16px;
            background: #f9f9f9;
            border-radius: 5px;
            border: 1px dashed #ddd;
        }
        .no-posts-icon {
            font-size: 48px;
            margin-bottom: 15px;
            color: #ccc;
        }
        .post-count {
            background: #007bff;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
        }
        .view-link {
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
        }
        .view-link:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .post-item {
                flex-direction: column;
                align-items: flex-start;
            }
            .post-actions {
                margin-top: 10px;
                width: 100%;
                justify-content: flex-start;
            }
            .header-controls {
                flex-direction: column;
                align-items: stretch;
            }
            .btn {
                width: 100%;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📋 माझ्या सेव्ह केलेल्या पोस्ट्स 
            <span class="post-count"><?php echo $result->num_rows; ?> पोस्ट</span>
        </h2>
        
        <div class="header-controls">
            <div>
                <a href="post_entry.php" class="btn btn-success">➕ नवीन पोस्ट तयार करा</a>
                <a href="dashboard.php" class="btn btn-primary">🏠 डॅशबोर्ड</a>
            </div>
        </div>
        
        <?php if ($result->num_rows > 0): ?>
            <ul class="post-list">
                <?php 
                $counter = 1;
                while ($row = $result->fetch_assoc()): 
                    $post_name = htmlspecialchars($row['post_name']);
                    $last_updated = date('d/m/Y H:i', strtotime($row['last_updated']));
                ?>
                <li class="post-item">
                    <div class="post-info">
                        <div class="post-name">
                            <?php echo $counter; ?>. <?php echo $post_name; ?>
                        </div>
                        <div class="post-date">
                            अंतिम अपडेट: <?php echo $last_updated; ?>
                        </div>
                    </div>
                    <div class="post-actions">
                        <a href="post_entry.php?post_name=<?php echo urlencode($row['post_name']); ?>" 
                           class="btn btn-edit">✏️ संपादित</a>
                        <a href="view_post.php?post_name=<?php echo urlencode($row['post_name']); ?>" 
                           class="view-link">👁️ पहा</a>
                    </div>
                </li>
                <?php 
                $counter++;
                endwhile; 
                ?>
            </ul>
        <?php else: ?>
            <div class="no-posts">
                <div class="no-posts-icon">📭</div>
                <h3>अजून कोणतीही पोस्ट सेव्ह केलेली नाही</h3>
                <p>तुम्ही अजून कोणतीही पोस्ट तयार किंवा सेव्ह केलेली नाही.</p>
                <a href="post_entry.php" class="btn btn-success" style="margin-top: 15px;">➕ पहिली पोस्ट तयार करा</a>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 20px; text-align: center; color: #666; font-size: 14px;">
            एकूण <?php echo $result->num_rows; ?> पोस्ट सेव्ह केलेल्या आहेत
        </div>
    </div>

    <script>
    // Simple confirmation for navigation
    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.btn-edit');
        editButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('तुम्हाला ही पोस्ट संपादित करायची आहे?')) {
                    e.preventDefault();
                }
            });
        });
    });
    </script>
</body>
</html>