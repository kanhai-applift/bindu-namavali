<?php
session_start();
session_destroy();
require_once(__DIR__.'/../includes/helper.php');
header("Location: ".baseUrl('login'));
exit;
