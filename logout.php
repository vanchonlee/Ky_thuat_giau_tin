<?php
	error_reporting(0);
	date_default_timezone_set('asia/ho_chi_minh');
	if(!isset($_SESSION)) session_start();
	unset($_SESSION['user']);
	header("location: index.php");
?>