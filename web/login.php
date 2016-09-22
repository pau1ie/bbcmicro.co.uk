<?php
require_once('includes/config.php');
session_start();
if (array_key_exists('bbcmicro',$_SESSION)) {
	unset($_SESSION['bbcmicro']);
}

echo "<html><body onload='document.forms[0].user.focus();'>\n";

# If there is $_POST data, process it as a login request
if (isset($_POST) && $_POST) {
	if (isset($_POST['user']) && isset($_POST['pass'])) {
		if ($_POST['user']==ADMIN_USER && password_verify($_POST['pass'],ADMIN_PASS)) {
			$_SESSION['bbcmicro']='logged_in';
			header("Location: admin_index.php");
			exit;
		}
	}
	echo "<font color='red'>Invalid user or password</font><br>\n";
}

# Otherwise display the login form

echo "<form method='POST' action='login.php'>\n";
echo "<table>\n";
echo "<tr><td>User</td><td><input type='text' name='user'></td></tr>\n";
echo "<tr><td>Password</td><td><input type='password' name='pass'></td></tr>\n";
echo "<tr><td colspan='2'>&nbsp;</td></tr>\n";
echo "<tr><td>&nbsp;</td><td><input type='submit' value='Login'</td></tr>\n";
echo "</table>\n";
echo "</form>\n";

echo "</body>\n</html>";
?>
