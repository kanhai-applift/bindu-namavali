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

/* 3️⃣ Fetch designation (organization-scoped) */
$sql = "
    SELECT id, designation_name, description
    FROM designations
    WHERE id = ? AND organization_id = ?
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('ii', $designationId, $orgId);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

if (!$data) {
  exit('Designation not found');
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Edit Designations</h4>

    <div>
      <a href="<?= baseUrl('designations/') ?>" class="btn btn-secondary">
        <i class="bi bi-chevron-left"></i> Back to Designations
      </a>
      <a href="<?= baseUrl('designations') ?>" class="btn btn-primary">
        <i class="bi bi-list-check"></i> List Designation
      </a>
    </div>
  </div>

  <div id="alertBox"></div>

  <div class="row justify-content-center border p-2">
    <div class="col-md-8 shadow p-4">

      <form id="designationEditForm" novalidate>
        <?= csrfField() ?>

        <!-- real numeric ID stays hidden -->
        <input type="hidden" name="id" value="<?= $hash ?>">

        <div class="mb-3">
          <label class="form-label">Designation Name *</label>
          <input type="text"
            name="designation_name"
            class="form-control"
            value="<?= e($data['designation_name']) ?>"
            required>
          <div class="invalid-feedback">Designation name is required.</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea name="description"
            class="form-control"
            rows="3"><?= e($data['description']) ?></textarea>
        </div>

        <button type="submit" class="btn btn-success">
          Update
        </button>

        <a href="<?= baseUrl('designations') ?>" class="btn btn-secondary">
          Back
        </a>
      </form>
    </div>
  </div>
</div>

<?php
$page_scripts = [
  'https://code.jquery.com/jquery-3.7.1.min.js',
  baseUrl('assets/js/designations-edit.js')
];
$inline_scripts = <<<JS
JS;
?>