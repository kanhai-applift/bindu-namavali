<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
check_login();
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
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="css/font-awesome.min.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
  <link rel="stylesheet" href="css/bootstrap-social.css">
  <link rel="stylesheet" href="css/bootstrap-select.css">
  <link rel="stylesheet" href="css/fileinput.min.css">
  <link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include("includes/header.php");?>
<div class="ts-main-content">
<?php include("includes/sidebar.php");?>

<div class="content-wrapper">
  <div class="container-fluid">

    <div class="row">
      <div class="col-md-12">
        <h2 class="page-title" style="margin-top:4%">Dashboard</h2>

        <!-- === Row 1 === -->
        <div class="row">

          <!-- Registered Posts -->
          <div class="col-md-4">
            <div class="panel panel-default">
              <div class="panel-body bk-info text-light">
                <div class="stat-panel text-center">
                  <?php
                  $result ="SELECT count(*) FROM complaints";
                  $stmt = $mysqli->prepare($result);
                  $stmt->execute();
                  $stmt->bind_result($count);
                  $stmt->fetch();
                  $stmt->close();
                  ?>
                  <div class="stat-panel-number h1"><?php echo $count;?></div>
                  <div class="stat-panel-title text-uppercase">Approved POST</div>
                </div>
              </div>
              <a href="all-complaints.php" class="block-anchor panel-footer">Full Detail <i class="fa fa-arrow-right"></i></a>
            </div>
          </div>

          <!-- New Post -->
          <div class="col-md-4">
            <div class="panel panel-default">
              <div class="panel-body bk-danger text-light">
                <div class="stat-panel text-center">
                  <?php
                  $result1 ="select count(*) from complaints where complaintStatus is null";
                  $stmt1 = $mysqli->prepare($result1);
                  $stmt1->execute();
                  $stmt1->bind_result($count1);
                  $stmt1->fetch();
                  $stmt1->close();
                  ?>
                  <div class="stat-panel-number h1"><?php echo $count1;?></div>
                  <div class="stat-panel-title text-uppercase">New POST</div>
                </div>
              </div>
              <a href="new-complaints.php" class="block-anchor panel-footer text-center">See All &nbsp;<i class="fa fa-arrow-right"></i></a>
            </div>
          </div>

          

        <!-- === Row 2 === -->
        <div class="row">

          

          <!-- Total Feedbacks -->
          <div class="col-md-4">
            <div class="panel panel-success">
              <div class="panel-body bk-info text-light">
                <div class="stat-panel text-center">
                  <?php
                  $result4 ="select count(*) from feedback";
                  $stmt4 = $mysqli->prepare($result4);
                  $stmt4->execute();
                  $stmt4->bind_result($count4);
                  $stmt4->fetch();
                  $stmt4->close();
                  ?>
                  <div class="stat-panel-number h1"><?php echo $count4;?></div>
                  <div class="stat-panel-title text-uppercase">Chat Bot</div>
                </div>
              </div>
              <a href="feedbacks.php" class="block-anchor panel-footer text-center">See All &nbsp;<i class="fa fa-arrow-right"></i></a>
            </div>
          </div>

          <!-- Users Notebook -->
          <div class="col-md-4">
            <div class="panel panel-success">
              <div class="panel-body bk-primary text-light">
                <div class="stat-panel text-center">
                  <?php
                  // count total users (for notebooks)
                  $result5 ="select count(*) from userregistration";
                  $stmt5 = $mysqli->prepare($result5);
                  $stmt5->execute();
                  $stmt5->bind_result($count5);
                  $stmt5->fetch();
                  $stmt5->close();
                  ?>
                  <div class="stat-panel-number h1"><?php echo $count5;?></div>
                  <div class="stat-panel-title text-uppercase">Users Notebook</div>
                </div>
              </div>
              <a href="all_notebooks.php" class="block-anchor panel-footer text-center">View Users &nbsp;<i class="fa fa-arrow-right"></i></a>
            </div>  
</div><!-- /.row -->
<!-- शासन निर्णय -->
  <div class="col-md-4">
    <div class="panel panel-info">
      <div class="panel-body bk-warning text-light">
        <div class="stat-panel text-center">
          <?php
          $result6 ="select count(*) from shasan_nirnay";
          $stmt6 = $mysqli->prepare($result6);
          $stmt6->execute();
          $stmt6->bind_result($count6);
          $stmt6->fetch();
          $stmt6->close();
          ?>
          <div class="stat-panel-number h1"><?php echo $count6;?></div>
          <div class="stat-panel-title text-uppercase">शासन निर्णय</div>
        </div>
      </div>
      <a href="shashan_nirnay.php" class="block-anchor panel-footer text-center">View List &nbsp;<i class="fa fa-arrow-right"></i></a>
    </div>
  </div>
</div>
</div><!-- /.row -->

          </div>

       
      </div>
    </div>

  </div>
</div>
</div>



  


<!-- Loading Scripts -->
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap-select.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/jquery.dataTables.min.js"></script>
<script src="js/dataTables.bootstrap.min.js"></script>
<script src="js/Chart.min.js"></script>
<script src="js/fileinput.js"></script>
<script src="js/chartData.js"></script>
<script src="js/main.js"></script>
</body>
</html>
