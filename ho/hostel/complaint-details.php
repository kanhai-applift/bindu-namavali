<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/ho/hostel/includes/config.php');

if(!isset($_GET['cid'])){
    echo "<script>alert('Invalid Request');window.location='new-complaints.php';</script>";
    exit;
}

$cid = intval($_GET['cid']);

// ✅ Approve complaint
if(isset($_POST['submit']))
{
    $cstatus = "Approved"; 
    $redproblem = $_POST['remark'];

    // History insert
    $query="INSERT INTO complainthistory(complaintid, compalintStatus, complaintRemark) VALUES(?,?,?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('iss',$cid,$cstatus,$redproblem);
    $stmt->execute();

    // Update status only
    $query1="UPDATE complaints SET complaintStatus=? WHERE id=?";
    $stmt1 = $mysqli->prepare($query1);
    $stmt1->bind_param('si',$cstatus,$cid);
    $stmt1->execute();
    
    echo "<script>alert('Complaint Approved');</script>";
    echo "<script type='text/javascript'> document.location = 'complaint-details.php?cid=$cid'; </script>";
    exit;
}

// ✅ Fetch complaint info
$ret="SELECT c.*, u.firstName, u.middleName, u.lastName, u.email 
      FROM complaints c
      JOIN userregistration u ON u.id = c.userId
      WHERE c.id=?";
$stmt= $mysqli->prepare($ret);
$stmt->bind_param('i',$cid);
$stmt->execute();
$res=$stmt->get_result();
$row=$res->fetch_object();

if(!$row){
    echo "<script>alert('Complaint not found');window.location='new-complaints.php';</script>";
    exit;
}

