<?php
require_once('constant.php');
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'MSkrishna@14');
define('DB_DATABASE', 'bindunamavali');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_DATABASE);

if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8mb4");
