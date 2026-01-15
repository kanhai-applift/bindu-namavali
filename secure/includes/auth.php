<?php
if(!isset($_SESSION)) { session_start(); }

require_once(__DIR__.'/helper.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . baseUrl('login'));
    exit;
}

function require_login() {
    /* 3️⃣ Authorization check */
    if (!in_array($_SESSION['role'], ['admin', 'superadmin'], true)) {
        header("Location:".baseUrl('login'));
    }
}

function require_superadmin() {
    require_login();
    if ($_SESSION['role'] !== 'superadmin') {
        die('Access denied');
    }
}
