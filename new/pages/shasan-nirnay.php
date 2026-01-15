<?php
require_once(__DIR__ . '/../includes/auth.php');
require_superadmin();

require_once(__DIR__ . '/../config/db.php');

$stmt = $mysqli->prepare("
    SELECT *
    FROM shasan_nirnay
    ORDER BY id DESC
");
$stmt->execute();
$result = $stmt->get_result();
$count = 1;
?>

<div class="container-fluid mt-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-3">
      शासन निर्णय यादी
      <small class="text-muted sfs-2">(Shasan Nirnay List)</small>
    </h4>

    <div>
      <a href="<?= baseUrl('dashboard/') ?>" class="btn btn-secondary">
        <i class="bi bi-chevron-left"></i> Back to Dashboard
      </a>
      <a href="<?= baseUrl('shasan-nirnay-add/') ?>" class="btn btn-primary">
        <i class="bi bi-plus"></i> Add New
      </a>
    </div>
  </div>

  <table id="listTable" class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>क्र. क्र.</th>
        <th>अंमलबजावणीची तारीख</th>
        <th>सरकारी ठराव/परिपत्रक</th>
        <th>विषय</th>
        <th>PDF</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= e($row['kr_no']) ?></td>
          <td><?= formatDate(e($row['amal_tarik'])) ?></td>
          <td><?= e($row['gr_no']) ?></td>
          <td><?= e($row['vishay']) ?></td>
          <td>
            <?php if ($row['pdf_file']): ?>
              <a href="<?= baseUrl(e($row['pdf_file'])) ?>" target="_blank">
                <i class="bi bi-file-pdf"></i> पहा
              </a>
            <?php endif; ?>
          </td>
          <td>
            <a href="<?= baseUrl('shasan-nirnay-edit/' . $hashids->encode($row['id'])) ?>"
              class="btn btn-sm btn-warning">
              <i class="bi bi-pencil"></i>
            </a>
            <button class="btn btn-sm btn-danger"
              onclick="deleteShasanNirnay('<?= $hashids->encode($row['id']) ?>')">
              <i class="bi bi-trash3"></i>
            </button>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php
$page_scripts = [
  'https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.3.6/b-3.2.6/datatables.min.js',
];

$csrf_token = csrf_token();

$inline_scripts = <<<JS
  $(document).ready(function () {
      $('#listTable').DataTable({
          pageLength: 10,
          order: [[5, 'desc']],
          lengthMenu: [10, 25, 50, 100],
          responsive: true
      });
  });

    function deleteShasanNirnay(id) {
    if (!confirm('Are you sure you want to delete this Shasan Nirnay?')) {
      return;
    }

    $.ajax({
      url: baseUrl('api/shasan-nirnay-delete'),
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
JS;
?>