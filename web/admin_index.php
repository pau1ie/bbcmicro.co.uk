<?php
require('includes/admin_session.php');
require_once('includes/config.php');
require_once('includes/admin_db_open.php');

require_once('includes/admin_menu.php');
show_admin_menu();

echo "<hr>";

make_link('Edit Authors','admin_authors.php','authors');
make_link('Edit Games including linking to authors','admin_games.php','games');
make_link('Bulk Edit Games including linking to authors','admin_games_update.php','games');
make_link('Edit Genres','admin_genres.php','genres');
make_link('Edit Compilations','admin_compilations.php','compilations');
make_link('Edit Publishers','admin_publishers.php','publishers');
make_link('Edit Admin Users','admin_users.php','users');

exit;

function make_link($text,$url,$table) {
	global $dbh;

	$count=0;
	$s="SELECT COUNT(*) AS entity_count FROM $table";

	$sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	if ($sth->execute()) {
		if ($r=$sth->fetch()) {
			$count=$r['entity_count'];
		}
		$sth->closeCursor();
	} else {
		echo "$s gave ".$dbh->errorCode()."<br>\n";
	}

	echo "<a href='$url'>$text</a> $count<br>";
}
?>