// ✅ Fetch user post table
$postName = $row->complaintType;
$stmt2 = $mysqli->prepare("SELECT * FROM user_posts WHERE user_id=? AND post_name=? ORDER BY 
    CASE category 
        WHEN 'मंजूर_पदे' THEN 1
        WHEN 'अतिरिक्त_पदे' THEN 2
        WHEN 'कार्यारत_पदे' THEN 3
        WHEN 'संभाव्य_भरवयाची_पदे' THEN 4
        WHEN 'दिनांक' THEN 5
        WHEN 'एकूण_भरायची_पदे' THEN 6
        ELSE 7
    END");
$stmt2->bind_param('is', $row->userId, $postName);
$stmt2->execute();
$posts = $stmt2->get_result();

// Store all rows in an array for processing
$post_data = [];
while($p = $posts->fetch_assoc()) {
    $post_data[] = $p;
}

// Separate categories for special handling
$mfjur_pade = null;
$atirikt_pade = null;
$karyarat_pade = null;
$sambhavy_pade = null;
$dinank = null;
$ekun_pade = null;

foreach ($post_data as $p) {
    switch($p['category']) {
        case 'मंजूर_पदे':
            $mfjur_pade = $p;
            break;
       
        case 'कार्यारत_पदे':
            $karyarat_pade = $p;
            break;
        case 'संभाव्य_भरवयाची_पदे':
            $sambhavy_pade = $p;
            break;
        case 'दिनांक':
            $dinank = $p;
            break;
 	case 'अतिरिक्त_पदे':
            $atirikt_pade = $p;
            break;
        case 'एकूण_भरायची_पदे':
            $ekun_pade = $p;
            break;
    }
}
?>
<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <title>Complaint Details</title>
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        table, th, td { border: 1px solid black; border-collapse: collapse; text-align:center; padding:5px; }
        th { background:#f2a65a; }
        td:first-child { font-weight:bold; background:#f9e7c4; }
        .status-approved { color: #28a745; font-weight: bold; }
        .extra-row { background-color: #ffebee; font-weight: bold; }
        .total-row { background-color: #e8f5e8; font-weight: bold; }
    </style>
</head>
<body>
<?php include('includes/header.php');?>
<div class="ts-main-content">
    <?php include('includes/sidebar.php');?>
    <div class="content-wrapper">
        <div class="container-fluid">
            <div class="row" id="print">
                <div class="col-md-12">
                    <h2 class="page-title" style="margin-top:3%">प्रकरण क्रमांक #<?php echo $row->ComplainNumber;?> Details</h2>
                    
                    <!-- ✅ User Info -->
                    <div class="panel panel-primary">
                        <div class="panel-heading">User Information</div>
                        <div class="panel-body">
                            <p><strong>Name:</strong> <?php echo trim($row->firstName.' '.$row->middleName.' '.$row->lastName); ?></p>
                            <p><strong>Email:</strong> <?php echo $row->email; ?></p>
                            <p><strong>प्रकरण क्रमांक:</strong> <?php echo $row->ComplainNumber; ?></p>
                            <p><strong>प्रकरण  प्रकार:</strong> <?php echo $row->complaintType; ?></p>
                            <p><strong>Status:</strong> <span class="status-approved"><?php echo $row->complaintStatus ?: "New"; ?></span></p>
                            <p><strong>Registration Date:</strong> <?php echo $row->registrationDate; ?></p>
                            <p><strong>प्रकरण माहिती:</strong> <?php echo $row->complaintDetails; ?></p>
                            <p><strong>File:</strong> 
                                <?php if($row->complaintDoc==''): echo "NA"; 
                                else: ?>
                                <a href="../comnplaintdoc/<?php echo $row->complaintDoc;?>" target="_blank">View File</a>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <!-- ✅ User Post Table -->
                    <?php if(count($post_data) > 0): ?>
                    <div class="panel panel-primary">
                        <div class="panel-heading">User Post Table Data</div>
                        <div class="panel-body">
                            <table class="table table-bordered">
                                <tr>
                                    <th>प्रकार / Category</th>
                                    <th>अनुसूचित जाती</th>
                                    <th>अनुसूचित जमाती</th>
                                    <th>विमुक्त जमाती (अ)</th>
                                    <th>भटक्या जमाती (ब)</th>
                                    <th>भटक्या जमाती (क)</th>
                                    <th>भटक्या जमाती (ड)</th>
                                    <th>विशेष मागास प्रवर्ग</th>
                                    <th>इतर मागास प्रवर्ग</th>
                                    <th>सामाजिक आणि शैक्षणिक मागास वर्ग</th>
                                    <th>आर्थिक दृष्ट्या दुर्बल घटक</th>
                                    <th>अराखीव</th>
                                    <th>Total</th>
                                </tr>

                                <!-- ✅ Fixed Percentage Row -->
                                <tr>
                                    <td>प्रतिशत (%)</td>
                                    <td>13%</td>
                                    <td>7%</td>
                                    <td>3%</td>
                                    <td>2.5%</td>
                                    <td>3.5%</td>
                                    <td>2%</td>
                                    <td>2%</td>
                                    <td>19%</td>
                                    <td>10%</td>
                                    <td>10%</td>
                                    <td>28%</td>
                                    <td>100%</td>
                                </tr>

                                <!-- ✅ मंजूर_पदे -->
                                <?php if($mfjur_pade): ?>
                                <tr>
                                    <td><?php echo $mfjur_pade['category']; ?></td>
                                    <td><?php echo $mfjur_pade['col0']; ?></td>
                                    <td><?php echo $mfjur_pade['col1']; ?></td>
                                    <td><?php echo $mfjur_pade['col2']; ?></td>
                                    <td><?php echo $mfjur_pade['col3']; ?></td>
                                    <td><?php echo $mfjur_pade['col4']; ?></td>
                                    <td><?php echo $mfjur_pade['col5']; ?></td>
                                    <td><?php echo $mfjur_pade['col6']; ?></td>
                                    <td><?php echo $mfjur_pade['col7']; ?></td>
                                    <td><?php echo $mfjur_pade['col8']; ?></td>
                                    <td><?php echo $mfjur_pade['col9']; ?></td>
                                    <td><?php echo $mfjur_pade['col10']; ?></td>
                                    <td><?php echo $mfjur_pade['total']; ?></td>
                                </tr>
                                <?php endif; ?>

                                

                                <!-- ✅ कार्यारत_पदे -->
                                <?php if($karyarat_pade): ?>
                                <tr>
                                    <td><?php echo $karyarat_pade['category']; ?></td>
                                    <td><?php echo $karyarat_pade['col0']; ?></td>
                                    <td><?php echo $karyarat_pade['col1']; ?></td>
                                    <td><?php echo $karyarat_pade['col2']; ?></td>
                                    <td><?php echo $karyarat_pade['col3']; ?></td>
                                    <td><?php echo $karyarat_pade['col4']; ?></td>
                                    <td><?php echo $karyarat_pade['col5']; ?></td>
                                    <td><?php echo $karyarat_pade['col6']; ?></td>
                                    <td><?php echo $karyarat_pade['col7']; ?></td>
                                    <td><?php echo $karyarat_pade['col8']; ?></td>
                                    <td><?php echo $karyarat_pade['col9']; ?></td>
                                    <td><?php echo $karyarat_pade['col10']; ?></td>
                                    <td><?php echo $karyarat_pade['total']; ?></td>
                                </tr>
                                <?php endif; ?>

                                <!-- ✅ संभाव्य_भरवयाची_पदे -->
                                <?php if($sambhavy_pade): ?>
                                <tr>
                                    <td><?php echo $sambhavy_pade['category']; ?></td>
                                    <td><?php echo $sambhavy_pade['col0']; ?></td>
                                    <td><?php echo $sambhavy_pade['col1']; ?></td>
                                    <td><?php echo $sambhavy_pade['col2']; ?></td>
                                    <td><?php echo $sambhavy_pade['col3']; ?></td>
                                    <td><?php echo $sambhavy_pade['col4']; ?></td>
                                    <td><?php echo $sambhavy_pade['col5']; ?></td>
                                    <td><?php echo $sambhavy_pade['col6']; ?></td>
                                    <td><?php echo $sambhavy_pade['col7']; ?></td>
                                    <td><?php echo $sambhavy_pade['col8']; ?></td>
                                    <td><?php echo $sambhavy_pade['col9']; ?></td>
                                    <td><?php echo $sambhavy_pade['col10']; ?></td>
                                    <td><?php echo $sambhavy_pade['total']; ?></td>
                                </tr>
                                <?php endif; ?>

                                <!-- ✅ दिनांक -->
                                <?php if($dinank): ?>
                                <tr>
                                    <td><?php echo $dinank['category']; ?></td>
                                    <td ><?php echo $dinank['col0']; ?></td>

<td ><?php echo $dinank['col1']; ?></td>
<td ><?php echo $dinank['col2']; ?></td>
<td ><?php echo $dinank['col3']; ?></td>
<td ><?php echo $dinank['col4']; ?></td>
<td ><?php echo $dinank['col5']; ?></td>
<td ><?php echo $dinank['col6']; ?></td>
<td ><?php echo $dinank['col7']; ?></td>
<td ><?php echo $dinank['col8']; ?></td>
<td ><?php echo $dinank['col9']; ?></td>
<td ><?php echo $dinank['col10']; ?></td>
                                    <td><?php echo $dinank['total']; ?></td>
                                </tr>
                                <?php endif; ?>
				
				

                                <!-- ✅ एकूण_भरायची_पदे -->
                                <?php if($ekun_pade): ?>
                                <tr class="total-row">
                                    <td><?php echo $ekun_pade['category']; ?></td>
                                    <td><?php echo $ekun_pade['col0']; ?></td>
                                    <td><?php echo $ekun_pade['col1']; ?></td>
                                    <td><?php echo $ekun_pade['col2']; ?></td>
                                    <td><?php echo $ekun_pade['col3']; ?></td>
                                    <td><?php echo $ekun_pade['col4']; ?></td>
                                    <td><?php echo $ekun_pade['col5']; ?></td>
                                    <td><?php echo $ekun_pade['col6']; ?></td>
                                    <td><?php echo $ekun_pade['col7']; ?></td>
                                    <td><?php echo $ekun_pade['col8']; ?></td>
                                    <td><?php echo $ekun_pade['col9']; ?></td>
                                    <td><?php echo $ekun_pade['col10']; ?></td>
                                    <td><?php echo $ekun_pade['total']; ?></td>
                                </tr>
                                <?php endif; ?>

				<!-- ✅ अतिरिक्त_पदे -->
                                <?php if($atirikt_pade): ?>
                                <tr class="extra-row">
                                    <td><?php echo $atirikt_pade['category']; ?></td>
                                    <td><?php echo $atirikt_pade['col0']; ?></td>
                                    <td><?php echo $atirikt_pade['col1']; ?></td>
                                    <td><?php echo $atirikt_pade['col2']; ?></td>
                                    <td><?php echo $atirikt_pade['col3']; ?></td>
                                    <td><?php echo $atirikt_pade['col4']; ?></td>
                                    <td><?php echo $atirikt_pade['col5']; ?></td>
                                    <td><?php echo $atirikt_pade['col6']; ?></td>
                                    <td><?php echo $atirikt_pade['col7']; ?></td>
                                    <td><?php echo $atirikt_pade['col8']; ?></td>
                                    <td><?php echo $atirikt_pade['col9']; ?></td>
                                    <td><?php echo $atirikt_pade['col10']; ?></td>
                                    <td><?php echo $atirikt_pade['total']; ?></td>
                                </tr>
                                <?php endif; ?>

                            </table>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- ✅ Complaint History -->
                    <?php 
                    $query="SELECT * FROM complainthistory WHERE complaintid=? ORDER BY postingDate DESC";
                    $stmt1= $mysqli->prepare($query);
                    $stmt1->bind_param('i',$cid);
                    $stmt1->execute();
                    $res1=$stmt1->get_result();
                    if($res1->num_rows > 0): ?>
                    <div class="panel panel-default">
                        <div class="panel-heading">Complaint History</div>
                        <div class="panel-body">
                            <table class="table table-bordered">
                                <tr><th>Remark</th><th>Status</th><th>Posting Date</th></tr>
                                <?php while($row1=$res1->fetch_object()): ?>
                                <tr>
                                    <td><?php echo $row1->complaintRemark; ?></td>
                                    <td><span class="status-approved"><?php echo $row1->compalintStatus; ?></span></td>
                                    <td><?php echo $row1->postingDate; ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- ✅ Action Buttons -->
                    
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ✅ Action Modal -->
<div id="actionModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h4 class="modal-title">Approve Complaint</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="cstatus" value="Approved">
                    <div class="form-group">
                        <label for="remark">Remarks:</label>
                        <textarea name="remark" rows="6" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="submit" class="btn btn-success">Approve</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>