<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ho/hostel/admin/includes/header.php');

$deleteMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {

    // CSRF validation
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    // Validate id
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        $deleteMessage = 'Invalid user ID.';
    } else {

        // OPTIONAL: Authorization check
        // if (!isAdmin()) { die('Unauthorized'); }

        $sql = "DELETE FROM userregistration WHERE id = ?";
        $stmt = $mysqli->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('i', $id);

            if ($stmt->execute()) {
                $deleteMessage = 'User deleted successfully.';
            } else {
                $deleteMessage = 'Failed to delete user.';
            }

            $stmt->close();
        } else {
            $deleteMessage = 'Database error.';
        }
    }
}

?>


	<div class="ts-main-content">
			<?php include('includes/sidebar.php');?>
		<div class="content-wrapper">
			<div class="container-fluid">
				<div class="row">
					<div class="col-md-12">
						<h2 class="page-title" style="margin-top:4%">Manage Registered Users </h2>
						<div class="panel panel-default">
							<div class="panel-heading">All Users</div>
							<?php if (!empty($deleteMessage)) : ?>
									<div class="alert alert-info">
											<?php echo htmlspecialchars($deleteMessage); ?>
									</div>
							<?php endif; ?>
							<div class="panel-body">
								<table id="zctb" class="display table table-striped table-bordered table-hover" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th>Sno.</th>
											<th>Name</th>
											<th>Reg no</th>
											<th>Phone</th>
											<th>Email </th>
											<th>Action</th>
										</tr>
									</thead>
									<tfoot>
										<tr>
											<th>Sno.</th>
											<th> Name</th>
											<th>Reg no</th>
											<th>Phone</th>
											<th>Email </th>
											<th>Action</th>
										</tr>
									</tfoot>
									<tbody>
<?php	
$aid=$_SESSION['id'];
$ret="select * from userregistration";
$stmt= $mysqli->prepare($ret) ;
//$stmt->bind_param('i',$aid);
$stmt->execute() ;//ok
$res=$stmt->get_result();
$cnt=1;
while($row=$res->fetch_object())
	  {
	  	?>
<tr><td><?php echo $cnt;;?></td>
<td><?php echo $row->firstName;?><?php echo $row->middleName;?><?php echo $row->lastName;?></td>
<td><?php echo $row->regNo;?></td>
<td><?php echo $row->contactNo;?></td>
<td><?php echo $row->email;?></td>
<td>
<!-- <a href="user-details.php?regno=<?php echo $row->regno;?>" title="View Full Details"><i class="fa fa-desktop"></i></a>&nbsp;&nbsp; -->
<form method="post" style="display:inline;" 
      onsubmit="return confirm('Do you want to delete this user?');">

    <input type="hidden" name="id" value="<?php echo $row->id; ?>">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

    <button type="submit" name="delete_user" class="btn btn-link p-0" title="Delete Record">
        <i class="fa fa-close text-danger"></i>
    </button>
</form>
</td>
										</tr>
									<?php
$cnt=$cnt+1;
									 } ?>
											
										
									</tbody>
								</table>

								
							</div>
						</div>

					
					</div>
				</div>

			

			</div>
		</div>
	</div>

<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ho/hostel/admin/includes/footer.php');
?>
