<?php
require_once(__DIR__.'/../includes/auth.php');
require_login();

require_once(__DIR__.'/../config/db.php');




if($_SESSION['role'] == 'superadmin') {
  include_once('organisations-post-super.php');
}
else {
  include_once('organisations-post-admin.php');
}


$stmt->close();

$csrf_token = csrf_token();

$page_scripts = [
  'https://code.jquery.com/jquery-3.7.1.min.js',
  'https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.3.6/b-3.2.6/datatables.min.js',
];

$inline_scripts = <<<JS
  $(document).ready(function () {

    $('#organisationPostTable').DataTable({
      pageLength: 10,
      lengthChange: true,
      searching: true,
      ordering: true,
      info: true,
      autoWidth: false,
      columnDefs: [
        { orderable: false, targets: 4 } // Disable sort on Action column
      ]
    });

  });  
JS;
?>