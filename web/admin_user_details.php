<?php
define('DEBUG',True);

session_start();
if (!array_key_exists('bbcmicro',$_SESSION)) {
	header("Location: login.php");
	exit;
}

require_once('includes/config.php');
require_once('includes/admin_db_open.php');

require_once('includes/admin_menu.php');

show_admin_menu();
$error="";

# GET params means want to edit a game ...
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
	$user_id=intval($_GET['id']);
} else {
	# POST params mean an update
	if (isset($_POST) && $_POST) {
		if (DEBUG) { echo "<br/>POST<pre>";print_r($_POST);echo "</pre>";}
		$user_id = intval($_POST['id']);
		# Prepare update SQL
		
		if ($user_id == null) {
			# New entry
			$pwhash='';
			$s="INSERT INTO users ( username, description, locked, email, pwhash, lastupdater,lastupdated) VALUES (?,?,?,?,?,?,NOW())";
			if (!empty($_POST['psw'] ) or !empty($_POST['psw2'])) {
				if ($_POST['psw'] == $_POST['psw2']) {
					if (strlen($_POST['psw']) > 8) {
						$pwhash=password_hash($_POST['psw'],PASSWORD_DEFAULT);
					} else {
						$error='<p><i>Password not updated - too short</i></p>';
					}
				} else {
					$error="<p><i>Password not updated - entries don't match</i></p>";
				}
			}
			$bs=[$_POST['username'],$_POST['description'],$_POST['locked'],$_POST['email'],$pwhash,$_SESSION['userid']];
			$sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			if ($sth->execute($bs)) {
				$user_id = $dbh->lastInsertId();
			} else {
				$error=$error."<p>Error creating user.</p>";
			}
		} else {
			# An entry already exists. Compare it.
			$s="SELECT id,username,description,locked,email FROM users where id = ?";

			$sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			$sth->bindParam(1, $user_id, PDO::PARAM_INT);
			$diffs=array();
			$abort=False;
			if ($sth->execute()) {
				$r=$sth->fetch(PDO::FETCH_ASSOC);
				$sth->closeCursor();
				if (DEBUG) { echo "<br/>POST<pre>";print_r($r);echo "</pre>";}
				foreach ($r as $k => $v ) {
					# Will break silently if the structure changes.
					if (array_key_exists($k,$_POST)) {
						$pv = $_POST[$k];
						if ( $v != $pv ) {
							$diffs[$k]=$pv;
							if ($k = 'id') $abort=True;
						}
					} else {
						$abort=True;
						if (DEBUG) { echo "<br/>$k Missing <br/>";}
					}
					
				}
			} else {
				echo "$s gave ".$dbh->errorCode()."<br>\n";
			}
			if (!empty($_POST['psw'] ) or !empty($_POST['psw2'])) {
				if ($_POST['psw'] == $_POST['psw2']) {
					if (strlen($_POST['psw']) > 8) {
						$diffs['pwhash']=password_hash($_POST['psw'],PASSWORD_DEFAULT);
					} else {
						$error='<p><i>Password not updated - too short</i></p>';
					}
				} else {
					$error="<p><i>Password not updated - entries don't match</i></p>";
				}
			}
			if (count($diffs)>0) {
				$diffs['lastupdater']=$_SESSION['userid'];
				$diffs['lastupdated']=$_SESSION['userid'];
				$sql_cmd="update users set ".join('=?, ',array_keys($diffs)).'=NOW() where id = ?';
				$bs=array();
				foreach ($diffs as $b) {
					$bs[]=$b;
				}
				array_pop($bs);
				$bs[]=$user_id;

				if (DEBUG) { echo "<br/><pre>"; print_r($sql_cmd); echo "<br/>";print_r($bs); echo "</pre>"; }
				$sth = $dbh->prepare($sql_cmd,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				if (!$sth->execute($bs)) {
					echo "$sql_cmd gave ".$dbh->errorCode()."<br>\n";
				}
			}
		}
	}
}

if ($user_id) {
	$s="select * from users where id=?";

	$sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$sth->bindParam(1, $user_id, PDO::PARAM_INT);
	if ($sth->execute()) {
		if ($r=$sth->fetch(PDO::FETCH_ASSOC)) {
			make_form($user_id,$r,$error);
		}
		$sth->closeCursor();
	} else {
		echo "$s gave ".$dbh->errorCode()."<br>\n";
	}
} else {
	# Make an empty form
	$r=['id'=>'','username'=>'','description'=>'','locked'=>'','email'=>'','pwhash'=>''];
	make_form(0,$r,$error);
}


function make_form($user_id,$r,$error) {
	echo "<br><b>".$r['username']."</b> ".$r['description']."<hr>";
	echo "$error";
	if (DEBUG) { echo "<br/>POST<pre>";print_r($_POST);echo "</pre>";}
	echo "<form name='frmUser' method='POST' action='admin_user_details.php'>\n";
	echo "<input type='hidden' name='id' value='$user_id'/>\n";
	echo "<label> Username: <input type='text' name='username' size='40' value='".$r['username']."'/></label><br/><br/>";
	echo "<label> User Description <input type='text' name='description' size='40' value='".$r['description']."'/></label><br/><br/>";
	echo "<label> Locked <input type='radio' name='locked' id='sely' value='Y' ";
	if ($r['locked'] == 'Y' ) echo "checked='checked'";
	echo "><label for='sely'>Yes</label>";	
	echo "<input type='radio' name='locked' id='selno' value='N' ";
	if ($r['locked'] != 'Y' ) echo "checked='checked'";
	echo "><label for='selno'>No</label><br/><br/>";
	echo "<label> Email address <input type='text' name='email' size='80' value='".$r['email']."'/></label><br/><br/>";
	echo "<label>Change password:  <input type='password' name='psw'/></label> <label>And again: <input type='password' name='psw2'/></label><br/><br/>";
	echo "<br/><br/><input type='submit' value='Save'/>\n";
	echo "</form>";
}
?>
