<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
check_login();

$user_id = $_SESSION['id'];

// Get all unique post names for this user
$query = $conn->query("SELECT DISTINCT post_name, MAX(created_at) as last_updated 
                      FROM user_posts 
                      WHERE user_id = '$user_id' 
                      GROUP BY post_name 
                      ORDER BY last_updated DESC");

$posts = [];
while($row = $query->fetch_assoc()) {
    $posts[] = $row;
}
?>

<!DOCTYPE html>
<html lang="mr">
<head>
    <meta charset="UTF-8">
    <title>सर्व पद डेटा</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        .post-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .post-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            border-color: #0077cc;
        }
        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .post-name {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }
        .post-date {
            color: #7f8c8d;
            font-size: 14px;
        }
        .post-actions {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s ease;
        }
        .btn-view {
            background: #3498db;
            color: white;
        }
        .btn-edit {
            background: #f39c12;
            color: white;
        }
        .btn-delete {
            background: #e74c3c;
            color: white;
        }
        .btn-home {
            background: #27ae60;
            color: white;
            padding: 10px 20px;
            margin-bottom: 20px;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
        .search-box {
            margin-bottom: 20px;
            padding: 10px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
            background: #ecf0f1;
            padding: 15px;
            border-radius: 8px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-label {
            font-size: 14px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 सर्व पदांची माहिती</h1>
        
        <a href="dashboard.php" class="btn btn-home">🏠 मुख्यपृष्ठ</a>
        <a href="user_post.php" class="btn btn-home" style="background: #0077cc;">➕ नवीन पद जोडा</a>

        <div class="stats">
            <div class="stat-item">
                <div class="stat-number"><?php echo count($posts); ?></div>
                <div class="stat-label">एकूण पदे</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo date('d/m/Y'); ?></div>
                <div class="stat-label">आजची तारीख</div>
            </div>
        </div>

        <input type="text" id="searchInput" class="search-box" placeholder="🔍 पदाच्या नावाने शोधा...">

        <?php if (count($posts) > 0): ?>
            <div id="postsList">
                <?php foreach ($posts as $index => $post): ?>
                    <div class="post-card" data-post-name="<?php echo htmlspecialchars($post['post_name']); ?>">
                        <div class="post-header">
                            <div class="post-name"><?php echo ($index + 1) . '. ' . htmlspecialchars($post['post_name']); ?></div>
                            <div class="post-date">अंतिम सुधारणा: <?php echo date('d/m/Y H:i', strtotime($post['last_updated'])); ?></div>
                        </div>
                        <div class="post-actions">
                            <a href="view_post_details.php?post_name=<?php echo urlencode($post['post_name']); ?>" class="btn btn-view">👁️ पाहा</a>
                            <a href="user_post.php?post_name=<?php echo urlencode($post['post_name']); ?>" class="btn btn-edit">✏️ सुधारा</a>
                            <button onclick="deletePost('<?php echo urlencode($post['post_name']); ?>')" class="btn btn-delete">🗑️ काढून टाका</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>❌ अजून कोणतेही पद सेव्ह केलेले नाही</h3>
                <p>पहिले पद नोंदवण्यासाठी <a href="user_post.php">येथे क्लिक करा</a></p>
            </div>
        <?php endif; ?>
    </div>

    <script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const postCards = document.querySelectorAll('.post-card');
        
        postCards.forEach(card => {
            const postName = card.getAttribute('data-post-name').toLowerCase();
            if (postName.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });

    // Delete post function
    function deletePost(postName) {
        if (confirm('⚠️ खात्री आहे का? "' + decodeURIComponent(postName) + '" हे पद कायमस्वरूपी काढून टाकायचे?')) {
            fetch('delete_post.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'post_name=' + postName
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ पद यशस्वीरित्या काढून टाकले!');
                    location.reload();
                } else {
                    alert('❌ त्रुटी: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ नेटवर्क त्रुटी!');
            });
        }
    }

    // Add click event to entire post card
    document.querySelectorAll('.post-card').forEach(card => {
        card.addEventListener('click', function(e) {
            // Only navigate if not clicking on buttons
            if (!e.target.classList.contains('btn')) {
                const postName = this.getAttribute('data-post-name');
                window.location.href = 'view_post_details.php?post_name=' + encodeURIComponent(postName);
            }
        });
    });
    </script>
</body>
</html>