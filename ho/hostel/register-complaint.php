<?php
session_start();
include('includes/config.php');
date_default_timezone_set('Asia/Kolkata');
include('includes/checklogin.php');
check_login();
$aid=$_SESSION['id'];

if(isset($_POST['submit']))
{
    // Posted Values
    $complainttype=$_POST['ctype'];
    $complaintdetails=$_POST['cdetails'];
    $imgfile=$_FILES["image"]["name"];
    $imgfile2=$_FILES["image2"]["name"];
    $imgfile3=$_FILES["image3"]["name"];
    $cnumber=mt_rand(100000000,999999999);

    // Function to validate and upload files
    function uploadFile($file, $fileInputName) {
        global $mysqli;
        
        if($file != ''):
            // get the image extension
            $extension = substr($file,strlen($file)-4,strlen($file));
            // allowed extensions
            $allowed_extensions = array(".jpg","jpeg",".png",".gif",'.pdf', '.JPG', '.JPEG', '.PNG', '.GIF', '.PDF');
            if(!in_array($extension,$allowed_extensions))
            {
                return array('error' => 'Invalid format. Only jpg / jpeg/ png /gif / pdf format allowed');
            }
            else
            {
                $newfilename = md5($file.time().rand(1000,9999)).$extension;
                move_uploaded_file($_FILES[$fileInputName]["tmp_name"],"comnplaintdoc/".$newfilename);
                return array('success' => $newfilename);
            }
        else:
            return array('success' => "");
        endif;
    }

    // Upload all files
    $file1_result = uploadFile($imgfile, "image");
    $file2_result = uploadFile($imgfile2, "image2");
    $file3_result = uploadFile($imgfile3, "image3");
    
    // Check for errors
    if(isset($file1_result['error']) || isset($file2_result['error']) || isset($file3_result['error'])) {
        $error_msg = "";
        if(isset($file1_result['error'])) $error_msg .= "File 1: " . $file1_result['error'] . "\\n";
        if(isset($file2_result['error'])) $error_msg .= "File 2: " . $file2_result['error'] . "\\n";
        if(isset($file3_result['error'])) $error_msg .= "File 3: " . $file3_result['error'] . "\\n";
        echo "<script>alert('$error_msg');</script>";
    } else {
        // Get file names
        $imgnewfile = $file1_result['success'];
        $imgnewfile2 = $file2_result['success'];
        $imgnewfile3 = $file3_result['success'];
        
        // Update database query to include all files
        $query="insert into complaints(ComplainNumber,userId,complaintType,complaintDetails,complaintDoc,complaintDoc2,complaintDoc3) values(?,?,?,?,?,?,?)";
        $stmt = $mysqli->prepare($query);
        $rc=$stmt->bind_param('iisssss',$cnumber,$aid,$complainttype,$complaintdetails,$imgnewfile,$imgnewfile2,$imgnewfile3);
        $stmt->execute();

        echo "<script>alert('registered number is : $cnumber');</script>";
        echo "<script type='text/javascript'> document.location = 'my-complaints.php'; </script>";
    }
}
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
    <title>Complaint Registration</title>
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap-social.css">
    <link rel="stylesheet" href="css/bootstrap-select.css">
    <link rel="stylesheet" href="css/fileinput.min.css">
    <link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
    <link rel="stylesheet" href="css/style.css">
    <script type="text/javascript" src="js/jquery-1.11.3-jquery.min.js"></script>
    <script type="text/javascript" src="js/validation.min.js"></script>
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

                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-primary">
                            <div class="panel-body">
<form method="post" action="" name="complaint" class="form-horizontal" enctype="multipart/form-data">

<div class="form-group">
<label class="col-sm-2 control-label"> Post Type </label>
<div class="col-sm-8">
<select class="form-control" required="required" name="ctype">
    <option value="">Select Post Type</option>
    <?php
    // Fetch already saved Post Names for this user
    $post_query = "SELECT DISTINCT post_name FROM user_posts WHERE user_id='$aid' ORDER BY post_name ASC";
    $result = $mysqli->query($post_query);
    if($result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            echo '<option value="'.htmlspecialchars($row['post_name']).'">'.htmlspecialchars($row['post_name']).'</option>';
        }
    } else {
        echo '<option value="">No saved Post Names</option>';
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
<label class="col-sm-2 control-label">सेवा प्रवेश नियम </label>
<div class="col-sm-8">
<input type="file" name="image" id="image" class="form-control">
</div>
</div>

<div class="form-group">
<label class="col-sm-2 control-label">आकृतीबंध </label>
<div class="col-sm-8">
<input type="file" name="image2" id="image2" class="form-control">
</div>
</div>

<div class="form-group">
<label class="col-sm-2 control-label">गोषवारा </label>
<div class="col-sm-8">
<input type="file" name="image3" id="image3" class="form-control">
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
</div>
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