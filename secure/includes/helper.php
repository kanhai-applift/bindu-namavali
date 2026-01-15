<?php
// This includes csrf.php for pages using header.php
require_once(__DIR__.'/bootstrap.php');
require_once('config/constant.php');


// 1️⃣ Get path from .htaccess
$path = $_GET['path'] ?? '';

// 2️⃣ Convert path to array
$segments = array_values(array_filter(explode('/', $path)));

// 3️⃣ Sanitize each segment
$segments = array_map(function ($seg) {
    return htmlspecialchars(strip_tags($seg), ENT_QUOTES, 'UTF-8');
}, $segments);

$current_page = 'dashboard';

if (!empty($segments[0]) && in_array($segments[0], ALLOWED_PAGES)) {
    $current_page = $segments[0];
}
else {
    $current_page = '404';
}

// $current_page = $segments[0]
//     ?? (!empty($_SESSION['logged_in']) ? 'login' : 'dashboard');

// 4️⃣ Limit number of segments
if (count($segments) > 6) {
    http_response_code(400);
    exit('Invalid URL');
}

function selected($value, $current) {
    return $value === $current ? 'selected' : '';
}

function csrfField() {
    return '<input type="hidden" id="csrf_token" name="csrf_token" value="'.csrf_token().'">';
}

// Function to convert MySQL date to DD/MM/YYYY format
function formatDate($mysqlDate) {
    if (empty($mysqlDate) || $mysqlDate == '0000-00-00') {
        return '';
    }
    $date = DateTime::createFromFormat('Y-m-d', $mysqlDate);
    return $date ? $date->format('d/m/Y') : '';
}

function formatToIST(string $dateString): string {
    if(empty($dateString)) return '--';
    try {
        // 1. Create a DateTime object with the input date (assuming input is UTC or Server Time)
        $date = new DateTime($dateString);

        // 2. Set the timezone to IST (Asia/Kolkata)
        $date->setTimezone(new DateTimeZone('Asia/Kolkata'));

        // 3. Format: d/m/Y (Date), h:i a (12-hour time with am/pm)
        return $date->format('d/m/Y h:i a');
    } catch (Exception $e) {
        return "Invalid Date";
    }
}

function getCacheVersion(): int {
    $interval = 300; // 5 minutes in seconds
    return floor(time() / $interval);
}