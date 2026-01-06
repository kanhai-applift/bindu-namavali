<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/ho/hostel/includes/config.php');
include('includes/checklogin.php');
check_login();

$user_id   = $_SESSION['id']; 
$post_name = isset($_GET['post']) ? trim($_GET['post']) : "";

if (empty($post_name)) {
    die("⚠️ Post not selected!");
}

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
    } else {
    }
}

// Function to get category name from bindu_kramaank
function getCategoryName($bindu_kramaank) {
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

    if (in_array($bindu_kramaank, $sc))       return "अनुसूचित जाती";
    else if (in_array($bindu_kramaank, $st))  return "अनुसूचित जमाती";
    else if (in_array($bindu_kramaank, $vjA)) return "विमुक्त जमाती (अ)";
    else if (in_array($bindu_kramaank, $bjB)) return "भटक्या जमाती (ब)";
    else if (in_array($bindu_kramaank, $bjC)) return "भटक्या जमाती (क)";
    else if (in_array($bindu_kramaank, $bjD)) return "भटक्या जमाती (ड)";
    else if (in_array($bindu_kramaank, $smp)) return "विशेष मागास प्रवर्ग";
    else if (in_array($bindu_kramaank, $obc)) return "इतर मागास प्रवर्ग";
    else if (in_array($bindu_kramaank, $ssmv)) return "सामाजिक आणि शैक्षणिक मागास वर्ग";
    else if (in_array($bindu_kramaank, $ews)) return "आर्थिक दृष्ट्या दुर्बल घटक";
    else if (in_array($bindu_kramaank, $open)) return "अराखीव";
    
    return "";
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

    // Auto-determine bindu_namavli based on bindu_kramaank
    $bindu_kramaank = $_POST['bindu_kramaank'];
    $bindu_namavli = getCategoryName($bindu_kramaank);

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
                $_POST['pad_niyukt_dinank'], $_POST['janma_tarik'], $_POST['sevaniroti_dinank'],
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
                $_POST['pad_niyukt_dinank'], $_POST['janma_tarik'], $_POST['sevaniroti_dinank'],
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
            $_POST['pad_niyukt_dinank'], $_POST['janma_tarik'], $_POST['sevaniroti_dinank'],
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
$result = $mysqli->query("SELECT * FROM `$table_name` ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="mr">
<head>
<meta charset="UTF-8">
<title>Notebook - <?= htmlspecialchars($post_name) ?></title>
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
        padding: 6px 12px; 
        border: none; 
        border-radius: 4px; 
        cursor: pointer; 
        font-size: 12px;
        text-decoration: none;
        display: inline-block;
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
        width: 50px !important;
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
        gap: 10px;
        margin-bottom: 15px;
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
    
    <div class="top-buttons">
        <a href="post_entry.php" class="back-btn">⬅ Back to Posts</a>
        <a href="user-post.php?post_name=<?= urlencode($post_name) ?>" class="user-post-btn" target="_blank">➕ Add User to This Post</a>
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
                                <input type="text" id="bindu_kramaank" name="bindu_kramaank" required class="bindu-kramaank" maxlength="3" value="<?= htmlspecialchars($edit_data['bindu_kramaank']) ?>">
                            <?php else: ?>
                                <input type="text" id="bindu_kramaank" name="bindu_kramaank" readonly class="bindu-kramaank" value="<?= $next_bindu_kramaank ?>">
                            <?php endif; ?>
                        </td>
                        <td><input type="text" id="bindu_namavli" name="bindu_namavli" readonly value="<?= $edit_mode ? htmlspecialchars($edit_data['bindu_namavli']) : $initial_category ?>"></td>
                        <td><textarea name="karmachari_naam" required class="karmachari-naam" rows="2"><?= $edit_mode ? htmlspecialchars($edit_data['karmachari_naam']) : '' ?></textarea></td>
                        <td><input type="text" name="karmachari_jat" class="karmachari-jat" value="<?= $edit_mode ? htmlspecialchars($edit_data['karmachari_jat']) : '' ?>"></td>
                        <td><input type="date" name="pad_niyukt_dinank" value="<?= $edit_mode ? $edit_data['pad_niyukt_dinank'] : '' ?>"></td>
                        <td><input type="date" name="janma_tarik" value="<?= $edit_mode ? $edit_data['janma_tarik'] : '' ?>"></td>
                        <td><input type="date" name="sevaniroti_dinank" value="<?= $edit_mode ? $edit_data['sevaniroti_dinank'] : '' ?>"></td>
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
                        <td><?= $row['pad_niyukt_dinank'] ?></td>
                        <td><?= $row['janma_tarik'] ?></td>
                        <td><?= $row['sevaniroti_dinank'] ?></td>
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
document.getElementById("bindu_kramaank").addEventListener("input", function() {
    const val = parseInt(this.value, 10);

    // Categories mapping
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