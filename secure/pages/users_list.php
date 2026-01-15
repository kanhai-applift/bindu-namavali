<?php
require_once(__DIR__ . '/../includes/auth.php');
require_once(__DIR__ . '/../config/db.php');

require_superadmin();

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
    <h4>Users</h4>
    <a href="<?= baseUrl('dashboard') ?>" class="btn btn-secondary">
      <i class="bi bi-chevron-left"></i> Back to Dashboard
    </a>
  </div>

  <table id="usersTable" class="table table-bordered table-striped w-100">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Registration No</th>
        <th>Organisation</th>
        <th>Head Name</th>
        <th>District</th>
        <th>Contact</th>
        <th>Email</th>
        <th>Created At</th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 1; ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= e($row['registration_no']) ?></td>
          <td><?= e($row['office_name']) ?></td>
          <td><?= e($row['head_name']) ?> </td>
          <td><?= e($row['district']) ?> </td>
          <td><?= e($row['contact_no']) ?> </td>
          <td><?= e($row['email']) ?> </td>
          <td><?= date('d-m-Y', strtotime($row['created_at'])) ?></td>
          <!-- <td>
            <a href="<?= baseUrl('employees-add/' . $hashids->encode($row['id'])) ?>"
              class="btn btn-sm btn-primary">
              <i class="bi bi-display"></i>
            </a>

            <a href="<?= baseUrl('designations-edit/' . $hashids->encode($row['id'])) ?>"
              class="btn btn-sm btn-warning">
              <i class="bi bi-pencil"></i>
            </a>

            <button class="btn btn-sm btn-danger"
              onclick="deleteDesignation('<?= $hashids->encode($row['id']) ?>')">
              <i class="bi bi-trash3"></i>
            </button>
          </td> -->
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
      pageLength: 50,
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