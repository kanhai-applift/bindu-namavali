<?php
require_once(__DIR__ . '/../includes/auth.php');
require_login();

require_once(__DIR__ . '/../config/db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
  http_response_code(403);
  exit('Access denied');
}

$orgId = $_SESSION['user_id'];

// Fetch users
$sql = "SELECT u.*, d.district_name as district
        FROM users u
        LEFT JOIN districts d ON d.id = u.district_id
        WHERE role = 'admin'
        ORDER BY id DESC";

$stmt = $mysqli->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

?>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>
      वापरकर्त्यांनी जतन केलेल्या पदनामांची यादी
      <small class="text-muted sfs-2">(Designations saved under users)</small>
    </h4>
    
    <div>
      <button class="btn btn-secondary" onclick="window.history.back()">
        <i class="bi bi-chevron-left"></i>
        मागे जा (Go Back)
      </button>
    </div>

  </div>

  <table id="usersTable" class="table table-bordered table-striped w-100">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Registration No</th>
        <th>Organisation</th>
        <th>Head Name</th>
        <th>Email/Phone</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 1; ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= e($row['id']) ?></td>
          <td><?= e($row['registration_no']) ?></td>
          <td><?= e($row['office_name']) ?></td>
          <td><?= e($row['head_name']) ?> </td>
          <td><?= e($row['email']) ?> <br> <?= e($row['contact_no']) ?> </td>
          <td>
            <a href="<?= baseUrl('users-designations/' . $hashids->encode($row['id'])) ?>"
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
  'https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.3.6/b-3.2.6/datatables.min.js',
];

$inline_scripts = <<<JS
    
  $(document).ready(function () {

    $('#usersTable').DataTable({
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