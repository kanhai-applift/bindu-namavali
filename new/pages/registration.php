<?php
require_once(__DIR__ . '/../includes/auth.php');
require_superadmin();
require_once(__DIR__ . '/../config/db.php');

?>

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-3"> 
      नवीन वापरकर्ता
      <small class="text-muted sfs-2">(User Registration)</small>
    </h4>
    <a href="<?= baseUrl('dashboard') ?>" class="btn btn-secondary">
      <i class="bi bi-chevron-left"></i> Back to Dashboard
    </a>
  </div>



  <div class="card shadow">
    <div class="card-header bg-dark text-white">
      <h5 class="mb-0">User Registration</h5>
    </div>

    <div class="card-body">
      <form id="registrationForm" novalidate>
        <input type="hidden" id="csrf_token" name="csrf_token" value="<?= csrf_token() ?>">
        <!-- Registration No -->
        <div class="mb-3 row">
          <div class="col-4 text-end">
            <label class="form-label">Registration No</label>
          </div>
          <div class="col-5">
            <input type="text" class="form-control" name="registration_no" required>
            <div class="invalid-feedback">Registration No is required.</div>
          </div>
        </div>

        <!-- Office / Organization -->
        <div class="mb-3 row">
          <div class="col-4 text-end">
            <label class="form-label">Office / Organization</label>
          </div>
          <div class="col-5">
            <input type="text" class="form-control" name="office_name" required>
            <div class="invalid-feedback">Office / Organization is required.</div>
          </div>
        </div>

        <!-- Head of Office -->
        <div class="mb-3 row">
          <div class="col-4 text-end">
            <label class="form-label">Name of the Head of Office</label>
          </div>
          <div class="col-5">
            <input type="text" class="form-control" name="head_name" required>
            <div class="invalid-feedback">Name is required.</div>
          </div>
        </div>

        <!-- District -->
        <div class="mb-3 row">
          <div class="col-4 text-end">
            <label class="form-label">District</label>
          </div>
          <div class="col-5">
            <select class="form-select" name="district" required>
              <option value="">-- Select District --</option>
              <?php
              // 1. Fetch districts from the table
              $sql = "SELECT id, district_name FROM districts WHERE is_active = 1 ORDER BY district_name ASC";
              $stmt = $mysqli->prepare($sql);
              $stmt->execute();
              $result = $stmt->get_result();

              if ($result):
                // 2. Loop through the rows to create options
                while ($row = $result->fetch_assoc()):
                  // It is safer to use an ID for the value and the Name for display
                  $id   = e($row['id']);
                  $name = e($row['district_name']);
              ?>
                  <option value="<?= $id ?>"><?= $name ?></option>
              <?php
                endwhile;
              endif;
              ?>
            </select>
            <div class="invalid-feedback">Please select a district.</div>
          </div>
        </div>

        <!-- Contact No -->
        <div class="mb-3 row">
          <div class="col-4 text-end">
            <label class="form-label">Contact No</label>
          </div>
          <div class="col-5">
            <input type="tel" class="form-control" name="contact_no" pattern="[0-9]{10}" required>
            <div class="invalid-feedback"> Enter a valid 10-digit contact number. </div>
          </div>
        </div>

        <!-- Email -->
        <div class="mb-3 row">
          <div class="col-4 text-end">
            <label class="form-label">Email ID</label>
          </div>
          <div class="col-5">
            <input id="email" type="email" class="form-control" name="email" required>
            <div class="invalid-feedback">Enter a valid email address.</div>
            <small id="emailHelp"></small>
          </div>
        </div>

        <!-- Password -->
        <div class="mb-3 row">
          <div class="col-4 text-end">
            <label class="form-label">Password</label>
          </div>
          <div class="col-5">
            <input type="password" class="form-control" id="password" name="password" minlength="6" required>
            <div class="invalid-feedback"> Password must be at least 6 characters. </div>
          </div>
        </div>

        <!-- Confirm Password -->
        <div class="mb-3 row">
          <div class="col-4 text-end">
            <label class="form-label">Confirm Password</label>
          </div>
          <div class="col-5">
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            <div class="invalid-feedback">Passwords do not match.</div>
          </div>
        </div>

        <div class="text-end">
          <button type="submit" class="btn btn-primary">
            Register
          </button>
        </div>

        <div id="msg" class="mt-3"></div>

      </form>
    </div>
  </div>

</div>

<?php
$page_scripts = [
  'https://code.jquery.com/jquery-3.7.1.min.js',
  baseUrl('assets/js/registration.js'),
];
?>
<?php include "includes/footer.php"; ?>