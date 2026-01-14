<?php
require_once(__DIR__ . '/../includes/auth.php');
require_login();

require_once(__DIR__ . '/../config/db.php');

if (empty($segments[1])) {
  exit('Invalid designation');
}

$hashedDesignationId = $segments[1];

// Decode designation ID
$decoded = $hashids->decode($hashedDesignationId);

if (empty($decoded)) {
  exit('Invalid designation');
}

$designationId = (int)$decoded[0];
$orgId = $_SESSION['user_id'];

// Verify designation belongs to this organization
$sql = "SELECT designation_name
        FROM designations
        WHERE id = ? AND organization_id = ?";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('ii', $designationId, $orgId);
$stmt->execute();
$res = $stmt->get_result();
$designation = $res->fetch_assoc();
$stmt->close();

if (!$designation) {
  exit('Unauthorized access');
}

//============================= old code
require_once(__DIR__ . '/../old-code/notebook.php');
$edit_mode = 0;
?>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">

<div class="m-0 mt-3">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-3">
      पद <small class="sfs-1 text-muted">(Post/Designation)</small> - <?= e($designation['designation_name']) ?>
    </h4>
    <a href="<?= baseUrl('designations/') ?>" class="btn btn-secondary">
      <i class="bi bi-chevron-left"></i> Back to Designations
    </a>
  </div>

  <div class="alert alert-primary d-flex align-items-center sfs-1" role="alert">
    <small>
      <strong>Number Mapping Info:</strong> Numbers like 101, 201, 301, etc. will map to the same category as 1, 2, 3, etc.
      (Example: 101 → category of 1, 202 → category of 2)<br>
      <strong>Date Format:</strong> DD/MM/YYYY (e.g., 25/12/2023) - Click on date fields to open calendar<br>
      <strong>Retirement Date:</strong> Auto-calculated as Birth Date + 58 years (month end), but can be manually edited
    </small>
  </div>


  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Add new Entries</h4>
    <a href="<?= baseUrl('goshwara-add/' . $hashedDesignationId) ?>" class="btn btn-primary">
      <i class="bi bi-plus-lg"></i> गोषवारा
    </a>
  </div>

  <div id="alertBox"></div>

  <form id="employeeForm" class="border p-2 sfs-3" enctype="multipart/form-data" novalidate>
    <?= csrfField() ?>

    <input type="hidden" name="designation_hash" value="<?= e($hashedDesignationId) ?>">

    <div class="row g-1">
      <div class="col-md-1 mb-3">
        <label class="form-label">बिंदू क्रमांक</label>
        <input id="bindu_kramaank" readonly type="number" name="bindu_no" value="<?= $next_bindu_kramaank ?>" class="form-control" required>
        <br>
        <label class="form-label">बिंदू प्रवर्ग</label>
        <input id="bindu_namavli" readonly type="text" name="bindu_category" value="<?= $initial_category ?>" class="form-control" required>
      </div>

      <div class="col-md-2 mb-3">
        <div>
          <label class="form-label">कर्मचाऱ्याचे नाव</label>
          <input type="text" name="employee_name" class="form-control">
        </div>
        <br>
        <div class="row g-1">
          <div class="col-md-6">
            <label class="form-label">कर्मचारी जात</label>
            <input type="text" name="employee_caste" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="form-label">कर्मचारी प्रवर्ग</label>
            <input type="text" name="employee_category" class="form-control">
          </div>
        </div>
      </div>

      <div class="col-md-1 mb-3">
        <label class="form-label">नियुक्ती दिनांक</label>
        <input id="pad_niyukt_dinank" type="text" name="date_of_appointment" class="form-control fw-bold"><br>
        <label class="form-label">जन्म दिनांक</label>
        <input id="janma_tarik" type="text" name="date_of_birth" class="form-control fw-bold">
      </div>

      <div class="col-md-1 mb-3">
        <label class="form-label">सेवा निवृत्ती दिनांक</label>
        <input id="sevaniroti_dinank" type="text" name="date_of_retirement" class="form-control date-input auto-calculated">
        <div id="retirementInfo" class="auto-calculation-info d-flex align-items-center sfs-4 p-2">
          Auto-calculated: Birth Date + 58 years (Month end date)
        </div>
      </div>

      <div class="col-md-2 mb-3">
        <label class="form-label">जात प्रमाणपत्र क्रमांक</label>
        <input type="text" name="caste_certificate_no" class="form-control">
        <br>
        <label class="form-label"> प्रधीकार्यांचे पदनाव</label>
        <input type="text" name="caste_cert_authority" class="form-control">
      </div>


      <div class="col-md-2 mb-3">
        <label class="form-label">जात वैधता प्रमानपत्र क्रमांक</label>
        <input type="text" name="caste_validity_certificate_no" class="form-control">
        <br>
        <label class="form-label">वैधता समितीचे नाव</label>
        <input type="text" name="validation_committee_name" class="form-control">
      </div>

      <div class="col-md-3 mb-3">
        <div class="row g-1">
          <div class="col-md-4 p-3">
            <label class="form-label">
              <input type="checkbox" name="working" value="1" <?= ($edit_mode && $edit_data['karyarat']) || !$edit_mode ? 'checked' : '' ?>>
              &nbsp; &nbsp; कार्यरत
            </label>
          </div>
          <div class="col-md-8">
            <label class="form-label">Upload PDF</label>
            <input type="file" name="pdf" class="form-control" accept="application/pdf">
          </div>
        </div>
        <div class="row g-1">
          <textarea placeholder="शेरा Shera" class="form-control" name="remarks" rows="2"><?= $edit_mode ? htmlspecialchars($edit_data['shera']) : '' ?></textarea>
        </div>
      </div>

    </div>

    <a href="<?= baseUrl('designations/' . $hashedDesignationId) ?>" class="btn btn-secondary">Back</a>

    <button type="submit" class="btn btn-success float-end">Save</button>

  </form>

  <?php
  include_once('employees-list.php');
  ?>

</div>

<?php
$page_scripts = [
  'https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.3.6/b-3.2.6/datatables.min.js',
  'https://code.jquery.com/ui/1.14.1/jquery-ui.js',
  baseUrl('assets/js/employees-common.js'),
  baseUrl('assets/js/employees-add.js')
];
$inline_scripts = <<<JS
  $(function () {
    document.getElementById('sidebar').classList.toggle('collapsed');

    $('#employeesTable').DataTable({
      pageLength: 10,
      order: [[5, 'desc']],
      lengthMenu: [10, 25, 50, 100],
      responsive: true
    });
  });
JS;
?>