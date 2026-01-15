<?php
require_once(__DIR__ . '/../includes/auth.php');
require_login();

require_once(__DIR__ . '/../config/db.php');

$orgId = $_SESSION['user_id'];

/* 1️⃣ Validate hash from URL */
if (empty($segments[1]) || !is_string($segments[1])) {
  exit('Invalid request');
}

$hash = trim($segments[1]);

/* 2️⃣ Decode Hashid → numeric ID */
$decoded = $hashids->decode($hash);

if (empty($decoded)) {
  exit('Invalid designation');
}

$designationId = (int) $decoded[0];
$orgId = $_SESSION['user_id'];

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
?>

<div class="container-fluid">

  <?php
  include_once('employees-list.php');
  ?>

</div>


<?php
$page_scripts = [
  'https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.3.6/b-3.2.6/datatables.min.js',
  'https://code.jquery.com/ui/1.14.1/jquery-ui.js',
];

$inline_scripts = <<<JS
  $(function () {
    document.getElementById('sidebar').classList.toggle('collapsed');

    var t = $('#employeesTable').DataTable({
        pageLength: 50,
        order: [[1, 'asc']], // Your current sorting
        columnDefs: [{
            searchable: false,
            orderable: false,
            targets: 0, // Targets the first column
        }],
        lengthMenu: [10, 25, 50, 100],
        responsive: true
    });

    // Event listener to fill the serial number column
    t.on('draw.dt', function () {
      console.log('Event called draw.dt');
      
        var info = t.page.info();
        t.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
            cell.innerHTML = i + 1 + info.start;
        });
    });
  });
JS;
?>