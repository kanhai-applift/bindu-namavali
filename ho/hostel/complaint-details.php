<?php
session_start();
include('includes/config.php');

if(!isset($_GET['cid'])){
    echo "<script>alert('Invalid Request');window.location='new-complaints.php';</script>";
    exit;
}

$cid = intval($_GET['cid']);

// ✅ Fetch complaint info - UPDATED to include all file fields
$ret="SELECT c.*, u.firstName, u.middleName, u.lastName, u.email 
      FROM complaints c
      JOIN userregistration u ON u.id = c.userId
      WHERE c.id=?";
$stmt= $mysqli->prepare($ret);
$stmt->bind_param('i',$cid);
$stmt->execute();
$res=$stmt->get_result();
$row=$res->fetch_object();

if(!$row){
    echo "<script>alert(' not found');window.location='new-complaints.php';</script>";
    exit;
}

// Define the upload directory path
$upload_dir = "comnplaintdoc/"; // Relative path from current directory

// ✅ Fetch user post table
$postName = $row->complaintType;
$stmt2 = $mysqli->prepare("SELECT * FROM user_posts WHERE user_id=? AND post_name=? ORDER BY 
    CASE category 
        WHEN 'मंजूर_पदे' THEN 1
        WHEN 'कार्यारत_पदे' THEN 2
        WHEN 'दिनांक_भरावयाची_पदे' THEN 3
        WHEN 'कालावधितील_संभाव्य_भरावयाची_पदे' THEN 4
        WHEN 'एकूण_भरावयाची_पदे' THEN 5
        WHEN 'अतिरिक्त_पदे' THEN 6
        ELSE 7
    END");
$stmt2->bind_param('is', $row->userId, $postName);
$stmt2->execute();
$posts = $stmt2->get_result();

// Store all rows in an array for processing
$post_data = [];
while($p = $posts->fetch_assoc()) {
    $post_data[] = $p;
}

// Separate categories for proper handling
$mfjur_pade = null;
$karyarat_pade = null;
$dinank_bharavayachi_pade = null;
$kalaavadhitil_sambhavy_pade = null;
$ekun_bharavayachi_pade = null;
$atirikt_pade = null;

foreach ($post_data as $p) {
    switch($p['category']) {
        case 'मंजूर_पदे':
            $mfjur_pade = $p;
            break;
        case 'कार्यारत_पदे':
            $karyarat_pade = $p;
            break;
        case 'दिनांक_भरावयाची_पदे':
            $dinank_bharavayachi_pade = $p;
            break;
        case 'कालावधितील_संभाव्य_भरावयाची_पदे':
            $kalaavadhitil_sambhavy_pade = $p;
            break;
        case 'एकूण_भरावयाची_पदे':
            $ekun_bharavayachi_pade = $p;
            break;
        case 'अतिरिक्त_पदे':
            $atirikt_pade = $p;
            break;
    }
}

// Calculate दिनांक_भरावयाची_पदे if not in database but मंजूर_पदे and कार्यारत_पदे exist
if (!$dinank_bharavayachi_pade && $mfjur_pade && $karyarat_pade) {
    $dinank_bharavayachi_pade = [
        'category' => 'दिनांक_भरावयाची_पदे',
        'col0' => $mfjur_pade['col0'] - $karyarat_pade['col0'],
        'col1' => $mfjur_pade['col1'] - $karyarat_pade['col1'],
        'col2' => $mfjur_pade['col2'] - $karyarat_pade['col2'],
        'col3' => $mfjur_pade['col3'] - $karyarat_pade['col3'],
        'col4' => $mfjur_pade['col4'] - $karyarat_pade['col4'],
        'col5' => $mfjur_pade['col5'] - $karyarat_pade['col5'],
        'col6' => $mfjur_pade['col6'] - $karyarat_pade['col6'],
        'col7' => $mfjur_pade['col7'] - $karyarat_pade['col7'],
        'col8' => $mfjur_pade['col8'] - $karyarat_pade['col8'],
        'col9' => $mfjur_pade['col9'] - $karyarat_pade['col9'],
        'col10' => $mfjur_pade['col10'] - $karyarat_pade['col10'],
        'total' => $mfjur_pade['total'] - $karyarat_pade['total']
    ];
}

// Calculate एकूण_भरावयाची_पदे if not in database
if (!$ekun_bharavayachi_pade && $dinank_bharavayachi_pade && $kalaavadhitil_sambhavy_pade) {
    $ekun_bharavayachi_pade = [
        'category' => 'एकूण_भरावयाची_पदे',
        'col0' => $dinank_bharavayachi_pade['col0'] + ($kalaavadhitil_sambhavy_pade['col0'] ?? 0),
        'col1' => $dinank_bharavayachi_pade['col1'] + ($kalaavadhitil_sambhavy_pade['col1'] ?? 0),
        'col2' => $dinank_bharavayachi_pade['col2'] + ($kalaavadhitil_sambhavy_pade['col2'] ?? 0),
        'col3' => $dinank_bharavayachi_pade['col3'] + ($kalaavadhitil_sambhavy_pade['col3'] ?? 0),
        'col4' => $dinank_bharavayachi_pade['col4'] + ($kalaavadhitil_sambhavy_pade['col4'] ?? 0),
        'col5' => $dinank_bharavayachi_pade['col5'] + ($kalaavadhitil_sambhavy_pade['col5'] ?? 0),
        'col6' => $dinank_bharavayachi_pade['col6'] + ($kalaavadhitil_sambhavy_pade['col6'] ?? 0),
        'col7' => $dinank_bharavayachi_pade['col7'] + ($kalaavadhitil_sambhavy_pade['col7'] ?? 0),
        'col8' => $dinank_bharavayachi_pade['col8'] + ($kalaavadhitil_sambhavy_pade['col8'] ?? 0),
        'col9' => $dinank_bharavayachi_pade['col9'] + ($kalaavadhitil_sambhavy_pade['col9'] ?? 0),
        'col10' => $dinank_bharavayachi_pade['col10'] + ($kalaavadhitil_sambhavy_pade['col10'] ?? 0)
    ];
    $ekun_bharavayachi_pade['total'] = array_sum(array_slice($ekun_bharavayachi_pade, 1, 11));
}

// Calculate अतिरिक्त_पदे if not in database
if (!$atirikt_pade && $ekun_bharavayachi_pade) {
    $atirikt_pade = [
        'category' => 'अतिरिक्त_पदे',
        'col0' => $ekun_bharavayachi_pade['col0'] < 0 ? abs($ekun_bharavayachi_pade['col0']) : 0,
        'col1' => $ekun_bharavayachi_pade['col1'] < 0 ? abs($ekun_bharavayachi_pade['col1']) : 0,
        'col2' => $ekun_bharavayachi_pade['col2'] < 0 ? abs($ekun_bharavayachi_pade['col2']) : 0,
        'col3' => $ekun_bharavayachi_pade['col3'] < 0 ? abs($ekun_bharavayachi_pade['col3']) : 0,
        'col4' => $ekun_bharavayachi_pade['col4'] < 0 ? abs($ekun_bharavayachi_pade['col4']) : 0,
        'col5' => $ekun_bharavayachi_pade['col5'] < 0 ? abs($ekun_bharavayachi_pade['col5']) : 0,
        'col6' => $ekun_bharavayachi_pade['col6'] < 0 ? abs($ekun_bharavayachi_pade['col6']) : 0,
        'col7' => $ekun_bharavayachi_pade['col7'] < 0 ? abs($ekun_bharavayachi_pade['col7']) : 0,
        'col8' => $ekun_bharavayachi_pade['col8'] < 0 ? abs($ekun_bharavayachi_pade['col8']) : 0,
        'col9' => $ekun_bharavayachi_pade['col9'] < 0 ? abs($ekun_bharavayachi_pade['col9']) : 0,
        'col10' => $ekun_bharavayachi_pade['col10'] < 0 ? abs($ekun_bharavayachi_pade['col10']) : 0
    ];
    $atirikt_pade['total'] = array_sum(array_slice($atirikt_pade, 1, 11));
}
?>
<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <title>प्रकरण  Details</title>
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        table, th, td { border: 1px solid black; border-collapse: collapse; text-align:center; padding:8px; font-size:14px; }
        th { background:#f2a65a; color:#000; }
        td:first-child { font-weight:bold; background:#f9e7c4; text-align:left; padding-left:15px; }
        .percent-row { font-weight:bold; background:#e8f4f8; }
        .category-row { background:#fff; }
        .dinank-row { background:#fff3cd; }
        .kalaavadhitil-row { background:#e7f3ff; }
        .ekun-row { background:#d4edda; font-weight:bold; }
        .atirikt-row { background:#f8d7da; font-weight:bold; }
        .status-approved { color: #28a745; font-weight: bold; }
        .panel-heading { background:#007bff; color:white; }
        .negative-value { color: #dc3545; font-weight: bold; }
        .positive-value { color: #28a745; }
        .file-info { margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 4px; }
        .file-link { color: #007bff; text-decoration: underline; }
        .file-link:hover { color: #0056b3; text-decoration: none; }
        .file-item { margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #dee2e6; }
        .file-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .file-preview { max-width: 300px; max-height: 200px; border: 1px solid #ddd; padding: 5px; margin-top: 5px; }
        .file-type-badge { 
            background-color: #6c757d; 
            color: white; 
            padding: 2px 6px; 
            border-radius: 3px; 
            font-size: 12px; 
            margin-left: 5px; 
        }
    </style>
</head>
<body>
<?php include('includes/header.php');?>
<div class="ts-main-content">
    <?php include('includes/sidebar.php');?>
    <div class="content-wrapper">
        <div class="container-fluid">
            <div class="row" id="print">
                <div class="col-md-12">
                    <h2 class="page-title" style="margin-top:3%">प्रकरण क्रमांक #<?php echo $row->ComplainNumber;?> Details</h2>
                    
                    <!-- ✅ User Info -->
                    <div class="panel panel-primary">
                        <div class="panel-heading">User Information</div>
                        <div class="panel-body">
                            <p><strong>Name:</strong> <?php echo trim($row->firstName.' '.$row->middleName.' '.$row->lastName); ?></p>
                            <p><strong>Email:</strong> <?php echo $row->email; ?></p>
                            <p><strong>प्रकरण क्रमांक:</strong> <?php echo $row->ComplainNumber; ?></p>
                            <p><strong>पदाचे नाव:</strong> <?php echo $row->complaintType; ?></p>
                            <p><strong>Status:</strong> <span class="status-approved"><?php echo $row->complaintStatus ?: "New"; ?></span></p>
                            <p><strong>Registration Date:</strong> <?php echo $row->registrationDate; ?></p>
                            <p><strong>प्रकरण माहिती:</strong> <?php echo $row->complaintDetails; ?></p>
                            
                            <!-- ✅ FILE 1 Information -->
                            <div class="file-info">
                                <h5><strong>File 1:</strong></h5>
                                <?php 
                                $files = [
                                    'File 1' => $row->complaintDoc,
                                    'File 2' => $row->complaintDoc2 ?? null,
                                    'File 3' => $row->complaintDoc3 ?? null
                                ];
                                
                                $file_count = 1;
                                foreach($files as $file_label => $file_name):
                                    if($file_name == '' || empty($file_name) || $file_name === null): 
                                ?>
                                    <div class="file-item">
                                        <p><strong><?php echo $file_label; ?>:</strong> NA (No file uploaded)</p>
                                    </div>
                                <?php
                                    else: 
                                        $file_path = $upload_dir . $file_name;
                                        
                                        // Check if file exists
                                        if(file_exists($file_path)) {
                                            // Get file extension
                                            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                                            $file_display_name = htmlspecialchars($file_name);
                                           
                                            // Check file type
                                            $is_image = in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp']);
                                            $is_pdf = ($file_extension == 'pdf');
                                            $is_document = in_array($file_extension, ['doc', 'docx', 'txt', 'rtf']);
                                            
                                            echo "<div class='file-item'>";
                                            echo "<p><strong>$file_label:</strong></p>";
                                            echo "<p><strong>File Name:</strong> $file_display_name <span class='file-type-badge'>" . strtoupper($file_extension) . "</span></p>";
                                            echo "<p><strong>File Size:</strong> $file_size_formatted</p>";
                                            
                                            // Create appropriate links
                                            echo "<p><strong>Actions:</strong> ";
                                            
                                            if ($is_image) {
                                                // For images, show preview and download
                                                echo "<a href='$file_path' class='file-link' target='_blank'>View Image</a> | ";
                                            } elseif ($is_pdf) {
                                                // For PDFs
                                                echo "<a href='$file_path' class='file-link' target='_blank'>View PDF</a> | ";
                                            }
                                            
                                            // Always show download link
                                            echo "<a href='$file_path' class='file-link' download>Download</a>";
                                            echo "</p>";
                                            
                                            // Show image preview
                                            if ($is_image) {
                                                echo "<div>";
                                                echo "<strong>Preview:</strong><br>";
                                                echo "<img src='$file_path' alt='$file_label' class='file-preview'>";
                                                echo "</div>";
                                            } elseif ($is_pdf) {
                                                echo "<div>";
                                                echo "<strong>PDF Preview:</strong><br>";
                                                echo "<iframe src='$file_path#view=fitH' width='300' height='200' style='border: 1px solid #ddd;'></iframe>";
                                                echo "</div>";
                                            }
                                            
                                            echo "</div>";
                                            
                                            // Debug info (remove in production)
                                            if(isset($_GET['debug'])) {
                                                echo "<small style='color: #666;'>";
                                                echo "Full path: " . realpath($file_path) . "<br>";
                                                echo "File permissions: " . substr(sprintf('%o', fileperms($file_path)), -4);
                                                echo "</small>";
                                            }
                                        } else {
                                            echo "<div class='file-item'>";
                                            echo "<p><strong>$file_label:</strong> File not found: $file_name</p>";
                                            echo "<small>Please check if the file exists in the upload directory.</small>";
                                            echo "</div>";
                                        }
                                    endif;
                                    
                                    $file_count++;
                                endforeach; 
                                ?>
                                
                                <?php 
                                // Count uploaded files
                                $uploaded_files = 0;
                                foreach($files as $file_name) {
                                    if($file_name != '' && !empty($file_name) && $file_name !== null) {
                                        $uploaded_files++;
                                    }
                                }
                                ?>
                                <p><strong>Total Files Uploaded:</strong> <?php echo $uploaded_files; ?> out of 3</p>
                            </div>
                        </div>
                    </div>

                    <!-- ✅ User Post Table -->
                    <?php if(count($post_data) > 0 || $mfjur_pade): ?>
                    <div class="panel panel-primary">
                        <div class="panel-heading">पदांची माहिती (Post Information)</div>
                        <div class="panel-body">
                            <table class="table table-bordered" style="width:100%;">
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

                                <!-- ✅ Fixed Percentage Row -->
                                <tr class="percent-row">
                                    <td>प्रतिशत (%)</td>
                                    <td>13%</td>
                                    <td>7%</td>
                                    <td>3%</td>
                                    <td>2.5%</td>
                                    <td>3.5%</td>
                                    <td>2%</td>
                                    <td>2%</td>
                                    <td>19%</td>
                                    <td>10%</td>
                                    <td>10%</td>
                                    <td>28%</td>
                                    <td>100%</td>
                                </tr>

                                <!-- ✅ मंजूर_पदे -->
                                <?php if($mfjur_pade): ?>
                                <tr class="category-row">
                                    <td><?php echo $mfjur_pade['category']; ?></td>
                                    <?php for($i=0; $i<=10; $i++): ?>
                                    <td><?php echo $mfjur_pade['col'.$i]; ?></td>
                                    <?php endfor; ?>
                                    <td><?php echo $mfjur_pade['total']; ?></td>
                                </tr>
                                <?php endif; ?>

                                <!-- ✅ कार्यारत_पदे -->
                                <?php if($karyarat_pade): ?>
                                <tr class="category-row">
                                    <td><?php echo $karyarat_pade['category']; ?></td>
                                    <?php for($i=0; $i<=10; $i++): ?>
                                    <td><?php echo $karyarat_pade['col'.$i]; ?></td>
                                    <?php endfor; ?>
                                    <td><?php echo $karyarat_pade['total']; ?></td>
                                </tr>
                                <?php endif; ?>

                                <!-- ✅ दिनांक_भरावयाची_पदे (मंजूर - कार्यारत) -->
                                <?php if($dinank_bharavayachi_pade): ?>
                                <tr class="dinank-row">
                                    <td><?php echo $dinank_bharavayachi_pade['category']; ?></td>
                                    <?php for($i=0; $i<=10; $i++): 
                                        $value = $dinank_bharavayachi_pade['col'.$i];
                                        $class = $value < 0 ? 'negative-value' : 'positive-value';
                                    ?>
                                    <td class="<?php echo $class; ?>"><?php echo $value; ?></td>
                                    <?php endfor; ?>
                                    <?php 
                                    $dinank_total = $dinank_bharavayachi_pade['total'];
                                    $total_class = $dinank_total < 0 ? 'negative-value' : 'positive-value';
                                    ?>
                                    <td class="<?php echo $total_class; ?>"><?php echo $dinank_total; ?></td>
                                </tr>
                                <?php endif; ?>

                                <!-- ✅ कालावधितील_संभाव्य_भरावयाची_पदे -->
                                <?php if($kalaavadhitil_sambhavy_pade): ?>
                                <tr class="kalaavadhitil-row">
                                    <td><?php echo $kalaavadhitil_sambhavy_pade['category']; ?></td>
                                    <?php for($i=0; $i<=10; $i++): ?>
                                    <td><?php echo $kalaavadhitil_sambhavy_pade['col'.$i]; ?></td>
                                    <?php endfor; ?>
                                    <td><?php echo $kalaavadhitil_sambhavy_pade['total']; ?></td>
                                </tr>
                                <?php endif; ?>

                                <!-- ✅ एकूण_भरावयाची_पदे (दिनांक + कालावधितील) -->
                                <?php if($ekun_bharavayachi_pade): ?>
                                <tr class="ekun-row">
                                    <td><?php echo $ekun_bharavayachi_pade['category']; ?></td>
                                    <?php for($i=0; $i<=10; $i++): 
                                        $value = $ekun_bharavayachi_pade['col'.$i];
                                        $class = $value < 0 ? 'negative-value' : 'positive-value';
                                    ?>
                                    <td class="<?php echo $class; ?>"><?php echo $value; ?></td>
                                    <?php endfor; ?>
                                    <?php 
                                    $ekun_total = $ekun_bharavayachi_pade['total'];
                                    $total_class = $ekun_total < 0 ? 'negative-value' : 'positive-value';
                                    ?>
                                    <td class="<?php echo $total_class; ?>"><?php echo $ekun_total; ?></td>
                                </tr>
                                <?php endif; ?>

                                <!-- ✅ अतिरिक्त_पदे (for negative values in एकूण) -->
                                <?php if($atirikt_pade && $atirikt_pade['total'] > 0): ?>
                                <tr class="atirikt-row">
                                    <td><?php echo $atirikt_pade['category']; ?></td>
                                    <?php for($i=0; $i<=10; $i++): ?>
                                    <td><?php echo $atirikt_pade['col'.$i]; ?></td>
                                    <?php endfor; ?>
                                    <td><?php echo $atirikt_pade['total']; ?></td>
                                </tr>
                                <?php endif; ?>

                            </table>
                            
                            <!-- ✅ Additional Information -->
                            <div class="well" style="margin-top:20px; background:#f8f9fa; padding:15px;">
                                <h4>टीप / Notes:</h4>
                                <ul>
                                    
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <strong>No post data found</strong> for this complaint. The user may not have submitted post information yet.
                    </div>
                    <?php endif; ?>

                    <!-- ✅ Action Buttons (Approve button removed) -->
                    <div class="panel panel-default">
                        <div class="panel-heading">Actions</div>
                        <div class="panel-body">
                            <button class="btn btn-primary" onclick="window.print()">🖨️ Print</button>
                            <a href="new-complaints.php" class="btn btn-default">Back to List</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>

<?php
// Helper function to format file size
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
        $bytes = $bytes . ' byte';
    } else {
        $bytes = '0 bytes';
    }
    return $bytes;
}
?>