<?php
require_once(__DIR__ . '/../includes/auth.php');
require_superadmin();

require_once(__DIR__ . '/../config/db.php');

$userHash = $segments[1];

// Decode user ID
$decodedUser = $hashids->decode($userHash);

if (empty($decodedUser)) {
  exit('Invalid User Data');
}

$orgId  = (int)$decodedUser[0];

// Fetch designations
$sql = "SELECT office_name
        FROM users
        WHERE id = ?";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $orgId);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();


// Fetch designations
$sql = "SELECT id, designation_name, description, created_at
        FROM designations
        WHERE organization_id = ?
        ORDER BY designation_name ASC";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $orgId);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container-fluid">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">
      जतन केलेल्या पदनामांची यादी : <?= $user['office_name'] ?>
      <small class="text-muted sfs-2">  (Designations under this user) </small>
    </h4>

    <div>
      <button class="btn btn-secondary" onclick="window.history.back()">
        <i class="bi bi-chevron-left"></i>
        मागे जा (Go Back)
      </button>
    </div>
  </div>

  <div id="alertBox"></div>

  <table id="designationTable" class="table table-bordered table-striped">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Designation Name</th>
        <th>Description</th>
        <th>Created On</th>
        <th>Action</th>
      </tr>
    </thead>

    <tbody>
      <?php $i = 1; ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= e($row['designation_name']) ?></td>
          <td><?= e($row['description']) ?> </td>
          <td><?= date('d-m-Y', strtotime($row['created_at'])) ?></td>
          <td>
            <a href="<?= baseUrl('users-employees/'. $userHash .'/' . $hashids->encode($row['id'])) ?>"
              class="btn btn-sm btn-primary">
              <i class="bi bi-display"></i>
            </a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

</div>

<?php
$stmt->close();

$csrf_token = csrf_token();

$page_scripts = [
  'https://code.jquery.com/jquery-3.7.1.min.js',
  'https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.3.6/b-3.2.6/datatables.min.js',
];

$inline_scripts = <<<JS
  $(document).ready(function () {

    $('#designationTable').DataTable({
      pageLength: 10,
      order: [[0, 'desc']], // Your current sorting
      lengthMenu: [10, 25, 50, 100],
      lengthChange: true,
      searching: true,
      ordering: true,
      info: true,
      autoWidth: false,
      columnDefs: [
        { orderable: false, targets: 4 } // Disable sort on Action column
      ]
    });

  });  
JS;
?>