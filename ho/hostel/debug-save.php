<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
check_login();

echo "<h2>Debug: Form Data Received</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>POST Data:</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    echo "<h3>SESSION Data:</h3>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
    
    echo "<h3>Test Database Insert:</h3>";
    
    // Try a simple insert
    $test_sql = "INSERT INTO user_posts (user_id, post_name, category, col0, remark) 
                 VALUES (1, 'DEBUG_TEST', 'TEST_CAT', 99, 'Debug test')";
    
    if ($conn->query($test_sql)) {
        echo "✓ Test insert successful<br>";
        $conn->query("DELETE FROM user_posts WHERE post_name = 'DEBUG_TEST'");
    } else {
        echo "✗ Test insert failed: " . $conn->error . "<br>";
    }
} else {
    echo "No POST data received.";
}

echo "<br><br><a href='post-entry.php'>Back to Form</a>";
?>