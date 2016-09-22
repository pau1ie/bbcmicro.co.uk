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
		FROM 		genres
		ORDER BY 	name";
$q=@$dbh->query($s);
if (!$dbh->errno) {
	if ($q->num_rows) {
		echo "$q->num_rows genres<hr>";
		echo "<table>\n";
		echo "<tr><td><b>ID</b></td><td><b>Name</b></td></tr>\n";
		while ($r=$q->fetch_object()) {
			echo "<tr><td>$r->id</td><td>$r->name</td></tr>\n";
		}
		echo "</table>\n";
	}
	$q->free_result();
} else {
	echo "$s gave ".$dbh->error."<br>\n";
}
$dbh->close();
?>
