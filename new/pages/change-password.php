<?php
if (!isset($_SESSION['user_id'])) {
  http_response_code(403);
  exit('Unauthorized');
}
?>

<div class="container-fluid">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-3">Change Password</h4>
    <a href="<?= baseUrl('dashboard') ?>" class="btn btn-secondary">
      <i class="bi bi-chevron-left"></i> Back to Dashboard
    </a>
  </div>

  <div id="passwordAlert"></div>

  <div class="row justify-content-center border p-2">
    <div class="col-md-8 shadow p-4">

      <form id="passwordForm" novalidate>
        <?= csrfField() ?>

        <!-- Current Password -->
        <div class="mb-3">
          <label class="form-label">Current Password</label>
          <input type="password"
            class="form-control"
            name="current_password"
            required>
        </div>

        <!-- New Password -->
        <div class="mb-3">
          <label class="form-label">New Password</label>
          <input type="password"
            class="form-control"
            name="new_password"
            minlength="8"
            required>
          <div class="form-text">
            Minimum 8 characters recommended
          </div>
        </div>

        <!-- Confirm New Password -->
        <div class="mb-3">
          <label class="form-label">Confirm New Password</label>
          <input type="password"
            class="form-control"
            name="confirm_password"
            required>
        </div>

        <button type="submit" class="btn btn-warning">
          Update Password
        </button>
      </form>

    </div>
  </div>
</div>

<?php
$page_scripts = [
  'https://code.jquery.com/jquery-3.7.1.min.js',
];
$inline_scripts = <<<JS
$(function () {

  $('#passwordForm').on('submit', function (e) {
    e.preventDefault();

    let form = this;

    if (!form.checkValidity()) {
      form.classList.add('was-validated');
      return;
    }

    $.ajax({
      url: baseUrl('api/update-password'),
      type: 'POST',
      data: $(form).serialize(),
      dataType: 'json',

      success: function (res) {
        if (res.status === 'success') {
          $('#passwordAlert').html(
            `<div class="alert alert-success">`+res . message+`</div>`
          );
          form.reset();
          form.classList.remove('was-validated');
        } else {
          $('#passwordAlert').html(
            '<div class="alert alert-danger">'+res . message+'</div>'
          );
        }
      },

      error: function () {
        $('#passwordAlert').html(
          `<div class="alert alert-danger">Server error occurred.</div>`
        );
      }
    });
  });

});
JS;

?>