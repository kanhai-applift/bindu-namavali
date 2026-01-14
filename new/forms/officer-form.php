<style>
  label small {
    display: block;
    font-size: 0.7rem;
    color: #676666ff;
  }

  label small::before {
    content: "(";
  }

  label small::after {
    content: ")";
  }
</style>
<?php
$data = $data ?? [];
$is_edit = isset($data['id']);
?>
<div class="container">

  <form id="entryForm" class="row g-3">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <?php if ($is_edit): ?>
      <input type="hidden" name="id" value="<?= $data['id'] ?>">
    <?php endif; ?>

    <div class="mb-3 col-sm-12 col-md-5">
      <label class="form-label">मंडळ अधिकारी यांचे नाव <small>Name of Board Officer</small></label>
      <input type="text" name="officer_name" class="form-control"
        value="<?= e($data['officer_name']) ?>" required>
    </div>

    <div class="mb-3 col-sm-12 col-md-2">
      <label class="form-label">जि.ज्ये.क्र.<small>G.J. No</small></label>
      <input type="text" name="gj_no" class="form-control"
        value="<?= e($data['gj_no']) ?>">
    </div>


    <!-- District -->
    <div class="mb-3 col-sm-12 col-md-5">
      <label for="district" class="form-label">मूळ जिल्ला<small>District of Origin</small></label>
      <input type="text" class="form-control" id="district" name="district"
        value="<?= e($data['district']) ?>">
    </div>

    <!-- Date of entry into Govt service -->
    <div class="mb-3 col-sm-12 col-md-3">
      <label for="entry_service_date" class="form-label">
        शाशन सेवेत प्रवेश केल्याचे दिनांक
        <small> Date of Entry into Government Service </small>
      </label>
      <input type="date" class="form-control" id="entry_service_date" name="entry_service_date"
        value="<?= e($data['entry_service_date']) ?>">
    </div>

    <!-- Caste -->
    <div class="mb-3 col-sm-12 col-md-3">
      <label for="caste" class="form-label">जात<small>Caste</small></label>
      <input type="text" class="form-control" id="caste" name="caste"
        value="<?= e($data['caste']) ?>">
    </div>

    <!-- Category -->
    <div class="mb-3 col-sm-12 col-md-3">
      <label for="category" class="form-label">प्रवर्ग<small>Category</small></label>
      <select class="form-select" id="category" name="category">
        <option value="">-- Category --</option>
        <option value="Open" <?= selected('Open', e($data['category'])) ?>>Open</option>
        <option value="अ.जा." <?= selected('अ.जा.', e($data['category'])) ?>>अ.जा.</option>
        <option value="SC" <?= selected('SC', e($data['category'])) ?>>SC</option>
        <option value="ST" <?= selected('ST', e($data['category'])) ?>>ST</option>
        <option value="OBC" <?= selected('OBC', e($data['category'])) ?>>OBC</option>
        <option value="VJNT" <?= selected('VJNT', e($data['category'])) ?>>VJNT</option>
      </select>
    </div>

    <!-- Category Valid -->
    <div class="mb-3 col-sm-12 col-md-3">
      <label class="form-label"><small>Category Valid</small></label>
      <div>
        <div class="form-check form-check-inline">
          <input id="valid-category" class="form-check-input" type="radio"
            name="category_valid" value="Valid"
            <?= (($data['category_valid'] ?? '') === 'Valid') ? 'checked' : '' ?>>
          <label for="valid-category" class="form-check-label">वैध <small>Valid</small></label>
        </div>

        <div class="form-check form-check-inline">
          <input id="invalid-category" class="form-check-input" type="radio"
            name="category_valid" value="Invalid"
            <?= (($data['category_valid'] ?? '') === 'Invalid') ? 'checked' : '' ?>>
          <label for="invalid-category" class="form-check-label">अवैध<small>Invalid</small></label>
        </div>
      </div>
    </div>


    <!-- Date of Birth -->
    <div class="mb-3 col-sm-12 col-md-6">
      <label for="dob" class="form-label">जन्म तारीख<small>Date of Birth</small></label>
      <input type="date" class="form-control" id="dob" name="dob"
        value="<?= e($data['dob']) ?>">
    </div>

    <!-- Academic Qualification -->
    <div class="mb-3 col-sm-12 col-md-6">
      <label for="qualification" class="form-label">
        शैक्षणिक अहर्ता
        <small> Academic Qualification </small>
      </label>
      <input type="text" class="form-control" id="qualification" name="qualification"
        value="<?= e($data['qualification']) ?>">
    </div>

    <!-- Date of Passing Revenue Examination -->
    <div class="mb-3 col-sm-12 col-md-3">
      <label for="revenue_exam_date" class="form-label">
        महसूल परीक्षा उत्तीर्ण केल्याचा दिनांक
        <small> Date of Passing Revenue Examination</small>
      </label>
      <input type="date" class="form-control" id="revenue_exam_date" name="revenue_exam_date"
        value="<?= e($data['revenue_exam_date']) ?>">
    </div>

    <!-- Date of relaxation -->
    <div class="mb-3 col-sm-12 col-md-3">
      <label for="relaxation_date" class="form-label">
        वयाची ४५ वर्ष पूर्ण केल्या मुळे सूट दिल्याचा दिनांक
        <small>Date of Relaxation on Completion of 45 Years of Age</small>
      </label>
      <input type="date" class="form-control" id="relaxation_date" name="relaxation_date"
        value="<?= e($data['relaxation_date']) ?>">
    </div>

    <!-- Date continuously working -->
    <div class="mb-3 col-sm-12 col-md-3">
      <label for="continuous_work_date" class="form-label">
        मंडळ अधिकारी म्हणून सतत कामे करीत असल्याचा दिनांक
        <small>Date of Continuously Working as Board Officer</small>
      </label>
      <input type="date" class="form-control" id="continuous_work_date" name="continuous_work_date"
        value="<?= e($data['continuous_work_date']) ?>">
    </div>

    <!-- Date of regularization -->
    <div class="mb-3 col-sm-12 col-md-3">
      <label for="regularization_date" class="form-label">
        मंडळ अधिकारी या संवर्गात नियमित केल्याचा दिनांक ( आदेश दिनांक )
        <small>Date of Regularization in Cadre of Board Officer (Date of Order)</small>
      </label>
      <input type="date" class="form-control" id="regularization_date" name="regularization_date"
        value="<?= e($data['regularization_date']) ?>">
    </div>

    <div class="mb-3 col-sm-12">
      <label class="form-label">शेरा<small>Remark</small></label>
      <textarea name="remark" class="form-control" rows="3"><?= e($data['remark']) ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary">
      <?= $is_edit ? 'Update Entry' : 'Save Entry' ?>
    </button>

    <div id="formMessage" class="mb-3"></div>
  </form>

</div>