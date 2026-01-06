<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ho/hostel/admin/includes/header.php');
if (isset($_POST['submit'])) {
	$regno = $_POST['regno'];
	$fname = $_POST['fname'];
	$mname = $_POST['mname'];
	$lname = $_POST['lname'];
	$gender = $_POST['gender'];
	$contactno = $_POST['contact'];
	$emailid = $_POST['email'];
	$password = $_POST['password'];
	$district = $_POST['district'];
	$result = "SELECT count(*) FROM userregistration WHERE email=? || regNo=?";
	$stmt = $mysqli->prepare($result);
	$stmt->bind_param('ss', $email, $regno);
	$stmt->execute();
	$stmt->bind_result($count);
	$stmt->fetch();
	$stmt->close();
	if ($count > 0) {
		echo "<script>alert('Registration number or email id already registered.');</script>";
	} else {

		$query = "insert into  userregistration (regNo,firstName,middleName,lastName,district,contactNo,email,password) values(?,?,?,?,?,?,?,?)";
		$stmt = $mysqli->prepare($query);
		$rc = $stmt->bind_param('sssssiss', $regno, $fname, $mname, $lname, $district, $contactno, $emailid, $password);
		$stmt->execute();
		echo "<script>alert('Succssfully registered');</script>";
	}
}
?>
<div class="ts-main-content">
	<?php include('includes/sidebar.php'); ?>
	<div class="content-wrapper">
		<div class="container-fluid">

			<div class="row">
				<div class="col-md-12">

					<h2 class="page-title pt-2x">Registration </h2>

					<div class="row">
						<div class="col-md-12">
							<div class="panel panel-primary">
								<div class="panel-heading">Fill all Info</div>
								<div class="panel-body">
									<form method="post" action="" name="registration" class="form-horizontal" onSubmit="return valid();">



										<div class="form-group">
											<label class="col-sm-2 control-label"> Registration No : </label>
											<div class="col-sm-8">
												<input type="text" name="regno" id="regno" class="form-control" required="required" onBlur="checkRegnoAvailability()">
												<span id="user-reg-availability" style="font-size:12px;"></span>
											</div>
										</div>


										<div class="form-group">
											<label class="col-sm-2 control-label">कार्यालय / संस्थेचे नाव : </label>
											<div class="col-sm-8">
												<input type="text" name="fname" id="fname" class="form-control" required="required">
											</div>
										</div>

										<div class="form-group">
											<label class="col-sm-2 control-label">कार्यालय प्रमुखाचे नाव : </label>
											<div class="col-sm-8">
												<input type="text" name="mname" id="mname" class="form-control">
											</div>
										</div>

										<div class="form-group" style="display: none;">
											<label class="col-sm-2 control-label">Last Name : </label>
											<div class="col-sm-8">
												<input type="text" name="lname" id="lname" class="form-control" value="D" required="required">
											</div>
										</div>



										<!-- District only -->
										<div class="form-group">
											<label class="col-sm-2 control-label">जिल्हा :</label>
											<div class="col-sm-8">
												<select name="district" class="form-control" required="required">
													<option value="">Select</option>
													<option value="AMRAVATI">AMRAVATI</option>
													<option value="AKOLA">AKOLA</option>
													<option value="BULDHANA">BULDHANA</option>
													<option value="WASHIM">WASHIM</option>
													<option value="YAVATMAL">YAVATMAL</option>
												</select>
											</div>
										</div>


										<div class="form-group">
											<label class="col-sm-2 control-label">Contact No : </label>
											<div class="col-sm-8">
												<input type="text" name="contact" id="contact" class="form-control" required="required">
											</div>
										</div>


										<div class="form-group">
											<label class="col-sm-2 control-label">Email id: </label>
											<div class="col-sm-8">
												<input type="email" name="email" id="email" class="form-control" onBlur="checkAvailability()" required="required">
												<span id="user-availability-status" style="font-size:12px;"></span>
											</div>
										</div>

										<div class="form-group">
											<label class="col-sm-2 control-label">Password: </label>
											<div class="col-sm-8">
												<input type="password" name="password" id="password" class="form-control" required="required">
											</div>
										</div>


										<div class="form-group">
											<label class="col-sm-2 control-label">Confirm Password : </label>
											<div class="col-sm-8">
												<input type="password" name="cpassword" id="cpassword" class="form-control" required="required">
											</div>
										</div>




										<div class="col-sm-6 col-sm-offset-4">
											<button class="btn btn-default" type="reset">Reset</button>
											<input type="submit" name="submit" Value="Register" class="btn btn-primary">
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
<script type="text/javascript" src="js/validation.min.js"></script>
<script type="text/javascript">
	function valid() {
		if (document.registration.password.value != document.registration.cpassword.value) {
			alert("Password and Re-Type Password Field do not match  !!");
			document.registration.cpassword.focus();
			return false;
		}
		return true;
	}
</script>
<script>
	function checkAvailability() {

		$("#loaderIcon").show();
		jQuery.ajax({
			url: "check_availability.php",
			data: 'emailid=' + $("#email").val(),
			type: "POST",
			success: function(data) {
				$("#user-availability-status").html(data);
				$("#loaderIcon").hide();
			},
			error: function() {
				event.preventDefault();
				alert('error');
			}
		});
	}
</script>
<script>
	function checkRegnoAvailability() {

		$("#loaderIcon").show();
		jQuery.ajax({
			url: "check_availability.php",
			data: 'regno=' + $("#regno").val(),
			type: "POST",
			success: function(data) {
				$("#user-reg-availability").html(data);
				$("#loaderIcon").hide();
			},
			error: function() {
				event.preventDefault();
				alert('error');
			}
		});
	}
</script>

</body>

</html>