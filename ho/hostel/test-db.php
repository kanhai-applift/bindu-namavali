<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
check_login();

echo "<h3>Testing Database Connection</h3>";

// Test 1: Check if table exists
$table_check = $conn->query("SHOW TABLES LIKE 'user_posts'");
if ($table_check->num_rows > 0) {
    echo "✓ Table 'user_posts' exists<br>";
    
    // Test 2: Check table structure
    $desc = $conn->query("DESCRIBE user_posts");
    echo "<h4>Table Structure:</h4>";
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while($row = $desc->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "✗ Table 'user_posts' does not exist<br>";
    
    // Create table if it doesn't exist
    $create_sql = "CREATE TABLE user_posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        post_name VARCHAR(255) NOT NULL,
        category VARCHAR(255) NOT NULL,
        col0 INT DEFAULT 0,
        col1 INT DEFAULT 0,
        col2 INT DEFAULT 0,
        col3 INT DEFAULT 0,
        col4 INT DEFAULT 0,
        col5 INT DEFAULT 0,
        col6 INT DEFAULT 0,
        col7 INT DEFAULT 0,
        col8 INT DEFAULT 0,
        col9 INT DEFAULT 0,
        col10 INT DEFAULT 0,
        total INT DEFAULT 0,
        remark TEXT,
        category_date DATE,
        from_date DATE,
        to_date DATE,
        entry_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        entry_id VARCHAR(100)
    )";
    
    if ($conn->query($create_sql)) {
        echo "✓ Table 'user_posts' created successfully<br>";
    } else {
        echo "✗ Failed to create table: " . $conn->error . "<br>";
    }
}

// Test 3: Try a simple insert
$test_insert = $conn->query("INSERT INTO user_posts (user_id, post_name, category, col0) VALUES (1, 'TEST', 'TEST_CAT', 1)");
if ($test_insert) {
    echo "✓ Test insert successful<br>";
    $conn->query("DELETE FROM user_posts WHERE post_name = 'TEST'");
} else {
    echo "✗ Test insert failed: " . $conn->error . "<br>";
}

echo "<br><a href='post-entry.php'>Back to Form</a>";
?>