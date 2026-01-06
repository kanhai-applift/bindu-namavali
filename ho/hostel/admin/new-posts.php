<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ho/hostel/admin/includes/header.php'); 
?>

    <div class="ts-main-content">
        <?php include('includes/sidebar.php');?>
        <div class="content-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <h2 class="page-title" style="margin-top:4%">New Post / भरवयाची पदे</h2>
                        <div class="panel panel-primary">
                            <div class="panel-heading">New Post Details</div>
                            <div class="panel-body">
                                <table id="zctb" class="display table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Sno.</th>
                                            <th>Case Number</th>
                                            <th>Post Type</th>
                                            <th>User Name</th>
                                            <th>User Email</th>
                                            <th>Post Status</th>
                                            <th>Registration Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th>Sno.</th>
                                            <th>Complaint Number</th>
                                            <th>Post Type</th>
                                            <th>User Name</th>
                                            <th>User Email</th>
                                            <th>Post Status</th>
                                            <th>Registration Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
<?php
$aid = $_SESSION['id'];
// Fetch complaints with user info
$query = "SELECT c.*, u.firstName, u.middleName, u.lastName, u.email
          FROM complaints c
          JOIN userregistration u ON u.id = c.userId
          WHERE c.complaintStatus IS NULL
          ORDER BY c.registrationDate DESC";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$res = $stmt->get_result();
$cnt = 1;
while ($row = $res->fetch_object()) {
    $fullName = trim($row->firstName . ' ' . $row->middleName . ' ' . $row->lastName);
?>
<tr>
    <td><?php echo $cnt; ?></td>
    <td><?php echo $row->ComplainNumber; ?></td>
    <td><?php echo $row->complaintType; ?></td>
    <td><?php echo $fullName; ?></td>
    <td><?php echo $row->email; ?></td>
    <td><?php echo $row->complaintStatus ? $row->complaintStatus : "New"; ?></td>
    <td><?php echo $row->registrationDate; ?></td>
    <td>
        <a href="post-details.php?cid=<?php echo $row->id;?>" title="View Full Details">
            <i class="fa fa-desktop"></i>
        </a>
    </td>
</tr>
<?php
$cnt++;
}
?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap-select.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.dataTables.min.js"></script>
    <script src="js/dataTables.bootstrap.min.js"></script>
    <script src="js/Chart.min.js"></script>
    <script src="js/fileinput.js"></script>
    <script src="js/chartData.js"></script>
    <script src="js/main.js"></script>
    <script>
        $(document).ready(function() {
            $('#zctb').DataTable();
        });
    </script>
</body>
</html>
