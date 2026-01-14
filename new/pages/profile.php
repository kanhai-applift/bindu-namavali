<?php
require_once(__DIR__ . '/../includes/auth.php');
require_login();

require_once(__DIR__ . '/../config/db.php');

$orgId = $_SESSION['user_id'];


$stmt = $mysqli->prepare("
    SELECT registration_no, office_name, contact_no, email
    FROM users
    WHERE id = ?
");
$stmt->bind_param('i', $orgId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<div class="container-fluid">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-3">Update Profile: <?= e($user['office_name']) ?> </h4>
    <a href="<?= baseUrl('dashboard') ?>" class="btn btn-secondary">
      <i class="bi bi-chevron-left"></i> Back to Dashboard
    </a>
  </div>

  <div id="alertBox"></div>

  <div class="row justify-content-center border p-2">
    <div class="col-md-8 shadow p-4">

      <form id="profileForm" novalidate>
        <?= csrfField() ?>

        <!-- Registration No -->
        <div class="mb-3">
          <label class="form-label">Registration No</label>
          <input type="text"
            readonly
            class="form-control"
            name="registration_no"
            value="<?= e($user['registration_no']) ?>"
            required>
        </div>

        <!-- Organisation Name -->
        <div class="mb-3">
          <label class="form-label">Organisation</label>
          <input type="text"
            class="form-control"
            name="office_name"
            value="<?= e($user['office_name']) ?>"
            required>
        </div>

        <!-- Contact No -->
        <div class="mb-3">
          <label class="form-label">Contact No</label>
          <input type="text"
            class="form-control"
            name="contact_no"
            pattern="[0-9]{10}"
            value="<?= e($user['contact_no']) ?>"
            required>
          <div class="invalid-feedback">
            Enter a valid 10-digit contact number
          </div>
        </div>

        <!-- Email -->
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email"
            readonly
            class="form-control"
            name="email"
            value="<?= e($user['email']) ?>"
            required>
        </div>

        <button type="submit" class="btn btn-primary">
          Update Profile
        </button>
      </form>
    </div>
  </div>
</div>


<?php 
$page_scripts = [
  'https://code.jquery.com/jquery-3.7.1.min.js',
];
$inline_scripts =<<<JS
$(function () {

  $('#profileForm').on('submit', function (e) {
    e.preventDefault();

    let form = this;

    if (!form.checkValidity()) {
      form.classList.add('was-validated');
      return;
    }

    $.ajax({
      url: baseUrl('api/profile-update'),
      type: 'POST',
      data: $(form).serialize(),
      dataType: 'json',

      success: function (res) {
        if (res.status === 'success') {
          $('#alertBox').html(
            `<div class="alert alert-success">`+res . message+`</div>`
          );
        } else {
          $('#alertBox').html(
            '<div class="alert alert-danger">'+res . message+'</div>'
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