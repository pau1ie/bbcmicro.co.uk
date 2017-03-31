<?php
session_start();
if (!array_key_exists('bbcmicro',$_SESSION)) {
	header("Location: login.php");
	exit;
}

require_once('includes/config.php');
require_once('includes/admin_db_open.php');

require_once('includes/admin_menu.php');

show_admin_menu();

$s="SELECT	id,username,description,locked,email,pwhash
		FROM 		users
		ORDER BY 	username";

$sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
if ($sth->execute()) {
	if ($sth->rowCount()) {
		echo $sth->rowCount()." administrators	<a href='admin_user_details.php?id=0'>New user</a><hr>";
		echo "<table>\n";
		echo "<tr><td><b>ID</b></td><td><b>Username</b></td><td><b>Name</b></td><td><b>Locked</b></td><td><b>Email address</b></td></tr>\n";
		while ($r=$sth->fetch()) {
			echo "<tr><td><a href='admin_user_details.php?id=".$r['id']."'>".$r['id']."</a></td><td><a href='admin_user_details.php?id=".$r['id']."'>".$r['username']."</td><td>".$r['description']."</td><td>".$r['locked']."</td><td>".$r['email']."</td></tr>\n";
		}
		echo "</table>\n";
	}
	$sth->closeCursor();
} else {
	echo "$s gave ".$dbh->errorCode()."<br>\n";
}
?>
