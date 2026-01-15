<?php
require_once(__DIR__ . '/../includes/auth.php');
require_superadmin();

require_once(__DIR__ . '/../config/db.php');


?>

<div class="container-fluid">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>
      नवीन शासन निर्णय जोडा
      <small class="text-muted sfs-2">(Add new Shasan Nirnay)</small>
    </h4>
    <a href="<?= baseUrl('shasan-nirnay') ?>" class="btn btn-secondary">
      <i class="bi bi-chevron-left"></i> Back to List
    </a>
  </div>

  <div class="row justify-content-center border p-2">
    <div class="col-md-10 shadow p-4">

      <form id="shasanNirnayForm" enctype="multipart/form-data" novalidate>
        <?= csrfField() ?>

        <!-- KR No -->
        <div class="mb-3">
          <label class="form-label">क्र. क्र.</label>
          <input type="text" class="form-control" name="kr_no" required>
          <div class="invalid-feedback">KR No is required</div>
        </div>

        <!-- Amal Tarikh -->
        <div class="mb-3">
          <label class="form-label">अंमलबजावणीची तारीख</label>
          <input type="date" class="form-control" name="amal_tarik" required>
          <div class="invalid-feedback">Date is required</div>
        </div>

        <!-- GR No -->
        <div class="mb-3">
          <label class="form-label">शासन निर्णय / परिपत्रक</label>
          <input type="text" class="form-control" name="gr_no" required>
          <div class="invalid-feedback">This No is required</div>
        </div>

        <!-- Vishay -->
        <div class="mb-3">
          <label class="form-label">विषय</label>
          <textarea class="form-control" name="vishay" rows="10" required></textarea>
          <div class="invalid-feedback">Vishay is required</div>
        </div>

        <!-- PDF -->
        <div class="mb-3">
          <label class="form-label">Upload PDF (optional)</label>
          <input type="file" class="form-control" name="pdf_file" accept="application/pdf">
        </div>

        <div id="snAlert" class="my-3"></div>

        <button type="submit" class="btn btn-primary">
          Save
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
  $(function() {

    $('#shasanNirnayForm').on('submit', function(e) {
      e.preventDefault();

      let form = this;

      if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
      }

      let formData = new FormData(form);

      $.ajax({
        url: baseUrl('api/shasan-nirnay-save'),
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',

        success: function(res) {
          if (res.status === 'success') {
            $('#snAlert').html(
              '<div class="alert alert-success">'+res.message+'</div>'
            );
            form.reset();
            form.classList.remove('was-validated');
          } else {
            $('#snAlert').html(
              '<div class="alert alert-danger">'+res.message+'</div>'
            );
          }
        },

        error: function() {
          $('#snAlert').html(
            `<div class="alert alert-danger">Server error occurred</div>`
          );
        }
      });

    });

  });
JS;
?>