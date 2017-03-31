<?php
require_once('includes/config.php');
require_once('includes/admin_db_open.php');

session_start();
if (array_key_exists('bbcmicro',$_SESSION)) {
	unset($_SESSION['bbcmicro']);
}

echo "<html><body onload='document.forms[0].user.focus();'>\n";

# If there is $_POST data, process it as a login request
if (isset($_POST) && $_POST) {
	if (isset($_POST['user']) && isset($_POST['pass'])) {
		$s='select * from users where username = ?';
		$sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->bindParam(1, $_POST['user'], PDO::PARAM_INT);
		if ($sth->execute()) {
			$r=$sth->fetch(PDO::FETCH_ASSOC);
			$sth->closeCursor();
			if ($_POST['user']==$r['username'] && password_verify($_POST['pass'],$r['pwhash']) && $r['locked']=='N') {
				$_SESSION['bbcmicro']='logged_in';
				$_SESSION['username']=$r['username'];
				$_SESSION['userid']=$r['id'];
				header("Location: admin_index.php");
				exit;
			}
		} else {
			echo "An error occurred";
		}
	}
	echo "<font color='red'>Invalid user or password</font><br>\n";
#	echo "<pre>";
#	print_r($r);
#	print_r($_POST);
#	echo "</pre>";
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
