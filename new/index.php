<?php
error_reporting(E_ALL);
include_once('includes/header.php');

if (!in_array($current_page ,['login','logout'])) {
    require_once 'includes/auth.php';
}

?>

<div class="d-flex">

  <?php
  include_once('includes/sidebar.php');
  ?>

  <!-- Main Content -->
  <main class="flex-grow-1 content">
    <!-- Include the page here -->
    <?php 
      // print_r(ALLOWED_PAGES);
    ?>
    <?php require_once("pages/$current_page.php"); ?>
  </main>
</div>

<?php
include_once('includes/footer.php');
