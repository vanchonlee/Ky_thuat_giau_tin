<?php
	error_reporting(0);
	date_default_timezone_set('asia/ho_chi_minh');
	if (!isset($_SESSION)) session_start();
	if (isset($_POST["loginbtn"]) && $_POST["guesttoken"] === $_SESSION["guesttoken"]) {
		try{
			$id = $_POST["loginid"];
			$pw = $_POST["loginpw"];
			include "connectdb.php";
			$qr = $conn->prepare("select id from user where id = :id and password = :pw limit 1;");
			$qr->bindParam(":id", $id, PDO::PARAM_STR);
			$qr->bindParam(":pw", $pw, PDO::PARAM_STR);
			$qr->execute();
			if ($qr->rowCount() === 1){
				$row = $qr->fetch();
				$_SESSION["user"] = $row["id"];
				echo "login success";
			}
			else{
				echo "login failure";
			}
		}
		catch (Exception $ex){
			echo "login failure";
		}
	}
	else if (isset($_POST["signupbtn"]) && $_POST["guesttoken"] === $_SESSION["guesttoken"]) {
		try{
			$id = $_POST["signupid"];
			$pw = $_POST["signuppw"];
			include "connectdb.php";
			$qr = $conn->prepare("select id from user where id = :id limit 1;");
			$qr->bindParam(":id", $id, PDO::PARAM_STR);
			$qr->execute();
			if ($qr->rowCount() === 0){
				$qr = $conn->prepare("insert into user (id, password, permission) values (:id, :pw, 'user');");
				$qr->bindParam(":id", $id, PDO::PARAM_STR);
				$qr->bindParam(":pw", sha1($pw), PDO::PARAM_STR);
				$qr->execute();
				echo "signup success";
			}
			else{
				echo "signup failure";
			}
		}
		catch (Exception $ex){
			echo "signup failure";
		}
	}
	$_SESSION["guesttoken"] = bin2hex(random_bytes(16));
?>
