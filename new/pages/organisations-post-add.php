<?php
require_once(__DIR__ . '/../includes/auth.php');
require_login();

require_once(__DIR__ . '/../config/db.php');

$orgId = $_SESSION['user_id'];

// $sql = "
// SELECT d.id, d.designation_name
// FROM designations d
// LEFT JOIN goshwara g
//     ON g.designation_id = d.id
//    AND g.is_deleted = 0
// WHERE d.organization_id = ?
// ORDER BY d.designation_name
// ";
$sql = "
SELECT 
    MAX(id) AS id, 
    organization_id, 
    designation_id, 
    MAX(designation_name) AS designation_name
FROM goshwara
WHERE organization_id = ? 
  AND is_deleted = 0
GROUP BY organization_id, designation_id
ORDER BY designation_name ASC;
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $orgId);
$stmt->execute();
$designations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-3">
      POST Registration
      <small class="text-muted sfs-2">(Post Registration)</small>
    </h4>
    <a href="<?= baseUrl('dashboard') ?>" class="btn btn-secondary">
      <i class="bi bi-chevron-left"></i> Back to Dashboard
    </a>
  </div>

  <div id="alertBox"></div>

  <div class="row justify-content-center border p-2">
    <div class="col-md-8 shadow p-4">

      <form id="orgPostForm" enctype="multipart/form-data" novalidate>
        <?= csrfField() ?>

        <!-- Designation -->
        <div class="mb-3">
          <label class="form-label">Designation *</label>
          <select name="designation_hash" class="form-select" required>
            <option value="">-- Select Designation --</option>
            <?php foreach ($designations as $d): ?>
              <option value="<?= $hashids->encode(e($d['designation_id'])) ?>">
                <?= e($d['designation_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <div class="invalid-feedback">Please select designation.</div>
        </div>

        <!-- Remarks -->
        <div class="mb-3">
          <label class="form-label">Remarks</label>
          <textarea name="remarks" class="form-control" rows="3"></textarea>
        </div>

        <!-- Service Access Rules -->
        <div class="mb-3">
          <label class="form-label">
            सेवा प्रवेश नियम
            <small class="text-muted sfs-3">Service Access Rules (PDF)</small>

          </label>
          <input type="file" name="service_rules_pdf" class="form-control"
                accept="application/pdf">
        </div>

        <!-- Layout -->
        <div class="mb-3">
          <label class="form-label">
            आकृतीबंध
            <small class="text-muted sfs-3">Layout / Pattern (PDF)</small>
          </label>
          <input type="file" name="layout_pdf" class="form-control"
                accept="application/pdf">
        </div>

        <!-- Goshwara -->
        <div class="mb-3">
          <label class="form-label">
            गोषवारा
            <small class="text-muted sfs-3">Goshwara (PDF)</small>
          </label>
          <input type="file" name="goshwara_pdf" class="form-control"
                accept="application/pdf">
        </div>

        <button type="submit" class="btn btn-primary">
          Submit
        </button>
      </form>

    </div>
  </div> <!-- row -->

</div>


<?php

$page_scripts = [
  'https://code.jquery.com/jquery-3.7.1.min.js',
  'https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.3.6/b-3.2.6/datatables.min.js',
];

$inline_scripts=<<<JS
  $(function () {

    $('#orgPostForm').on('submit', function (e) {
      e.preventDefault();

      let form = this;
      let formData = new FormData(form);

      $.ajax({
        url: baseUrl('api/organisation-post-save'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',

        success: function (res) {
          if (res.status === 'success') {
            $('#alertBox').html(
              '<div class="alert alert-success">'+res.message+'</div>'
            );
            form.reset();
          } else {
            $('#alertBox').html(
              '<div class="alert alert-danger">'+res.message+'</div>'
            );
          }
        },

        error: function () {
          $('#alertBox').html(
            `<div class="alert alert-danger">Server error occurred.</div>`
          );
        }
      });

    });

  });
JS;
?>