<?php
require_once(__DIR__ . '/../includes/auth.php');
require_login();

// echo "<pre>";
// print_r($_SESSION);
// echo "</pre>";

?>

<div class="container-fluid mt-4 dashboard">
  <h2 class="mb-4">Dashboard</h2>
  <hr>

  <div class="row g-4">

    <!-- Admin-only card -->
    <?php
    if ($_SESSION['role'] === 'admin'):
      include("dashboard-admin.php");
    elseif ($_SESSION['role'] === 'superadmin'):
      include("dashboard-superadmin.php");
    endif;
    ?>

  </div>
</div>