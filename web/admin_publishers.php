<?php
require('includes/admin_session.php');
require_once('includes/config.php');
require_once('includes/admin_db_open.php');

require_once('includes/admin_menu.php');

show_admin_menu();

$s="	SELECT 		id,name
		FROM 		publishers
		ORDER BY 	name";

$sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
if ($sth->execute()) {
	if ($sth->rowCount()) {
		echo '<p>'.$sth->rowCount()." publishers. <a href='admin_publishers_details.php?id=0'>New publisher</a></p><hr>";
		echo "<table>\n";
		echo "<tr><td><b>ID</b></td><td><b>Name</b></td></tr>\n";
		while ($r=$sth->fetch()) {
			echo "<tr><td>".$r['id']."</td><td><a href=admin_publishers_details.php?id=".$r['id'].">".$r['name']."</a></td></tr>\n";
		}
		echo "</table>\n";
	}
	$sth->closeCursor();
} else {
	echo "$s gave ".$dbh->errorCode()."<br>\n";
}
?>
