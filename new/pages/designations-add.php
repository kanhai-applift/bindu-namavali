<div class="container-fluid">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Designations</h4>

    <div>
      <a href="<?= baseUrl('designations/') ?>" class="btn btn-secondary">
        <i class="bi bi-chevron-left"></i> Back to Designations
      </a>
      <a href="<?= baseUrl('designations') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> List Designation
      </a>
    </div>
  </div>

  <div id="alertBox"></div>

  <div class="row justify-content-center border p-2">
    <div class="col-md-8 shadow p-4">

      <form id="designationForm" novalidate>
        <?= csrfField() ?>

        <div class="mb-3">
          <label class="form-label">Designation Name *</label>
          <input type="text" name="designation_name" class="form-control" required>
          <div class="invalid-feedback">Designation name is required.</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-success">Save</button>
        <a href="<?= baseUrl('designations') ?>" class="btn btn-secondary">Back</a>
      </form>

    </div>
  </div>
</div>

<?php

$page_scripts = [
  'https://code.jquery.com/jquery-3.7.1.min.js',
  baseUrl('assets/js/designations-add.js')
]
?>