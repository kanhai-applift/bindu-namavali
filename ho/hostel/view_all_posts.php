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

// Get data for selected post
$selected_post = null;
$post_data = [];
$sebc_data = null;
$ews_data = null;

if (isset($_GET['view_post'])) {
    $post_name = urldecode($_GET['view_post']);
    
    // Get main post data
    $data_query = $conn->query("SELECT * FROM user_posts 
                               WHERE user_id = '$user_id' AND post_name = '" . $conn->real_escape_string($post_name) . "' 
                               ORDER BY FIELD(category, 'मंजूर_पदे', 'कार्यारत_पदे', 'दिनांक', 'संभाव्य_भरवयाची_पदे', 'एकूण_भरायची_पदे', 'अतिरिक्त_पदे')");
    
    while($row = $data_query->fetch_assoc()) {
        $post_data[$row['category']] = $row;
    }
    
    $selected_post = $post_name;
    
    // Get SEBC data
    $sebc_query = $conn->query("SELECT * FROM sebc_data 
                               WHERE user_id = '$user_id' AND post_name = '" . $conn->real_escape_string($post_name) . "' 
                               ORDER BY created_at DESC LIMIT 1");
    $sebc_data = $sebc_query->fetch_assoc();
    
    // Get EWS data
    $ews_query = $conn->query("SELECT * FROM ews_data 
                              WHERE user_id = '$user_id' AND post_name = '" . $conn->real_escape_string($post_name) . "' 
                              ORDER BY created_at DESC LIMIT 1");
    $ews_data = $ews_query->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="mr">
<head>
    <meta charset="UTF-8">
    <title>सर्व पद डेटा - सूची आणि सारणी दृश्य</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            background: #f8f9fa;
            color: #333;
        }
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 350px;
            background: #2c3e50;
            color: white;
            padding: 20px;
            overflow-y: auto;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
        }
        .sidebar-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #34495e;
        }
        .sidebar-header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .search-box {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .post-list {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }
        .post-item {
            background: #34495e;
            margin: 8px 0;
            padding: 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            border-left: 4px solid #3498db;
        }
        .post-item:hover {
            background: #3d566e;
            transform: translateX(5px);
        }
        .post-item.active {
            background: #3498db;
            border-left-color: #2980b9;
        }
        .post-name {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 5px;
        }
        .post-date {
            font-size: 12px;
            color: #bdc3c7;
        }
        .stats {
            background: #34495e;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .stat-item {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }
        
        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 350px;
            padding: 20px;
            overflow-y: auto;
        }
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header-actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-primary {
            background: #3498db;
            color: white;
        }
        .btn-success {
            background: #27ae60;
            color: white;
        }
        .btn-warning {
            background: #f39c12;
            color: white;
        }
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .table-title {
            background: #2c3e50;
            color: white;
            padding: 15px;
            margin: -20px -20px 20px -20px;
            border-radius: 10px 10px 0 0;
            font-size: 18px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .data-table th {
            background: #f2a65a;
            color: #2c3e50;
            padding: 12px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        .data-table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .data-table tr:nth-child(even) {
            background: #f9f9f9;
        }
        .data-table tr:hover {
            background: #f1f1f1;
        }
        .category-cell {
            background: #f9e7c4 !important;
            font-weight: bold;
            text-align: left !important;
        }
        .total-cell {
            background: #e7f3ff !important;
            font-weight: bold;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }
        .empty-state h3 {
            margin-bottom: 10px;
            font-size: 24px;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar with Post List -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h1>📊 माझी पदे</h1>
                <p>सर्व सेव्ह केलेली पदे</p>
            </div>
            
            <input type="text" id="searchInput" class="search-box" placeholder="🔍 पदाच्या नावाने शोधा...">
            
            <div class="post-list" id="postList">
                <?php if (count($posts) > 0): ?>
                    <?php foreach ($posts as $index => $post): ?>
                        <div class="post-item <?php echo ($selected_post == $post['post_name']) ? 'active' : ''; ?>" 
                             onclick="viewPost('<?php echo urlencode($post['post_name']); ?>')">
                            <div class="post-name"><?php echo ($index + 1) . '. ' . htmlspecialchars($post['post_name']); ?></div>
                            <div class="post-date">अंतिम सुधारणा: <?php echo date('d/m/Y H:i', strtotime($post['last_updated'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <p>❌ अजून कोणतेही पद सेव्ह केलेले नाही</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="stats">
                <div class="stat-item">
                    <span>एकूण पदे:</span>
                    <span><strong><?php echo count($posts); ?></strong></span>
                </div>
                <div class="stat-item">
                    <span>आजची तारीख:</span>
                    <span><?php echo date('d/m/Y'); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Main Content Area -->
        <div class="main-content">
            <div class="content-header">
                <h2>
                    <?php if ($selected_post): ?>
                        📋 पद: <?php echo htmlspecialchars($selected_post); ?>
                    <?php else: ?>
                        👈 डावीकडील यादीतून पद निवडा
                    <?php endif; ?>
                </h2>
                
                <div class="header-actions">
                    <a href="dashboard.php" class="btn btn-primary">🏠 मुख्यपृष्ठ</a>
                    <a href="user_post.php" class="btn btn-success">➕ नवीन पद</a>
                    <?php if ($selected_post): ?>
                        <a href="user_post.php?post_name=<?php echo urlencode($selected_post); ?>" class="btn btn-warning">✏️ सुधारा</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($selected_post && !empty($post_data)): ?>
                <!-- Main Posts Table -->
                <div class="table-container">
                    <div class="table-title">मुख्य पद माहिती</div>
                    <table class="data-table">
                        <thead>
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
                        </thead>
                        <tbody>
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
                        </tbody>
                    </table>
                </div>

                <!-- SEBC Table -->
                <?php if ($sebc_data): ?>
                <div class="table-container">
                    <div class="table-title">एसईबीसी भारती करिता गणना</div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>पाहिल्या भरती वर्षात भरवयाची पदे</th>
                                <th>एसईबीसी भारती करीता १०% नुसार येणारी पदे</th>
                                <th>भरती वर्षात एसईबीसी प्रवर्गाकरिता उपलब्ध पदे</th>
                                <th>नोंदवणी दिनांक</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo $sebc_data['first_year_posts']; ?></td>
                                <td><?php echo $sebc_data['sebc_10percent']; ?></td>
                                <td><?php echo $sebc_data['sebc_available']; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($sebc_data['created_at'])); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <!-- EWS Table -->
                <?php if ($ews_data): ?>
                <div class="table-container">
                    <div class="table-title">आर्थिक दृष्ट्या दुर्बल घटक आरक्षण करिता गणना</div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>रिक्त पदे दिनांक</th>
                                <th>मागील वर्ष पदे</th>
                                <th>चालू वर्ष पदे</th>
                                <th>एकूण पदे</th>
                                <th>१०% नुसार येणारी पदे</th>
                                <th>उपलब्ध पदे</th>
                                <th>नोंदवणी दिनांक</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo $ews_data['from_date'] . ' ते ' . $ews_data['to_date']; ?></td>
                                <td><?php echo $ews_data['prev_posts']; ?></td>
                                <td><?php echo $ews_data['curr_posts']; ?></td>
                                <td><?php echo $ews_data['total_posts']; ?></td>
                                <td><?php echo $ews_data['sebc_10percent_new']; ?></td>
                                <td><?php echo $ews_data['sebc_available_new']; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($ews_data['created_at'])); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

            <?php elseif ($selected_post && empty($post_data)): ?>
                <div class="empty-state">
                    <h3>❌ या पदासाठी कोणताही डेटा उपलब्ध नाही</h3>
                    <p>कृपया पद सुधारित करण्यासाठी <a href="user_post.php?post_name=<?php echo urlencode($selected_post); ?>">येथे क्लिक करा</a></p>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <h3>👈 डावीकडील यादीतून पद निवडा</h3>
                    <p>पद निवडल्यानंतर येथे तपशील दिसेल</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    // Function to view post
    function viewPost(postName) {
        window.location.href = 'view_all_posts.php?view_post=' + postName;
    }

    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const postItems = document.querySelectorAll('.post-item');
        
        postItems.forEach(item => {
            const postName = item.querySelector('.post-name').textContent.toLowerCase();
            if (postName.includes(searchTerm)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Auto-scroll to active post
    window.addEventListener('load', function() {
        const activePost = document.querySelector('.post-item.active');
        if (activePost) {
            activePost.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl+F for search
        if (e.ctrlKey && e.key === 'f') {
            e.preventDefault();
            document.getElementById('searchInput').focus();
        }
        // Escape to clear search
        if (e.key === 'Escape') {
            document.getElementById('searchInput').value = '';
            document.getElementById('searchInput').focus();
        }
    });
    </script>
</body>
</html>