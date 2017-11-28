<?php
require('includes/admin_session.php');
require_once('includes/config.php');
require_once('includes/admin_db_open.php');

require_once('includes/admin_menu.php');

show_admin_menu();

$s='SELECT id,name,
 (select count(*) from games where genre = genres.id) as "primary",
 (select count(*) from game_genre where genreid = genres.id) as secondary 
FROM genres ORDER BY name';

$sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
if ($sth->execute()) {
	if ($sth->rowCount()) {
		echo '<p>'.$sth->rowCount()." genres. <a href='admin_genres_details.php?id=0'>New genre</a></p><hr>";
		echo "<table>\n";
		echo "<tr><td><b>ID</b></td><td><b>Name</b></td><td> #Primary </td><td> #Secondary </td></tr>\n";
		while ($r=$sth->fetch()) {
			echo "<tr><td>".$r['id']."</td><td><a href=admin_genres_details.php?id=".$r['id'].">".$r['name']."</a></td><td>".$r['primary']."</td><td>".$r['secondary']."</td></tr>\n";
		}
		echo "</table>\n";
	}
	$sth->closeCursor();
} else {
	echo "$s gave ".$dbh->errorCode()."<br>\n";
}
?>
