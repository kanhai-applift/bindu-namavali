<?php
$servername = "localhost";
$dbusername = "audiobus_zeroin";   // default XAMPP username
$dbpassword = "b66W6R@L25";       // default XAMPP password is blank
$dbname     = "audiobus_zeroin"; // <-- replace with your actual database name

// Create connection
$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}
?>