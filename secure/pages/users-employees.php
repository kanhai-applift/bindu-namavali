<?php
require_once(__DIR__ . '/../includes/auth.php');
require_superadmin();

require_once(__DIR__ . '/../config/db.php');

$userHash = $segments[1];
$designationHash = $segments[2];

// Decode user ID
$decodedUser = $hashids->decode($userHash);
$decodedDesignation = $hashids->decode($designationHash);

if (empty($decodedUser) || empty($decodedDesignation)) {
  exit('Invalid User/Designation Data');
}

$orgId  = (int)$decodedUser[0];
$designationId  = (int)$decodedDesignation[0];

// Fetch designations
$sql = "SELECT office_name
        FROM users
        WHERE id = ?";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $orgId);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();

// Validate designation belongs to organization
$sql = "SELECT designation_name
        FROM designations
        WHERE id = ? AND organization_id = ?";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('ii', $designationId, $orgId);
$stmt->execute();
$result = $stmt->get_result();
$designation = $result->fetch_assoc();
$stmt->close();

if (!$designation) {
  exit('Designation not found');
}

// Fetch the list of users
$sql = "SELECT * FROM employees WHERE designation_id = ? AND organization_id = ?";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('ii', $designationId, $orgId);
$stmt->execute();
$entries = $stmt->get_result();
$count = 1;
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<link rel="stylesheet" href="<?= baseUrl('assets/css/users-employees.css') ?>">

<div class="container-fluid user_emp">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">
      रिक्त/कामगार कर्मचाऱ्यांची यादी : <?= $user['office_name'] ?>
      <small class="text-muted sfs-2"> (Vaccant/Working employee list) </small>
    </h4>

    <div>
      <button class="btn btn-secondary" onclick="window.history.back()">
        <i class="bi bi-chevron-left"></i>
        मागे जा (Go Back)
      </button>
    </div>
  </div>

  <div class="border">

    <?php if ($entries && $entries->num_rows > 0): ?>
      <table id="notebookTable" class="display table table-sm table-striped" style="width:100%">
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
          <?php while ($row = $entries->fetch_assoc()):
            // Collect PDF files for this row
            $pdf_files = [];
            $col = 'pdf';
            $label = 'pdf';
            // 1. Check if the specific column exists and is a valid PDF
            if (!empty($row[$col]) && $row[$col] !== 'NULL' && $row[$col] !== '' && strtolower(pathinfo($row[$col], PATHINFO_EXTENSION)) === 'pdf') {

              $file_name = trim($row[$col]);
              $base_upload_dir = __DIR__ . "/../";

              // 2. Define the full system path for file_exists check
              // Note: You likely want to check if the specific file exists, not just the directory
              $full_system_path = $base_upload_dir . $file_name;

              $pdf_files[] = [
                'name'   => $file_name,
                'url'    => baseUrl($file_name),
                'path'   => __DIR__ . "/../", // The folder path
                'type'   => $col,
                'label'  => $label,
                'exists' => file_exists($full_system_path)
              ];
            }
          ?>
            <tr>
              <td><?= e($row['id']) ?></td>
              <td><?= e($row['bindu_no']) ?></td>
              <td><?= e($row['bindu_category']) ?></td>
              <td><?= e($row['employee_name']) ?></td>
              <td><?= e($row['employee_caste']) . ' ' . e($row['employee_category']) ?></td>
              <td><?= e($row['date_of_appointment']) ?></td>
              <td><?= e($row['date_of_birth']) ?></td>
              <td><?= e($row['date_of_retirement']) ?></td>
              <td>
                <div class="pdf-links-container">
                  <?php if (!empty($pdf_files)): ?>
                    <?php foreach ($pdf_files as $pdf): ?>
                      <div class="pdf-link-items">
                        <!-- <div class="pdf-link-title"><?= $pdf['label'] ?></div> -->
                        <div class="pdf-link-buttons">
                          <?php if ($pdf['exists']): ?>
                            <!-- <a href="#" class="pdf-viewer-btn"
                              onclick="viewPDF('<?= urlencode($pdf['url']) ?>', '<?= addslashes($pdf['name']) ?>')">
                              👁️ View
                            </a> -->
                            <a href="<?= $pdf['url'] ?>" class="btn btn-sm btn-info" target="_blank">View</a>
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
              <td><?= e($row['remarks']) ?></td>
              <td><?= ($row['working'] ? "✅ होय" : "❌ नाही") ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>⚠️ या Notebook मध्ये कोणतेही Entries नाहीत.</p>
    <?php endif; ?>
  </div>

</div>

<!-- PDF Viewer Modal -->
<div id="pdfModal" class="pdf-modal">
  <div class="pdf-modal-content">
    <!-- <span class="close-modal" onclick="closePDFModal()">&times;</span> -->
    <iframe id="pdfViewer" class="pdf-viewer"></iframe>
  </div>
</div>

<?php
$page_scripts = [
  "https://code.jquery.com/jquery-3.7.0.min.js",
  'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js',
  "https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js",
  "https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js",
  "https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js",
  "https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js",
];

$inline_scripts = <<<JS
  $(function () {
    document.getElementById('sidebar').classList.toggle('collapsed');

    $('#notebookTable').DataTable({
        pageLength: 100,
        order: [[0, 'desc']], // Your current sorting
        lengthMenu: [10, 25, 50, 100],
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
    console.log('the filepath ',filePath);
    
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
          log('the blobUrl', blobUrl);
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
JS;
?>