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

echo "<hr>";

make_link('Edit Authors (TODO)','admin_authors.php','authors');
make_link('Edit Games (TODO) including linking to authors','admin_games.php','games');
make_link('Edit Genres (TODO)','admin_genres.php','genres');
make_link('Edit Publishers (TODO)','admin_publishers.php','publishers');

exit;

function make_link($text,$url,$table) {
	global $dbh;

	$count=0;
	$s="SELECT COUNT(*) AS entity_count FROM $table";
	$q=$dbh->query($s);
	if (!$dbh->errno) {
		if ($r=$q->fetch_object()) {
			$count=$r->entity_count;
		}
	} else {
		echo "$s gave $dbh->error<br>\n";
	}

	echo "<a href='$url'>$text</a> $count<br>";
}
?>
