<?php
session_start();
include('../includes/config.php');
include('../includes/checklogin.php');
check_login();

$uid = intval($_GET['uid']);
$post = $_GET['post'];
$table_name = "notebook_" . $uid . "_" . $mysqli->real_escape_string($post);

$entries = $mysqli->query("SELECT * FROM `$table_name` ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="mr">
<head>
<meta charset="UTF-8">
<title>Notebook</title>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
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
        margin-top: 0;
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
    .pdf-viewer-btn {
        background: #3498db;
        color: white;
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        font-size: 12px;
        margin: 2px;
        white-space: nowrap;
    }
    .pdf-viewer-btn:hover {
        background: #2980b9;
    }
    .pdf-download-btn {
        background: #2ecc71;
        color: white;
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        font-size: 12px;
        margin: 2px;
        white-space: nowrap;
    }
    .pdf-download-btn:hover {
        background: #27ae60;
    }
    .no-pdf {
        color: #999;
        font-style: italic;
        font-size: 12px;
    }
    .pdf-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.8);
    }
    .pdf-modal-content {
        position: relative;
        margin: 2% auto;
        padding: 20px;
        width: 95%;
        height: 90%;
        background: white;
        border-radius: 8px;
    }
    .close-modal {
        position: absolute;
        right: 20px;
        top: 10px;
        font-size: 30px;
        font-weight: bold;
        color: #333;
        cursor: pointer;
        z-index: 1001;
        background: white;
        width: 40px;
        height: 40px;
        text-align: center;
        line-height: 40px;
        border-radius: 50%;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    .close-modal:hover {
        background: #f1f1f1;
    }
    .pdf-viewer {
        width: 100%;
        height: 100%;
        border: none;
        border-radius: 4px;
    }
    .pdf-info {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
        font-size: 14px;
        color: #666;
        border-left: 4px solid #3498db;
    }
    .pdf-links-container {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 200px;
    }
    .pdf-link-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
        padding: 8px;
        background: #f8f9fa;
        border-radius: 4px;
        border-left: 3px solid #3498db;
    }
    .pdf-link-title {
        font-weight: bold;
        font-size: 12px;
        color: #2c3e50;
        margin-bottom: 4px;
    }
    .pdf-link-buttons {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }
    table.dataTable tbody td {
        vertical-align: middle;
    }
    .dataTables_wrapper {
        margin-top: 20px;
    }
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .container {
            padding: 10px;
            margin: 10px;
        }
        .pdf-modal-content {
            width: 98%;
            height: 95%;
            margin: 1% auto;
            padding: 10px;
        }
    }
</style>
</head>
<body>

<div class="container">
    <a href="user_posts.php?uid=<?= $uid ?>" class="back-btn">⬅️ Back to Posts</a>
    
    <h2>📑 Notebook: <?= htmlspecialchars($post) ?></h2>
    
    <div class="pdf-info">
        📄 <strong>PDF Files:</strong> Click "View" to open PDF in viewer or "Download" to save the file. 
        Files are stored in the database and can be viewed directly in your browser.
    </div>
    
    <?php if ($entries && $entries->num_rows > 0): ?>
        <table id="notebookTable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>बिंदू क्रामांक</th>
                    <th>बिंदू नामावली</th>
                    <th>कर्मचार्यांचे नाव</th>
                    <th>कर्मचारी जात</th>
                    <th>पद नियुक्त दिनांक</th>
                    <th>जन्मतारीख</th>
                    <th>सेवानिवृत्ती दिनांक</th>
                    <th>PDF Files</th>
                    <th>शेरा</th>
                    <th>कार्यरत</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $entries->fetch_assoc()): 
                    // Collect PDF files for this row
                    $pdf_files = [];
                    $pdf_columns = [
                        'jat_pramanpatra' => 'जात प्रमाणपत्र',
                        'jat_pramanpatra_pradikar' => 'प्राधिकाऱ्याचे पदनाव',
                        'jat_vaidhta_pramanpatra' => 'वैधता प्रमाणपत्र',
                        'jat_vaidhta_samiti' => 'वैधता समिती',
                        'pdf_file' => 'pdf',
                    ];
                    
                    foreach ($pdf_columns as $col => $label) {
                        if (!empty($row[$col]) && $row[$col] !== 'NULL' && $row[$col] !== '' && strtolower(pathinfo($row[$col], PATHINFO_EXTENSION)) === 'pdf') {
                            $file_name = trim($row[$col]);
                            
                            // Define base uploads directory - adjust this path according to your structure
                            $base_upload_dir = "../uploads/";
                            
                            // Try different possible paths
                            $possible_paths = [
                                $base_upload_dir . "notebook_pdfs/" . $file_name,
                                $base_upload_dir . "user_" . $uid . "/" . $file_name,
                                $base_upload_dir . "notebooks/" . $table_name . "/" . $file_name,
                                $base_upload_dir . $file_name
                            ];
                            
                            $file_path = null;
                            foreach ($possible_paths as $path) {
                                if (file_exists($path)) {
                                    $file_path = $path;
                                    break;
                                }
                            }
                            
                            // If file doesn't exist, we can still show the link assuming it might be in a different location
                            if (!$file_path) {
                                // Use a generic path or the first possible path
                                $file_path = $possible_paths[0];
                            }
                            
                            $pdf_files[] = [
                                'name' => $file_name,
                                'path' => $file_path,
                                'type' => $col,
                                'label' => $label,
                                'exists' => file_exists($file_path)
                            ];
                        }
                    }
                ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['bindu_kramaank'] ?></td>
                    <td><?= $row['bindu_namavli'] ?></td>
                    <td><?= $row['karmachari_naam'] ?></td>
                    <td><?= $row['karmachari_jat'] ?></td>
                    <td><?= $row['pad_niyukt_dinank'] ?></td>
                    <td><?= $row['janma_tarik'] ?></td>
                    <td><?= $row['sevaniroti_dinank'] ?></td>
                    <td>
                        <div class="pdf-links-container">
                            <?php if (!empty($pdf_files)): ?>
                                <?php foreach ($pdf_files as $pdf): ?>
                                    <div class="pdf-link-itemsssssssss">
                                        <!-- <div class="pdf-link-title"><?= $pdf['label'] ?></div> -->
                                        <div class="pdf-link-buttons">
                                            <?php if ($pdf['exists']): ?>
                                                <a href="#" class="pdf-viewer-btn" 
                                                   onclick="viewPDF('<?= urlencode($pdf['path']) ?>', '<?= addslashes($pdf['name']) ?>')">
                                                    👁️ View
                                                </a>
                                            <?php else: ?>
                                                <span class="no-pdf">File not found</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="no-pdf">No PDF files</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td><?= $row['shera'] ?></td>
                    <td><?= ($row['karyarat'] ? "✅ होय" : "❌ नाही") ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>⚠️ या Notebook मध्ये कोणतेही Entries नाहीत.</p>
    <?php endif; ?>
