<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ho/hostel/admin/includes/header.php'); 
?>

	<div class="ts-main-content">
			<?php include('includes/sidebar.php');?>
		<div class="content-wrapper">
			<div class="container-fluid">
				<div class="row">
					<div class="col-md-12">
						<h2 class="page-title" style="margin-top:4%">Closed Post</h2>
						<div class="panel panel-default">
							<div class="panel-heading">Post Details</div>
							<div class="panel-body">
								<table id="zctb" class="display table table-striped table-bordered table-hover" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th>Sno.</th>
											<th>Post Number</th>
											<th>Post Type</th>
											<th>Post Status</th>
											<th>Post Reg. Date</th>
											<th>Action</th>
										</tr>
									</thead>
									<tfoot>
										<tr>
											<th>Sno.</th>
											<th>Post Number</th>
											<th>Post Type</th>
											<th>Post Status</th>
											<th>Post Reg. Date</th>
											<th>Action</th>
										</tr>
									</tfoot>
									<tbody>
<?php	
$aid=$_SESSION['id'];
$ret="select * from complaints where complaintStatus='Closed'";
$stmt= $mysqli->prepare($ret) ;
$stmt->execute() ;//ok
$res=$stmt->get_result();
$cnt=1;
while($row=$res->fetch_object())
	  {
	  	?>
<tr><td><?php echo $cnt;;?></td>
<td><?php echo $row->ComplainNumber;?></td>
<td><?php echo $row->complaintType;?></td>
<td><?php $cstatus=$row->complaintStatus;
if($cstatus==''):
	echo "New";
else:
echo $cstatus;
endif;	

?></td>
<td><?php echo $row->registrationDate;?></td>

<td>
<a href="complaint-details.php?cid=<?php echo $row->id;?>" title="View Full Details"><i class="fa fa-desktop"></i></a>&nbsp;&nbsp;
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
