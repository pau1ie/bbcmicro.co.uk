<?php
session_start();
unset($_SESSION['bbcmicro']);
session_destroy();
header("Location: login.php");
exit;
?>
