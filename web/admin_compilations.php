<?php
require('includes/admin_session.php');
require_once('includes/config.php');
require_once('includes/admin_db_open.php');

require_once('includes/admin_menu.php');

show_admin_menu();

$s='SELECT id,name
FROM compilations ORDER BY name';


$sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
if ($sth->execute()) {
	echo '<p>'.$sth->rowCount()." compilations. <a href='admin_compilations_details.php?id=0'>New Compilation</a></p><hr>";
	if ($sth->rowCount()) {
		echo "<table>\n";
		echo "<tr><td><b>ID</b></td><td><b>Name</b></td><td> </td><td> </td></tr>\n";
		while ($r=$sth->fetch()) {
			echo "<tr><td>".$r['id']."</td><td><a href=admin_compilations_details.php?id=".$r['id'].">".$r['name']."</a></td><td></td><td></td></tr>\n";
		}
		echo "</table>\n";
	}
	$sth->closeCursor();
} else {
	echo "$s gave ".$dbh->errorCode()."<br>\n";
}
?>
