<?php
require_once(__DIR__ . '/../includes/auth.php');
require_login();

require_once(__DIR__ . '/../config/db.php');

if (empty($segments[1])) {
  exit('Invalid designation');
}

$hashedDesignationId = $segments[1];
$hashedEmployeeId = $segments[2];

// Decode designation ID
$decoded = $hashids->decode($hashedDesignationId);
$decodedEmployeeId = $hashids->decode($hashedEmployeeId);

if (empty($decoded) || empty($decodedEmployeeId)) {
  exit('Invalid designation OR Invalid Employee');
}

$designationId  = (int)$decoded[0];
$employeeId     = (int)$decodedEmployeeId[0];
$orgId          = $_SESSION['user_id'];

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

$sql = "SELECT *
        FROM employees
        WHERE id = ? AND organization_id = ? AND designation_id = ?";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('iii', $employeeId, $orgId, $designationId);
$stmt->execute();
$data = $stmt->get_result();

if ($data->num_rows <= 0) {
  exit('Unauthorized access');
}

$edit_data = $data->fetch_assoc();
$stmt->close();


//============================= old code
require_once(__DIR__ . '/../old-code/notebook.php');
$edit_mode = 0;
?>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">

<div class="m-0 mt-3">
  <h4 class="mb-3">
    पद <small class="sfs-1 text-muted">(Post/Designation)</small> - <?= e($designation['designation_name']) ?>
  </h4>

  <div class="alert alert-primary d-flex align-items-center sfs-1" role="alert">
    <small>
      <strong>Number Mapping Info:</strong> Numbers like 101, 201, 301, etc. will map to the same category as 1, 2, 3, etc.
      (Example: 101 → category of 1, 202 → category of 2)<br>
      <strong>Date Format:</strong> DD/MM/YYYY (e.g., 25/12/2023) - Click on date fields to open calendar<br>
      <strong>Retirement Date:</strong> Auto-calculated as Birth Date + 58 years (month end), but can be manually edited
    </small>
  </div>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Update Entries</h4>
    <a href="<?= baseUrl('goshwara-add/'.$hashedDesignationId) ?>" class="btn btn-primary">
      <i class="bi bi-plus-lg"></i> गोषवारा
    </a>
  </div>

  <div id="alertBox"></div>

  <form id="employeeForm" class="border p-2 sfs-3" enctype="multipart/form-data" novalidate>
    <?= csrfField() ?>

    <input type="hidden" name="designation_hash" value="<?= e($hashedDesignationId) ?>">
    <input type="hidden" name="employee_hash" value="<?= e($hashedEmployeeId) ?>">

    <div class="row g-1">
      <div class="col-md-1 mb-3">
        <label class="form-label">बिंदू क्रमांक</label>
        <input id="bindu_kramaank" type="number" name="bindu_no" value="<?= e($edit_data['bindu_no']) ?>" class="form-control" required>
        <br>
        <label class="form-label">बिंदू प्रवर्ग</label>
        <input id="bindu_namavli" readonly type="text" name="bindu_category" value="<?= e($edit_data['bindu_category']) ?>" class="form-control" required>
      </div>

      <div class="col-md-2 mb-3">
        <div>
          <label class="form-label">कर्मचाऱ्याचे नाव</label>
          <input type="text" name="employee_name" class="form-control" value="<?= e($edit_data['employee_name']) ?>">
        </div>
        <br>
        <div class="row g-1">
          <div class="col-md-6">
            <label class="form-label">कर्मचारी जात</label>
            <input type="text" name="employee_caste" class="form-control" value="<?= e($edit_data['employee_caste']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">कर्मचारी प्रवर्ग</label>
            <input type="text" name="employee_category" class="form-control" value="<?= e($edit_data['employee_category']) ?>">
          </div>
        </div>
      </div>

      <div class="col-md-1 mb-3">
        <label class="form-label">नियुक्ती दिनांक</label>
        <input id="pad_niyukt_dinank" type="text" name="date_of_appointment" class="form-control fw-bold"
          value="<?= formatDate(e($edit_data['date_of_appointment'])) ?>"><br>
        <label class="form-label">जन्म दिनांक</label>
        <input id="janma_tarik" type="text" name="date_of_birth" class="form-control fw-bold"
          value="<?= formatDate(e($edit_data['date_of_birth'])) ?>">
      </div>

      <div class="col-md-1 mb-3">
        <label class="form-label">सेवा निवृत्ती दिनांक</label>
        <input id="sevaniroti_dinank" type="text" name="date_of_retirement" class="form-control date-input auto-calculated"
          value="<?= formatDate(e($edit_data['date_of_retirement'])) ?>">
        <div id="retirementInfo" class="auto-calculation-info d-flex align-items-center sfs-4 p-2" role="alert">
          Auto-calculated: Birth Date + 58 years (Month end date)
        </div>
      </div>

      <div class="col-md-2 mb-3">
        <label class="form-label">जात प्रमाणपत्र क्रमांक</label>
        <input type="text" name="caste_certificate_no" class="form-control"
          value="<?= e($edit_data['caste_certificate_no']) ?>">
        <br>
        <label class="form-label"> प्रधीकार्यांचे पदनाव</label>
        <input type="text" name="caste_cert_authority" class="form-control"
          value="<?= e($edit_data['caste_cert_authority']) ?>">
      </div>


      <div class="col-md-2 mb-3">
        <label class="form-label">जात वैधता प्रमानपत्र क्रमांक</label>
        <input type="text" name="caste_validity_certificate_no" class="form-control"
          value="<?= e($edit_data['caste_validity_certificate_no']) ?>">
        <br>
        <label class="form-label">वैधता समितीचे नाव</label>
        <input type="text" name="validation_committee_name" class="form-control"
          value="<?= e($edit_data['validation_committee_name']) ?>">
      </div>

      <div class="col-md-3 mb-3">
        <div class="row g-1">
          <div class="col-md-4 p-3">
            <label class="form-label">
              <input type="checkbox" name="working" value="1" <?= e($edit_data['working']) ? 'checked' : '' ?>>
              &nbsp; &nbsp; कार्यरत
            </label>
          </div>
          <div class="col-md-8">
            <label class="form-label">Upload PDF</label>
            <input type="file" name="pdf" class="form-control" accept="application/pdf">
            <?php if (!empty($edit_data['pdf'])) :  ?>
              <a href="<?= baseUrl($edit_data['pdf']) ?>" target="_blank"> View Current File</a>
            <?php endif; ?>
            <input type="hidden" name="pdf_old" class="form-control" value="<?= e($edit_data['pdf']) ?>">
          </div>
        </div>
        <div class="row g-1">
          <textarea placeholder="शेरा Shera" class="form-control" name="remarks" rows="2"><?= e($edit_data['remarks']) ?></textarea>
        </div>
      </div>

    </div>

    <a href="<?= baseUrl('employees-add/' . $hashedDesignationId) ?>" class="btn btn-secondary">Back</a>

    <button type="submit" class="btn btn-success float-end ms-3">Update</button>
    <a href="<?= baseUrl('employees-add/' . $hashedDesignationId) ?>" class="btn btn-secondary float-end">Cancel</a>

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
  baseUrl('assets/js/employees-edit.js')
];

$inline_scripts = <<<JS
  var designationHash = "{$hashedDesignationId}";
  $(function () {
    document.getElementById('sidebar').classList.toggle('collapsed');

    $('#employeesTable').DataTable({
      pageLength: 50,
      order: [[5, 'desc']],
      lengthMenu: [10, 25, 50, 100],
      responsive: true
    });
  });
JS;
?>