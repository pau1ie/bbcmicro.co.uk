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

$s="	SELECT 		id,name
		FROM 		authors
		ORDER BY 	name";

$sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
if ($sth->execute()) {
	if ($sth->rowCount()) {
		echo $sth->rowCount()." authors<hr>";
		echo "<table>\n";
		echo "<tr><td><b>ID</b></td><td><b>Name</b></td></tr>\n";
		while ($r=$sth->fetch()) {
			echo "<tr><td>".$r['id']."</td><td>".$r['name']."</td></tr>\n";
		}
		echo "</table>\n";
	}
	$sth->closeCursor();
} else {
	echo "$s gave ".$dbh->errorCode()."<br>\n";
}
?>
