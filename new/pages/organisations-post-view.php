<?php
require_once(__DIR__ . '/../includes/auth.php');
require_login();

require_once(__DIR__ . '/../config/db.php');


/* 1️⃣ Validate post_hash(ID) from URL */
if (empty($segments[1])) {
  exit('Invalid request');
}



if ($_SESSION['role'] === 'admin'):
  include("admin/post-details.php");
elseif ($_SESSION['role'] === 'superadmin'):
  include("superadmin/post-details.php");
endif;