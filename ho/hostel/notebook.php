<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
check_login();

$user_id   = $_SESSION['id']; 
$post_name = isset($_GET['post']) ? trim($_GET['post']) : "";

if (empty($post_name)) {
    die("⚠️ Post not selected!");
}

// Get user's district from database - using your dataset structure
$district = '';
$user_query = $mysqli->prepare("SELECT district FROM userregistration WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
if ($user_result->num_rows > 0) {
    $user_data = $user_result->fetch_assoc();
    $district = $user_data['district'];
}

// Determine which file to open based on district
$user_post_file = (strtoupper(trim($district)) == 'YAVATMAL') ? 'yml_user_post.php' : 'user_post.php';

// Debug: Uncomment the line below to see which district and file are being used
// echo "<!-- Debug: District: $district | File: $user_post_file -->";

// Table name = notebook_userid_postname
$table_name = "notebook_" . $user_id . "_" . preg_replace('/\s+/', '_', strtolower($post_name));

// Step 1: Create table if not exists
$sql_create = "
CREATE TABLE IF NOT EXISTS `$table_name` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bindu_kramaank VARCHAR(50),
    bindu_namavli VARCHAR(255),
    karmachari_naam VARCHAR(100),
    karmachari_jat VARCHAR(100),
    pad_niyukt_dinank DATE,
    janma_tarik DATE,
    sevaniroti_dinank DATE,
    jat_pramanpatra VARCHAR(255),
    jat_pramanpatra_pradikar VARCHAR(255),
    jat_vaidhta_pramanpatra VARCHAR(255),
    jat_vaidhta_samiti VARCHAR(255),
    karyarat TINYINT(1) DEFAULT 0,
    shera TEXT,
    pdf_file VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
";
$mysqli->query($sql_create);

// Check if pdf_file column exists, if not add it
$result = $mysqli->query("SHOW COLUMNS FROM `$table_name` LIKE 'pdf_file'");
if ($result->num_rows == 0) {
    $mysqli->query("ALTER TABLE `$table_name` ADD pdf_file VARCHAR(255) DEFAULT NULL AFTER shera");
}

// Get the next bindu_kramaank value
$next_bindu_kramaank = 1;
$max_result = $mysqli->query("SELECT MAX(CAST(bindu_kramaank AS UNSIGNED)) as max_bindu FROM `$table_name`");
if ($max_result && $max_row = $max_result->fetch_assoc()) {
    $next_bindu_kramaank = $max_row['max_bindu'] + 1;
}

// Initialize variables for edit mode
$edit_mode = false;
$edit_id = null;
$edit_data = null;

// Check if we're in edit mode
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_mode = true;
    
    // Fetch the record to edit
    $stmt = $mysqli->prepare("SELECT * FROM `$table_name` WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_result = $stmt->get_result();
    
    if ($edit_result->num_rows > 0) {
        $edit_data = $edit_result->fetch_assoc();
    }
}

// Function to get category name from bindu_kramaank (supports numbers > 100)
function getCategoryName($bindu_kramaank) {
    // Convert to integer and handle numbers > 100
    $val = intval($bindu_kramaank);
    
    // If value is greater than 100, use modulo 100 to get equivalent number
    if ($val > 100) {
        $val = $val % 100;
        // If modulo result is 0, it means it's a multiple of 100, so use 100
        if ($val === 0) {
            $val = 100;
        }
    }

    // Categories mapping (1-100)
    $sc   = [1,12,21,27,37,43,51,61,67,73,81,91,97];
    $st   = [2,23,33,53,63,71,93];
    $vjA  = [3,41,83];
    $bjB  = [4,47,99];
    $bjC  = [7,31,57,99];
    $bjD  = [11,77];
    $smp  = [15,87];
    $obc  = [5,9,17,19,25,29,35,39,45,49,55,59,65,69,75,79,85,89,95];
    $ssmv = [6,13,24,36,42,54,66,74,84,96];
    $ews  = [8,16,26,38,46,56,68,76,86,98];
    $open = [10,14,18,20,22,28,30,32,34,40,44,48,50,52,58,60,62,64,70,72,78,80,82,88,90,92,94,100];

    if (in_array($val, $sc))       return "अनुसूचित जाती";
    else if (in_array($val, $st))  return "अनुसूचित जमाती";
    else if (in_array($val, $vjA)) return "विमुक्त जमाती (अ)";
    else if (in_array($val, $bjB)) return "भटक्या जमाती (ब)";
    else if (in_array($val, $bjC)) return "भटक्या जमाती (क)";
    else if (in_array($val, $bjD)) return "भटक्या जमाती (ड)";
    else if (in_array($val, $smp)) return "विशेष मागास प्रवर्ग";
    else if (in_array($val, $obc)) return "इतर मागास प्रवर्ग";
    else if (in_array($val, $ssmv)) return "सामाजिक आणि शैक्षणिक मागास वर्ग";
    else if (in_array($val, $ews)) return "आर्थिक दृष्ट्या दुर्बल घटक";
    else if (in_array($val, $open)) return "अराखीव";
    
    return "";
}

// Function to convert MySQL date to DD/MM/YYYY format
function formatDate($mysqlDate) {
    if (empty($mysqlDate) || $mysqlDate == '0000-00-00') {
        return '';
    }
    $date = DateTime::createFromFormat('Y-m-d', $mysqlDate);
    return $date ? $date->format('d/m/Y') : '';
}

// Function to convert DD/MM/YYYY to MySQL date format
function convertToMySQLDate($ddmmyyyy) {
    if (empty($ddmmyyyy)) {
        return null;
    }
    $date = DateTime::createFromFormat('d/m/Y', $ddmmyyyy);
    return $date ? $date->format('Y-m-d') : null;
}

// Function to calculate retirement date (birth date + 58 years to month end)
function calculateRetirementDate($birthDate) {
    if (empty($birthDate)) {
        return '';
    }
    $date = DateTime::createFromFormat('d/m/Y', $birthDate);
    if ($date) {
        // Add 58 years
        $date->modify('+58 years');
        // Set to last day of the month
        $date->modify('last day of this month');
        return $date->format('d/m/Y');
    }
    return '';
}

// Get initial category for new entry
$initial_category = getCategoryName($next_bindu_kramaank);

// Step 2: Insert or Update form data
if (isset($_POST['submit'])) {
    $karyarat = isset($_POST['karyarat']) ? 1 : 0;
    
    // Handle PDF upload
    $pdf_file_name = null;
    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = "uploads/notebook_pdfs/";
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_tmp = $_FILES['pdf_file']['tmp_name'];
        $file_name = basename($_FILES['pdf_file']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Validate PDF file
        if ($file_ext === 'pdf') {
            // Generate unique filename
            $pdf_file_name = uniqid() . '_' . time() . '.' . $file_ext;
            $destination = $upload_dir . $pdf_file_name;
            
            if (move_uploaded_file($file_tmp, $destination)) {
                // File uploaded successfully
            } else {
                $pdf_file_name = null;
                echo "<script>alert('PDF upload failed.');</script>";
            }
        } else {
            echo "<script>alert('Only PDF files are allowed.');</script>";
        }
    } elseif (isset($_POST['existing_pdf']) && !empty($_POST['existing_pdf'])) {
        // Keep existing PDF if no new file uploaded
        $pdf_file_name = $_POST['existing_pdf'];
    }

    // Auto-determine bindu_namavli based on bindu_kramaank (supports numbers > 100)
    $bindu_kramaank = $_POST['bindu_kramaank'];
    $bindu_namavli = getCategoryName($bindu_kramaank);

    // Convert DD/MM/YYYY dates to MySQL format
    $pad_niyukt_dinank = convertToMySQLDate($_POST['pad_niyukt_dinank']);
    $janma_tarik = convertToMySQLDate($_POST['janma_tarik']);
    $sevaniroti_dinank = convertToMySQLDate($_POST['sevaniroti_dinank']);

    if (isset($_POST['edit_id'])) {
        // Update existing record
        $edit_id = intval($_POST['edit_id']);
        
        if ($pdf_file_name) {
            $sql_update = "UPDATE `$table_name` SET 
                bindu_kramaank = ?, bindu_namavli = ?, karmachari_naam = ?, karmachari_jat = ?, 
                pad_niyukt_dinank = ?, janma_tarik = ?, sevaniroti_dinank = ?, 
                jat_pramanpatra = ?, jat_pramanpatra_pradikar = ?, 
                jat_vaidhta_pramanpatra = ?, jat_vaidhta_samiti = ?, 
                karyarat = ?, shera = ?, pdf_file = ?
                WHERE id = ?";
                
            $stmt = $mysqli->prepare($sql_update);
            $stmt->bind_param("sssssssssssissi", 
                $bindu_kramaank, $bindu_namavli, $_POST['karmachari_naam'], $_POST['karmachari_jat'],
                $pad_niyukt_dinank, $janma_tarik, $sevaniroti_dinank,
                $_POST['jat_pramanpatra'], $_POST['jat_pramanpatra_pradikar'], $_POST['jat_vaidhta_pramanpatra'],
                $_POST['jat_vaidhta_samiti'], $karyarat, $_POST['shera'], $pdf_file_name, $edit_id
            );
        } else {
            $sql_update = "UPDATE `$table_name` SET 
                bindu_kramaank = ?, bindu_namavli = ?, karmachari_naam = ?, karmachari_jat = ?, 
                pad_niyukt_dinank = ?, janma_tarik = ?, sevaniroti_dinank = ?, 
                jat_pramanpatra = ?, jat_pramanpatra_pradikar = ?, 
                jat_vaidhta_pramanpatra = ?, jat_vaidhta_samiti = ?, 
                karyarat = ?, shera = ?
                WHERE id = ?";
                
            $stmt = $mysqli->prepare($sql_update);
            $stmt->bind_param("sssssssssssisi", 
                $bindu_kramaank, $bindu_namavli, $_POST['karmachari_naam'], $_POST['karmachari_jat'],
                $pad_niyukt_dinank, $janma_tarik, $sevaniroti_dinank,
                $_POST['jat_pramanpatra'], $_POST['jat_pramanpatra_pradikar'], $_POST['jat_vaidhta_pramanpatra'],
                $_POST['jat_vaidhta_samiti'], $karyarat, $_POST['shera'], $edit_id
            );
        }
        
        if ($stmt->execute()) {
            $success_message = "Record updated successfully!";
        } else {
            $error_message = "Error updating record: " . $mysqli->error;
        }
    } else {
        // Insert new record - auto-increment bindu_kramaank
        $sql_insert = "INSERT INTO `$table_name` 
            (bindu_kramaank, bindu_namavli, karmachari_naam, karmachari_jat, pad_niyukt_dinank, 
             janma_tarik, sevaniroti_dinank, jat_pramanpatra, jat_pramanpatra_pradikar, 
             jat_vaidhta_pramanpatra, jat_vaidhta_samiti, karyarat, shera, pdf_file) 
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $stmt = $mysqli->prepare($sql_insert);
        $stmt->bind_param("sssssssssssiss", 
            $next_bindu_kramaank, $bindu_namavli, $_POST['karmachari_naam'], $_POST['karmachari_jat'],
            $pad_niyukt_dinank, $janma_tarik, $sevaniroti_dinank,
            $_POST['jat_pramanpatra'], $_POST['jat_pramanpatra_pradikar'], $_POST['jat_vaidhta_pramanpatra'],
            $_POST['jat_vaidhta_samiti'], $karyarat, $_POST['shera'], $pdf_file_name
        );
        
        if ($stmt->execute()) {
            $success_message = "Record added successfully!";
        } else {
            $error_message = "Error adding record: " . $mysqli->error;
        }
    }
    
    // Refresh the page to show the updated data
    header("Location: " . str_replace("&edit=" . $edit_id, "", $_SERVER['REQUEST_URI']));
    exit();
}

// Step 3: Fetch all records
$result = $mysqli->query("SELECT * FROM `$table_name` ORDER BY CAST(bindu_kramaank AS UNSIGNED) ASC");
?>

<!DOCTYPE html>
<html lang="mr">
<head>
<meta charset="UTF-8">
<title>Notebook - <?= htmlspecialchars($post_name) ?></title>
<!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Include jQuery UI -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<style>
    body { 
        font-family: Arial, sans-serif; 
        margin: 20px; 
        background-color: #f5f5f5; 
    }
    .container { 
        max-width: 95%; 
        margin: 0 auto; 
        background: white; 
        padding: 20px; 
        border-radius: 8px; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
    }
    h2 { 
        color: #333; 
        border-bottom: 2px solid #ff9933; 
        padding-bottom: 10px; 
    }
    table { 
        width: 100%; 
        border-collapse: collapse; 
        margin-top: 20px; 
        font-size: 14px; 
    }
    table, th, td { 
        border: 1px solid #ddd; 
    }
    th, td { 
        padding: 8px; 
        text-align: center; 
        vertical-align: middle;
    }
    th { 
        background: #ff9933; 
        color: white; 
        font-weight: bold;
    }
    input, textarea, select { 
        width: 100%; 
        box-sizing: border-box; 
        padding: 6px; 
        font-size: 14px; 
        border: 1px solid #ddd; 
        border-radius: 4px; 
        margin: 0;
    }
    .date-input {
        font-family: monospace;
        text-align: center;
        cursor: pointer;
        background: white;
    }
    .date-input:hover {
        background: #f9f9f9;
    }
    .date-input.auto-calculated {
        background: #f0f8ff;
        color: #0066cc;
        font-weight: bold;
        border: 2px solid #4CAF50;
    }
    .date-input.manually-edited {
        background: #fff8e1;
        color: #ff6f00;
        font-weight: bold;
        border: 2px solid #ff9800;
    }
    .ui-datepicker {
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .ui-datepicker-header {
        background: #ff9933;
        color: white;
        border: none;
    }
    .ui-datepicker-calendar th {
        background: #f5f5f5;
        color: #333;
    }
    .submit-btn { 
        background: #4CAF50; 
        color: white; 
        padding: 8px 15px; 
        border: none; 
        border-radius: 4px; 
        cursor: pointer; 
        font-weight: bold; 
        font-size: 14px;
        width: 100%;
        white-space: nowrap;
    }
    .submit-btn:hover { 
        background: #45a049; 
    }
    .cancel-btn {
        background: #6c757d;
        color: white;
        padding: 8px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        width: 100%;
        margin-top: 5px;
    }
    .cancel-btn:hover {
        background: #5a6268;
    }
    .pdf-btn { 
        background: #e74c3c; 
        color: white; 
        padding: 6px 12px; 
        border: none; 
        border-radius: 4px; 
        cursor: pointer; 
        text-decoration: none; 
        display: inline-block; 
        font-size: 12px;
    }
    .pdf-btn:hover { 
        background: #c0392b; 
    }
    .pdf-upload { 
        font-size: 12px; 
        padding: 5px;
    }
    .form-section { 
        background: #f9f9f9; 
        padding: 15px; 
        border-radius: 6px; 
        margin-bottom: 20px; 
    }
    .data-section { 
        overflow-x: auto; 
    }
    .back-btn { 
        background: #6c757d; 
        color: white; 
        padding: 10px 20px; 
        text-decoration: none; 
        border-radius: 4px; 
        display: inline-block; 
        margin-bottom: 20px; 
        font-size: 16px;
    }
    .back-btn:hover { 
        background: #5a6268; 
    }
    .message { 
        padding: 15px; 
        margin: 15px 0; 
        border-radius: 4px; 
        font-size: 16px;
    }
    .success { 
        background: #d4edda; 
        color: #155724; 
        border: 1px solid #c3e6cb; 
    }
    .error { 
        background: #f8d7da; 
        color: #721c24; 
        border: 1px solid #f5c6cb; 
    }
    .action-btns { 
        display: flex; 
        gap: 5px; 
        justify-content: center; 
    }
    .edit-btn { 
        background: #ffc107; 
        color: #212529; 
        padding: 6px 12px; 
        border: none; 
        border-radius: 4px; 
        cursor: pointer; 
        font-size: 12px;
        text-decoration: none;
        display: inline-block;
    }
    .print-btn { 
        background: #17a2b8; 
        color: white; 
        padding: 6px 12px; 
        border: none; 
        border-radius: 4px; 
        cursor: pointer; 
        font-size: 12px;
        text-decoration: none;
        display: inline-block;
    }
    .print-btn:hover { 
        background: #138496; 
    }
    .user-post-btn { 
        background: #6f42c1; 
        color: white; 
        padding: 10px 20px; 
        border: none; 
        border-radius: 4px; 
        cursor: pointer; 
        font-size: 16px;
        text-decoration: none;
        display: inline-block;
        margin-left: 10px;
    }
    .user-post-btn:hover { 
        background: #5a36a6; 
    }
    .form-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 0;
    }
    .form-table th {
        padding: 8px;
    }
    .form-table td {
        padding: 4px;
    }
    .checkbox-cell {
        text-align: center;
    }
    input[type="checkbox"] {
        width: 18px;
        height: 18px;
        margin: 0;
    }
    textarea {
        resize: vertical;
        min-height: 40px;
    }
    /* Custom sizes for specific fields */
    .bindu-kramaank {
        width: 80px !important;
    }
    .karmachari-jat {
        width: 100px !important;
    }
    .pradikar-pad {
        width: 130px !important;
    }
    .karmachari-naam {
        min-height: 60px;
    }
    .date-field {
        width: 120px !important;
    }
    .search-container {
        margin-bottom: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .search-box {
        padding: 8px 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        width: 250px;
    }
    .entries-count {
        font-size: 14px;
        color: #666;
    }
    .action-container {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
    }
    .top-buttons {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    .number-info {
        background: #e7f3ff;
        padding: 10px;
        border-radius: 5px;
        margin: 10px 0;
        font-size: 14px;
        border-left: 4px solid #2196F3;
    }
    .auto-calculation-info {
        background: #f0fff0;
        padding: 8px;
        border-radius: 4px;
        margin: 5px 0;
        font-size: 12px;
        border-left: 3px solid #4CAF50;
    }
    .manual-edit-info {
        background: #fff8e1;
        padding: 8px;
        border-radius: 4px;
        margin: 5px 0;
        font-size: 12px;
        border-left: 3px solid #ff9800;
    }
    @media (max-width: 1600px) {
        table { font-size: 12px; }
        th, td { padding: 6px; }
        input, textarea, select { 
            padding: 4px; 
            font-size: 12px; 
        }
        .search-box {
            width: 200px;
            font-size: 12px;
        }
        .date-field {
            width: 100px !important;
        }
    }
    @media print {
        .no-print {
            display: none !important;
        }
        body, .container {
            margin: 0;
            padding: 0;
            width: 100%;
            background: white;
            box-shadow: none;
        }
        table {
            font-size: 10pt;
            width: 100%;
        }
        th {
            background: #ddd !important;
            color: black !important;
            -webkit-print-color-adjust: exact;
        }
        h2 {
            color: black;
            border-bottom: 1px solid #000;
        }
    }
</style>
</head>
<body>

<div class="container">
    <h2>📓 Notebook for Post: <?= htmlspecialchars($post_name) ?></h2>
    
    <div class="number-info">
        <strong>Number Mapping Info:</strong> Numbers like 101, 201, 301, etc. will map to the same category as 1, 2, 3, etc. 
        (Example: 101 → category of 1, 202 → category of 2)<br>
        <strong>Date Format:</strong> DD/MM/YYYY (e.g., 25/12/2023) - Click on date fields to open calendar<br>
        <strong>Retirement Date:</strong> Auto-calculated as Birth Date + 58 years (month end), but can be manually edited
    </div>
    
    <div class="top-buttons">
        <a href="post_entry.php" class="back-btn">⬅ Back to Posts</a>
        <a href="<?= $user_post_file ?>?post_name=<?= urlencode($post_name) ?>" class="user-post-btn" target="_blank">➕गोषवारा</a>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="message success"><?= $success_message ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="message error"><?= $error_message ?></div>
    <?php endif; ?>

    <!-- Data Entry Form Section -->
    <div class="form-section no-print">
        <h3><?= $edit_mode ? 'Edit Entry' : 'Add New Entry' ?></h3>
        <form method="post" enctype="multipart/form-data">
            <?php if ($edit_mode): ?>
                <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
                <?php if (!empty($edit_data['pdf_file'])): ?>
                    <input type="hidden" name="existing_pdf" value="<?= $edit_data['pdf_file'] ?>">
                <?php endif; ?>
            <?php endif; ?>
            
            <table class="form-table">
                <thead>
                    <tr>
                       <th>बिंदू क्रमांक</th>
                        <th>बिंदूचा प्रवर्ग</th>
                        <th>कर्मचाऱ्याचे नाव</th>
                        <th>कर्मचारी जात व प्रवर्ग</th>
                        <th>नियुक्ती दिनांक</th>
                        <th>जन्म दिनांक</th>
                        <th>सेवा निवृत्ती दिनांक</th>
                        <th>जात प्रमाणपत्र क्रमांक</th>
                        <th>प्रधीकार्यांचे पदनाव</th>
                        <th>जात वैधता प्रमानपत्र क्रमांक</th>
                        <th>वैधता समितीचे नाव</th>
                        <th>कार्यरत ✅</th>
                        <th>शेरा</th>
                        <th>PDF File</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <?php if ($edit_mode): ?>
                                <input type="text" id="bindu_kramaank" name="bindu_kramaank" required class="bindu-kramaank" maxlength="5" value="<?= htmlspecialchars($edit_data['bindu_kramaank']) ?>">
                            <?php else: ?>
                                <input type="text" id="bindu_kramaank" name="bindu_kramaank" readonly class="bindu-kramaank" value="<?= $next_bindu_kramaank ?>">
                            <?php endif; ?>
                        </td>
                        <td><input type="text" id="bindu_namavli" name="bindu_namavli" readonly value="<?= $edit_mode ? htmlspecialchars($edit_data['bindu_namavli']) : $initial_category ?>"></td>
                        <td><textarea name="karmachari_naam" required class="karmachari-naam" rows="2"><?= $edit_mode ? htmlspecialchars($edit_data['karmachari_naam']) : '' ?></textarea></td>
                        <td><input type="text" name="karmachari_jat" class="karmachari-jat" value="<?= $edit_mode ? htmlspecialchars($edit_data['karmachari_jat']) : '' ?>"></td>
                        <td><input type="text" name="pad_niyukt_dinank" class="date-input date-field" placeholder="DD/MM/YYYY" id="pad_niyukt_dinank" value="<?= $edit_mode ? formatDate($edit_data['pad_niyukt_dinank']) : '' ?>"></td>
                        <td><input type="text" name="janma_tarik" class="date-input date-field" placeholder="DD/MM/YYYY" id="janma_tarik" value="<?= $edit_mode ? formatDate($edit_data['janma_tarik']) : '' ?>"></td>
                        <td>
                            <input type="text" name="sevaniroti_dinank" class="date-input date-field auto-calculated" placeholder="DD/MM/YYYY" id="sevaniroti_dinank" value="<?= $edit_mode ? formatDate($edit_data['sevaniroti_dinank']) : '' ?>">
                            <div class="auto-calculation-info" id="retirementInfo">Auto-calculated: Birth Date + 58 years (Month end date)</div>
                        </td>
                        <td><input type="text" name="jat_pramanpatra" value="<?= $edit_mode ? htmlspecialchars($edit_data['jat_pramanpatra']) : '' ?>"></td>
                        <td><input type="text" name="jat_pramanpatra_pradikar" class="pradikar-pad" value="<?= $edit_mode ? htmlspecialchars($edit_data['jat_pramanpatra_pradikar']) : '' ?>"></td>
                        <td><input type="text" name="jat_vaidhta_pramanpatra" value="<?= $edit_mode ? htmlspecialchars($edit_data['jat_vaidhta_pramanpatra']) : '' ?>"></td>
                        <td><input type="text" name="jat_vaidhta_samiti" value="<?= $edit_mode ? htmlspecialchars($edit_data['jat_vaidhta_samiti']) : '' ?>"></td>
                        <td class="checkbox-cell"><input type="checkbox" name="karyarat" value="1" <?= ($edit_mode && $edit_data['karyarat']) || !$edit_mode ? 'checked' : '' ?>></td>
                        <td><textarea name="shera" rows="2"><?= $edit_mode ? htmlspecialchars($edit_data['shera']) : '' ?></textarea></td>
                        <td>
                            <input type="file" name="pdf_file" accept=".pdf" class="pdf-upload">
                            <?php if ($edit_mode && !empty($edit_data['pdf_file'])): ?>
                                <div style="font-size: 11px; margin-top: 5px;">
                                    Current: <a href="uploads/notebook_pdfs/<?= $edit_data['pdf_file'] ?>" target="_blank">View</a>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button type="submit" name="submit" class="submit-btn"><?= $edit_mode ? 'Update' : 'Save' ?></button>
                            <?php if ($edit_mode): ?>
                                <a href="?post=<?= urlencode($post_name) ?>" class="cancel-btn">Cancel</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
    </div>

    <!-- Data Display Section -->
    <div class="data-section">
        <h3>Saved Entries</h3>
        
        <!-- Action Container with Search and Print Button -->
        <div class="action-container no-print">
            <!-- Search Box -->
            <input type="text" id="searchInput" class="search-box" placeholder="Search बिंदू क्रामांक..." onkeyup="searchTable()">
            
            <!-- Print Button -->
            <button onclick="printTable()" class="print-btn">🖨️ Print Table</button>
            
            <div class="entries-count" id="entriesCount">
                Total entries: <?= $result->num_rows ?>
            </div>
        </div>
        
        <?php if ($result->num_rows > 0): ?>
            <table id="dataTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>बिंदू क्रमांक</th>
                        <th>बिंदूचा प्रवर्ग</th>
                        <th>कर्मचाऱ्याचे नाव</th>
                        <th>कर्मचारी जात व प्रवर्ग</th>
                        <th>नियुक्ती दिनांक</th>
                        <th>जन्म दिनांक</th>
                        <th>सेवा निवृत्ती दिनांक</th>
                        <th>जात प्रमाणपत्र क्रमांक</th>
                        <th>प्रधीकार्यांचे पदनाव</th>
                        <th>जात वैधता प्रमानपत्र क्रमांक</th>
                        <th>वैधता समितीचे नाव</th>
                        <th>कार्यरत ✅</th>
                        <th>शेरा</th>
                        <th>PDF File</th>
                        <th class="no-print">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['bindu_kramaank'] ?></td>
                        <td><?= $row['bindu_namavli'] ?></td>
                        <td><?= $row['karmachari_naam'] ?></td>
                        <td><?= $row['karmachari_jat'] ?></td>
                        <td><?= formatDate($row['pad_niyukt_dinank']) ?></td>
                        <td><?= formatDate($row['janma_tarik']) ?></td>
                        <td><?= formatDate($row['sevaniroti_dinank']) ?></td>
                        <td><?= $row['jat_pramanpatra'] ?></td>
                        <td><?= $row['jat_pramanpatra_pradikar'] ?></td>
                        <td><?= $row['jat_vaidhta_pramanpatra'] ?></td>
                        <td><?= $row['jat_vaidhta_samiti'] ?></td>
                        <td><?= $row['karyarat'] ? "✅" : "❌" ?></td>
                        <td><?= $row['shera'] ?></td>
                        <td>
                            <?php if (!empty($row['pdf_file'])): ?>
                                <a href="uploads/notebook_pdfs/<?= $row['pdf_file'] ?>" target="_blank" class="pdf-btn">View PDF</a>
                            <?php else: ?>
                                No PDF
                            <?php endif; ?>
                        </td>
                        <td class="action-btns no-print">
                            <a href="?post=<?= urlencode($post_name) ?>&edit=<?= $row['id'] ?>" class="edit-btn">Edit</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No entries found. Add your first entry using the form above.</p>
        <?php endif; ?>
    </div>
</div>

<script>
// Date picker initialization
$(document).ready(function() {
    // Configure datepicker for DD/MM/YYYY format with year range 1950-2090
    $.datepicker.setDefaults({
        dateFormat: 'dd/mm/yy',
        changeMonth: true,
        changeYear: true,
        yearRange: '1950:2090',
        showButtonPanel: true
    });

    // Initialize datepicker for ALL date fields including retirement date
    $('#pad_niyukt_dinank, #janma_tarik, #sevaniroti_dinank').datepicker({
        onSelect: function(dateText, inst) {
            // Format validation
            if (isValidDate(dateText)) {
                $(this).val(dateText);
                
                // If birth date is changed, update retirement date (unless manually edited)
                if (this.id === 'janma_tarik' && !isRetirementDateManuallyEdited()) {
                    calculateRetirementDate();
                }
                
                // If retirement date is manually edited, update the styling
                if (this.id === 'sevaniroti_dinank') {
                    setRetirementDateManuallyEdited(true);
                }
            }
        }
    });

    let retirementDateManuallyEdited = false;

    // Function to check if retirement date was manually edited
    function isRetirementDateManuallyEdited() {
        return retirementDateManuallyEdited;
    }

    // Function to set manual edit status
    function setRetirementDateManuallyEdited(edited) {
        retirementDateManuallyEdited = edited;
        const retirementField = $('#sevaniroti_dinank');
        const infoDiv = $('#retirementInfo');
        
        if (edited) {
            retirementField.removeClass('auto-calculated').addClass('manually-edited');
            infoDiv.removeClass('auto-calculation-info').addClass('manual-edit-info')
                   .text('Manually edited retirement date');
        } else {
            retirementField.removeClass('manually-edited').addClass('auto-calculated');
            infoDiv.removeClass('manual-edit-info').addClass('auto-calculation-info')
                   .text('Auto-calculated: Birth Date + 58 years (Month end date)');
        }
    }

    // Function to calculate retirement date (birth date + 58 years to month end)
    function calculateRetirementDate() {
        var birthDate = $('#janma_tarik').val();
        
        if (isValidDate(birthDate) && !isRetirementDateManuallyEdited()) {
            var parts = birthDate.split("/");
            var day = parseInt(parts[0], 10);
            var month = parseInt(parts[1], 10);
            var year = parseInt(parts[2], 10);
            
            // Add 58 years to birth date
            var retirementYear = year + 58;
            
            // Calculate last day of the retirement month
            var lastDayOfMonth = new Date(retirementYear, month, 0).getDate();
            var retirementDate = lastDayOfMonth.toString().padStart(2, '0') + '/' + 
                                month.toString().padStart(2, '0') + '/' + 
                                retirementYear;
            
            if (isValidDate(retirementDate)) {
                $('#sevaniroti_dinank').val(retirementDate);
                setRetirementDateManuallyEdited(false);
            }
        }
    }

    // Function to validate DD/MM/YYYY date
    function isValidDate(dateString) {
        if (!dateString) return false;
        
        var parts = dateString.split("/");
        if (parts.length !== 3) return false;
        
        var day = parseInt(parts[0], 10);
        var month = parseInt(parts[1], 10);
        var year = parseInt(parts[2], 10);
        
        if (isNaN(day) || isNaN(month) || isNaN(year)) return false;
        if (month < 1 || month > 12) return false;
        if (day < 1 || day > 31) return false;
        
        // Check for valid days in month
        var monthLength = [31,28,31,30,31,30,31,31,30,31,30,31];
        // Adjust for leap years
        if (year % 400 == 0 || (year % 100 != 0 && year % 4 == 0)) {
            monthLength[1] = 29;
        }
        
        return day <= monthLength[month - 1];
    }

    // Manual date input formatting with retirement date calculation
    $('.date-input').on('input', function(e) {
        var input = e.target.value.replace(/\D/g, '');
        
        if (input.length >= 2) {
            input = input.substring(0, 2) + '/' + input.substring(2);
        }
        if (input.length >= 5) {
            input = input.substring(0, 5) + '/' + input.substring(5, 9);
        }
        
        e.target.value = input;
        
        // If birth date is changed, update retirement date (unless manually edited)
        if (e.target.id === 'janma_tarik' && isValidDate(input) && !isRetirementDateManuallyEdited()) {
            calculateRetirementDate();
        }
        
        // If retirement date is manually typed, mark it as manually edited
        if (e.target.id === 'sevaniroti_dinank' && isValidDate(input)) {
            setRetirementDateManuallyEdited(true);
        }
    });

    // Restrict to numbers only for date fields
    $('.date-input').on('keydown', function(e) {
        // Allow: backspace, delete, tab, escape, enter
        if ([46, 8, 9, 27, 13].includes(e.keyCode) ||
            // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
            (e.keyCode == 65 && e.ctrlKey === true) ||
            (e.keyCode == 67 && e.ctrlKey === true) ||
            (e.keyCode == 86 && e.ctrlKey === true) ||
            (e.keyCode == 88 && e.ctrlKey === true) ||
            // Allow: home, end, left, right
            (e.keyCode >= 35 && e.keyCode <= 39)) {
            return;
        }
        
        // Ensure that it is a number and stop the keypress
        if ((e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });

    // Reset manual edit status when birth date is cleared
    $('#janma_tarik').on('input', function() {
        if (!$(this).val()) {
            setRetirementDateManuallyEdited(false);
            $('#sevaniroti_dinank').val('');
        }
    });

    // Calculate retirement date on page load if birth date exists
    if ($('#janma_tarik').val() && isValidDate($('#janma_tarik').val())) {
        // Check if retirement date matches auto-calculation
        const birthDate = $('#janma_tarik').val();
        const calculatedDate = calculateRetirementDateFromBirthDate(birthDate);
        const currentRetirementDate = $('#sevaniroti_dinank').val();
        
        if (calculatedDate !== currentRetirementDate) {
            setRetirementDateManuallyEdited(true);
        } else {
            setRetirementDateManuallyEdited(false);
        }
    }

    // Helper function to calculate retirement date without setting the field
    function calculateRetirementDateFromBirthDate(birthDate) {
        if (!isValidDate(birthDate)) return '';
        
        var parts = birthDate.split("/");
        var day = parseInt(parts[0], 10);
        var month = parseInt(parts[1], 10);
        var year = parseInt(parts[2], 10);
        
        // Add 58 years to birth date
        var retirementYear = year + 58;
        
        // Calculate last day of the retirement month
        var lastDayOfMonth = new Date(retirementYear, month, 0).getDate();
        return lastDayOfMonth.toString().padStart(2, '0') + '/' + 
               month.toString().padStart(2, '0') + '/' + 
               retirementYear;
    }
});

// Number mapping functionality
document.getElementById("bindu_kramaank").addEventListener("input", function() {
    let val = parseInt(this.value, 10);
    
    if (isNaN(val)) {
        document.getElementById("bindu_namavli").value = "";
        return;
    }
    
    // If value is greater than 100, use modulo 100 to get equivalent number
    if (val > 100) {
        val = val % 100;
        // If modulo result is 0, it means it's a multiple of 100, so use 100
        if (val === 0) {
            val = 100;
        }
    }

    // Categories mapping (1-100)
    const sc   = [1,12,21,27,37,43,51,61,67,73,81,91,97];
    const st   = [2,23,33,53,63,71,93];
    const vjA  = [3,41,83];
    const bjB  = [4,47,99];
    const bjC  = [7,31,57,99];
    const bjD  = [11,77];
    const smp  = [15,87];
    const obc  = [5,9,17,19,25,29,35,39,45,49,55,59,65,69,75,79,85,89,95];
    const ssmv = [6,13,24,36,42,54,66,74,84,96];
    const ews  = [8,16,26,38,46,56,68,76,86,98];
    const open = [10,14,18,20,22,28,30,32,34,40,44,48,50,52,58,60,62,64,70,72,78,80,82,88,90,92,94,100];

    let category = "";

    if (sc.includes(val))       category = "अनुसूचित जाती";
    else if (st.includes(val))  category = "अनुसूचित जमाती";
    else if (vjA.includes(val)) category = "विमुक्त जमाती (अ)";
    else if (bjB.includes(val)) category = "भटक्या जमाती (ब)";
    else if (bjC.includes(val)) category = "भटक्या जमाती (क)";
    else if (bjD.includes(val)) category = "भटक्या जमाती (ड)";
    else if (smp.includes(val)) category = "विशेष मागास प्रवर्ग";
    else if (obc.includes(val)) category = "इतर मागास प्रवर्ग";
    else if (ssmv.includes(val)) category = "सामाजिक आणि शैक्षणिक मागास वर्ग";
    else if (ews.includes(val)) category = "आर्थिक दृष्ट्या दुर्बल घटक";
    else if (open.includes(val)) category = "अराखीव";

    document.getElementById("bindu_namavli").value = category;
});

// Auto-fill category on page load for new entries
window.addEventListener('load', function() {
    const binduKramaank = document.getElementById("bindu_kramaank");
    if (binduKramaank && !binduKramaank.readOnly) {
        const event = new Event('input');
        binduKramaank.dispatchEvent(event);
    }
});

// Search function - Only searches in बिंदू क्रामांक column (2nd column, index 1)
function searchTable() {
    const input = document.getElementById("searchInput");
    const filter = input.value.toLowerCase();
    const table = document.getElementById("dataTable");
    const tr = table.getElementsByTagName("tr");
    
    let visibleCount = 0;
    
    // Loop through all table rows (starting from index 1 to skip header)
    for (let i = 1; i < tr.length; i++) {
        let td = tr[i].getElementsByTagName("td");
        let found = false;
        
        // Only search in बिंदू क्रामांक column (2nd column, index 1)
        if (td[1]) {
            let txtValue = td[1].textContent || td[1].innerText;
            if (txtValue.toLowerCase().indexOf(filter) > -1) {
                found = true;
            }
        }
        
        if (found) {
            tr[i].style.display = "";
            visibleCount++;
        } else {
            tr[i].style.display = "none";
        }
    }
    
    // Update entries count
    document.getElementById("entriesCount").textContent = `Showing ${visibleCount} of ${tr.length - 1} entries`;
}

// Print function
function printTable() {
    window.print();
}

// Initialize entries count on page load
window.addEventListener('load', function() {
    const table = document.getElementById("dataTable");
    if (table) {
        const tr = table.getElementsByTagName("tr");
        document.getElementById("entriesCount").textContent = `Showing ${tr.length - 1} of ${tr.length - 1} entries`;
    }
});
</script>

</body>
</html>