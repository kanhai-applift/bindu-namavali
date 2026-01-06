<?php
session_start();
include('includes/config.php');
date_default_timezone_set('Asia/Kolkata');
include('includes/checklogin.php');
check_login();
$aid=$_SESSION['id'];

// Form submission
if(isset($_POST['submit']))
{
    $complainttype=$_POST['ctype'];
    $complaintdetails=$_POST['cdetails'];
    $imgfile=$_FILES["image"]["name"];
    $cnumber=mt_rand(100000000,999999999);

    if($imgfile!=''){
        $extension = substr($imgfile,strlen($imgfile)-4,strlen($imgfile));
        $allowed_extensions = array(".jpg","jpeg",".png",".gif",'.pdf');
        if(!in_array($extension,$allowed_extensions)){
            echo "<script>alert('Invalid format. Only jpg / jpeg/ png /gif format allowed');</script>";
        } else {
            $imgnewfile=md5($imgfile.time()).$extension;
            move_uploaded_file($_FILES["image"]["tmp_name"],"comnplaintdoc/".$imgnewfile);

            $query="INSERT INTO complaints(ComplainNumber,userId,complaintType,complaintDetails,complaintDoc) 
                    VALUES(?,?,?,?,?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('iisss',$cnumber,$aid,$complainttype,$complaintdetails,$imgnewfile);
            $stmt->execute();
            echo "<script>alert('Complaint registered. Complaint number is: $cnumber');</script>";
            echo "<script type='text/javascript'> document.location = 'my-complaints.php'; </script>";
        }
    } else {
        $query="INSERT INTO complaints(ComplainNumber,userId,complaintType,complaintDetails,complaintDoc) 
                VALUES(?,?,?,?,?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('iisss',$cnumber,$aid,$complainttype,$complaintdetails,$imgfile);
        $stmt->execute();
        echo "<script>alert('Complaint registered. Complaint number is: $cnumber');</script>";
        echo "<script type='text/javascript'> document.location = 'my-complaints.php'; </script>";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Complaint Registration</title>
<link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body>
<?php include('includes/header.php');?>
<div class="ts-main-content">
<?php include('includes/sidebar.php');?>
<div class="content-wrapper">
<div class="container-fluid">
<div class="row">
<div class="col-md-12">
<h2 class="page-title">Register POST</h2>

<div class="panel panel-primary">
<div class="panel-body">
<form method="post" action="" name="complaint" class="form-horizontal" enctype="multipart/form-data">

<div class="form-group">
<label class="col-sm-2 control-label"> Post Type </label>
<div class="col-sm-8">
<select class="form-control" required="required" name="ctype">
<option value="">Select Post Type</option>
<?php
// Fetch all saved Post Names from database
$post_query = "SELECT DISTINCT post_name FROM user_posts WHERE user_id='$aid' ORDER BY post_name ASC";
$result = $mysqli->query($post_query);
if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        echo '<option value="'.$row['post_name'].'">'.htmlspecialchars($row['post_name']).'</option>';
    }
}
?>
</select>
</div>
</div>

<div class="form-group">
<label class="col-sm-2 control-label">Remark </label>
<div class="col-sm-8">
<textarea name="cdetails" id="cdetails" class="form-control" required="required"></textarea>
</div>
</div>

<div class="form-group">
<label class="col-sm-2 control-label">File (if any)</label>
<div class="col-sm-8">
<input type="file" name="image" id="image" class="form-control">
</div>
</div>

<div class="col-sm-6 col-sm-offset-4">
<input type="submit" name="submit" Value="Submit" class="btn btn-primary">
</div>
</form>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</body>
</html>
