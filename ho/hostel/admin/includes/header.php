<?php 
if($_SESSION['id']) { 
    // fetch username from DB
    $uid = $_SESSION['id'];
    $stmt = $mysqli->prepare("SELECT username FROM users WHERE id=?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $stmt->bind_result($username);
    $stmt->fetch();
    $stmt->close();
?>
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
