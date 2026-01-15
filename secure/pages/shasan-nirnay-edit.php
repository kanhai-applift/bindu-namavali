<?php
require_once(__DIR__ . '/../includes/auth.php');
require_login();

require_once(__DIR__ . '/../config/db.php');

if (empty($segments[1])) {
  exit('Invalid Shasan Nirnay');
}

$hashedShasanNirnay = $segments[1];

// Decode Shasan Nirnay ID
$decoded = $hashids->decode($hashedShasanNirnay);

if (empty($decoded)) {
  exit('Invalid Shasan Nirnay');
}

$shasanNirnayId = (int)$decoded[0];


$stmt = $mysqli->prepare("
  SELECT kr_no, amal_tarik, gr_no, vishay, pdf_file
  FROM shasan_nirnay
  WHERE id = ?
");
$stmt->bind_param('i', $shasanNirnayId);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

if (!$data) {
  exit('Record not found');
}
?>

<div class="container-fluid">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-3">
      शासन निर्णय संपादित करा
      <small class="text-muted sfs-2">(Edit Shasan Nirnay)</small>
    </h4>
    <a href="<?= baseUrl('shasan-nirnay') ?>" class="btn btn-secondary">
      <i class="bi bi-chevron-left"></i> Back to List
    </a>
  </div>

  <div class="row justify-content-center border p-2">
    <div class="col-md-10 shadow p-4">

      <form id="shasanNirnayEditForm" enctype="multipart/form-data" novalidate>
        <?= csrfField() ?>
        <input type="hidden" name="shasan_nirnay_hashed" value="<?= (string)$hashedShasanNirnay ?>">

        <!-- KR No -->
        <div class="mb-3">
          <label class="form-label">क्र. क्र.</label>
          <input type="text"
            class="form-control"
            name="kr_no"
            value="<?= e($data['kr_no']) ?>"
            required>
        </div>

        <!-- Amal Tarikh -->
        <div class="mb-3">
          <label class="form-label">अंमलबजावणीची तारीख</label>
          <input type="date"
            class="form-control"
            name="amal_tarik"
            value="<?= e($data['amal_tarik']) ?>"
            required>
        </div>

        <!-- GR No -->
        <div class="mb-3">
          <label class="form-label">शासन निर्णय / परिपत्रक</label>
          <input type="text"
            class="form-control"
            name="gr_no"
            value="<?= e($data['gr_no']) ?>"
            required>
        </div>

        <!-- Vishay -->
        <div class="mb-3">
          <label class="form-label">विषय</label>
          <textarea class="form-control"
            name="vishay"
            rows="10"
            required><?= e($data['vishay']) ?></textarea>
        </div>

        <!-- Existing PDF -->
        <?php if ($data['pdf_file']) : ?>
          <div class="mb-3">
            <input type="hidden" name="pdf_file_old" value="<?= e($data['pdf_file']) ?>">
            <a href="<?= baseUrl($data['pdf_file']) ?>"
              target="_blank"
              class="btn btn-sm btn-outline-primary">
              View Existing PDF
            </a>
          </div>
        <?php endif; ?>

        <!-- Replace PDF -->
        <div class="mb-3">
          <label class="form-label">Replace PDF (optional)</label>
          <input type="file"
            class="form-control"
            name="pdf_file"
            accept="application/pdf">
        </div>

        <div id="snEditAlert" class="my-3"></div>

        <button type="submit" class="btn btn-primary">
          Update
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

    $('#shasanNirnayEditForm').on('submit', function(e) {
      e.preventDefault();

      let form = this;

      if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
      }

      let formData = new FormData(form);

      $.ajax({
        url: baseUrl('api/shasan-nirnay-update'),
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',

        success: function(res) {
          if (res.status === 'success') {
            $('#snEditAlert').html(
              '<div class="alert alert-success">'+res.message+'</div>'
            );
          } else {
            $('#snEditAlert').html(
              '<div class="alert alert-danger">'+res.message+'</div>'
            );
          }
        },

        error: function() {
          $('#snEditAlert').html(
            `<div class="alert alert-danger">Server error occurred</div>`
          );
        }
      });

    });

  });
JS;
