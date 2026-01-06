<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
include($_SERVER['DOCUMENT_ROOT'] . '/ho/hostel/includes/config.php');
include('includes/checklogin.php');
check_login();

if ($_SESSION['id']) {
  // fetch username from DB
  $uid = $_SESSION['id'];
  $stmt = $mysqli->prepare("SELECT username FROM users WHERE id=?");
  $stmt->bind_param("i", $uid);
  $stmt->execute();
  $stmt->bind_result($username);
  $stmt->fetch();
  $stmt->close();
?>
  <!doctype html>
  <html lang="en" class="no-js">

  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="theme-color" content="#3e454c">
    <title>Bindu Namavali</title>
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap-social.css">
    <link rel="stylesheet" href="css/bootstrap-select.css">
    <link rel="stylesheet" href="css/fileinput.min.css">
    <link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
    <link rel="stylesheet" href="css/style.css">
    <script language="javascript" type="text/javascript">
      var popUpWin = 0;

      function popUpWindow(URLStr, left, top, width, height) {
        if (popUpWin) {
          if (!popUpWin.closed) popUpWin.close();
        }
        popUpWin = open(URLStr, 'popUpWin', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,copyhistory=yes,width=' + 510 + ',height=' + 430 + ',left=' + left + ', top=' + top + ',screenX=' + left + ',screenY=' + top + '');
      }
    </script>

  </head>

  <body>
    <div class="brand clearfix">
      <a href="#" class="logo" style="font-size:16px; color:#fff !important">
        बिंदू नामावली नोंदणी - ADMIN
      </a>
      <span class="menu-btn"><i class="fa fa-bars"></i></span>
      <ul class="ts-profile-nav">
        <li class="ts-account">
          <a href="#"><img src="img/ts-avatar.jpg" class="ts-avatar hidden-side" alt=""> Account <i class="fa fa-angle-down hidden-side"></i></a>
          <ul>
            <li><a href="my-profile.php">My Account</a></li>
            <li><a href="logout.php">Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>

  <?php } else { ?>
    <div class="brand clearfix">
      <a href="#" class="logo" style="font-size:16px;color:#fff !important">बिंदू नामावली नोंदणी</a>
      <span class="menu-btn"><i class="fa fa-bars"></i></span>
    </div>
  <?php } ?>