</div>

<!-- PDF Viewer Modal -->
<div id="pdfModal" class="pdf-modal">
    <div class="pdf-modal-content">
        <span class="close-modal" onclick="closePDFModal()">&times;</span>
        <iframe id="pdfViewer" class="pdf-viewer"></iframe>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script>
$(document).ready(function() {
    $('#notebookTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'print',
                text: '🖨️ Print',
                className: 'btn-print',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'colvis',
                text: '📊 Column Visibility',
                className: 'btn-colvis'
            },
            {
                extend: 'copy',
                text: '📋 Copy',
                className: 'btn-copy'
            },
            {
                extend: 'excel',
                text: '📊 Excel',
                className: 'btn-excel'
            }
        ],
        pageLength: 25,
        language: {
            search: "शोधा:",
            lengthMenu: "दाखवा _MENU_ नोंदी",
            info: "दाखवत आहे _START_ ते _END_ पैकी _TOTAL_ नोंदी",
            infoEmpty: "0 नोंदी आढळल्या",
            infoFiltered: "(एकूण _MAX_ नोंदींमधून)",
            paginate: {
                first: "पहिले",
                last: "शेवटचे",
                next: "पुढे",
                previous: "मागे"
            },
            buttons: {
                copyTitle: 'तक्त्याची नक्कल केली',
                copySuccess: {
                    _: '%d ओळी नक्कल केल्या',
                    1: '1 ओळ नक्कल केली'
                }
            }
        },
        columnDefs: [
            { width: "50px", targets: 0 }, // ID
            { width: "80px", targets: 1 }, // बिंदू क्रामांक
            { width: "120px", targets: 2 }, // बिंदू नामावली
            { width: "150px", targets: 3 }, // कर्मचार्यांचे नाव
            { width: "100px", targets: 4 }, // कर्मचारी जात
            { width: "100px", targets: [5, 6, 7] }, // Dates
            { width: "250px", targets: 8 }, // PDF Files
            { width: "150px", targets: 9 }, // शेरा
            { width: "80px", targets: 10 } // कार्यरत
        ],
        responsive: true
    });
});

// PDF Viewer Functions
function viewPDF(filePath, fileName) {
    // Decode the URL encoded path
    filePath = decodeURIComponent(filePath);
    
    // Encode the file path for URL
    const encodedPath = encodeURI(filePath);
    
    // Set the PDF viewer source
    // Use blob URL or direct file path based on browser support
    const pdfViewer = document.getElementById('pdfViewer');
    
    // Try to open as blob first (for same-origin files)
    fetch(filePath)
        .then(response => {
            if (response.ok) {
                return response.blob();
            }
            throw new Error('Network response was not ok.');
        })
        .then(blob => {
            const blobUrl = URL.createObjectURL(blob);
            pdfViewer.src = blobUrl;
        })
        .catch(error => {
            console.error('Error fetching PDF:', error);
            // Fallback to direct file path
            pdfViewer.src = filePath;
        });
    
    // Show the modal
    document.getElementById('pdfModal').style.display = 'block';
    
    // Store current title
    const originalTitle = document.title;
    document.title = "PDF Viewer - " + fileName;
    
    // Prevent body scrolling
    document.body.style.overflow = 'hidden';
    
    // Store original title to restore later
    pdfViewer.dataset.originalTitle = originalTitle;
}

function closePDFModal() {
    const modal = document.getElementById('pdfModal');
    const pdfViewer = document.getElementById('pdfViewer');
    
    // Hide modal
    modal.style.display = 'none';
    
    // Revoke blob URL to free memory
    if (pdfViewer.src.startsWith('blob:')) {
        URL.revokeObjectURL(pdfViewer.src);
    }
    
    // Clear iframe source
    pdfViewer.src = '';
    
    // Restore body scrolling
    document.body.style.overflow = 'auto';
    
    // Restore original title
    if (pdfViewer.dataset.originalTitle) {
        document.title = pdfViewer.dataset.originalTitle;
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('pdfModal');
    if (event.target == modal) {
        closePDFModal();
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closePDFModal();
    }
});

// Clean up blob URLs when page is unloaded
window.addEventListener('beforeunload', function() {
    const pdfViewer = document.getElementById('pdfViewer');
    if (pdfViewer && pdfViewer.src && pdfViewer.src.startsWith('blob:')) {
        URL.revokeObjectURL(pdfViewer.src);
    }
});
</script>

</body>
</html>