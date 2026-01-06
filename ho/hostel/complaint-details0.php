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
$stmt2 = $mysqli->prepare("SELECT * FROM user_posts WHERE user_id=? AND post_name=?");
$stmt2->bind_param('is', $row->userId, $postName);
$stmt2->execute();
$posts = $stmt2->get_result();

// Store all rows in an array for processing
$post_data = [];
while($p = $posts->fetch_assoc()) {
    $post_data[] = $p;
}

// Separate the extra row (अतिरिक्त_पदे) from other rows
$main_rows = [];
$extra_row = null;

foreach ($post_data as $p) {
    if ($p['category'] === 'अतिरिक्त_पदे') {
        $extra_row = $p;
    } else {
        $main_rows[] = $p;
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
                    <?php if(count($main_rows) > 0): ?>
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

                                <!-- ✅ Dynamic Rows (excluding extra row) -->
                                <?php 
                                $show_extra_after = false;
                                foreach($main_rows as $p): 
                                    // Check if this is the row after which we should show the extra row
                                    if ($p['category'] === 'एकूण_भरायची_पदे') {
                                        $show_extra_after = true;
                                    }
                                ?>
                                <tr>
                                    <td><?php echo $p['category']; ?></td>
                                    <td><?php echo $p['col0']; ?></td>
                                    <td><?php echo $p['col1']; ?></td>
                                    <td><?php echo $p['col2']; ?></td>
                                    <td><?php echo $p['col3']; ?></td>
                                    <td><?php echo $p['col4']; ?></td>
                                    <td><?php echo $p['col5']; ?></td>
                                    <td><?php echo $p['col6']; ?></td>
                                    <td><?php echo $p['col7']; ?></td>
                                    <td><?php echo $p['col8']; ?></td>
                                    <td><?php echo $p['col9']; ?></td>
                                    <td><?php echo $p['col10']; ?></td>
                                    <td><?php echo $p['total']; ?></td>
                                </tr>
                                
                                <!-- ✅ Show extra row after एकूण_भरायची_पदे -->
                                <?php if ($show_extra_after && $extra_row): ?>
                                <tr class="extra-row">
                                    <td><?php echo $extra_row['category']; ?></td>
                                    <td><?php echo $extra_row['col0']; ?></td>
                                    <td><?php echo $extra_row['col1']; ?></td>
                                    <td><?php echo $extra_row['col2']; ?></td>
                                    <td><?php echo $extra_row['col3']; ?></td>
                                    <td><?php echo $extra_row['col4']; ?></td>
                                    <td><?php echo $extra_row['col5']; ?></td>
                                    <td><?php echo $extra_row['col6']; ?></td>
                                    <td><?php echo $extra_row['col7']; ?></td>
                                    <td><?php echo $extra_row['col8']; ?></td>
                                    <td><?php echo $extra_row['col9']; ?></td>
                                    <td><?php echo $extra_row['col10']; ?></td>
                                    <td><?php echo $extra_row['total']; ?></td>
                                </tr>
                                <?php 
                                    $show_extra_after = false; // Reset flag after showing
                                    endif;
                                endforeach; 
                                ?>
                            </table>
                            
                            <?php if ($extra_row && !$show_extra_after): ?>
                            <div class="alert alert-info">
                                <strong>Note:</strong> 
                            </div>
                            <?php endif; ?>
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