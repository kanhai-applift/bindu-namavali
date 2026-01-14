<?php

require_once(__DIR__ . '/../includes/auth.php');
require_login();

require_once(__DIR__ . '/../config/db.php');

$orgId = $_SESSION['user_id'];

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
    <h4 class="mb-0">Designations</h4>

    <div>
      <a href="<?= baseUrl('dashboard/') ?>" class="btn btn-secondary">
        <i class="bi bi-chevron-left"></i> Back to Dashboard
      </a>
      <a href="<?= baseUrl('designations-add') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add Designation
      </a>
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
  function deleteDesignation(id) {
    if (!confirm('Are you sure you want to delete this designation?')) {
      return;
    }

    
    $.ajax({
      url: baseUrl('api/designations-delete'),
      type: 'POST',
      dataType: 'json',
      data: {
        id: id,
        csrf_token: "{$csrf_token}"
      },
      success: function (res) {
        if (res.status === 'success') {
          location.reload();
        } else {
          
          $('#alertBox').html(
            '<div class="alert alert-success">'+res . message+'</div>'
          );
        }
      },
      error: function () {
        $('#alertBox').html(
          `<div class="alert alert-danger">Unable to process request.</div>`
        );
      }
    });
  }
  $(document).ready(function () {

    $('#designationTable').DataTable({
      pageLength: 10,
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