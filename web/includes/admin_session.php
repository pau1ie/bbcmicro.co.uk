<?php
ini_set('session.cookie_httponly', 1);
session_start();
if (!array_key_exists('bbcmicro',$_SESSION)) {
	header("Location: login.php");
	exit;
}
?>
