<?php
function show_admin_menu($current_page='') {
	$space='&nbsp;&nbsp;&nbsp;';
	echo "<a href='admin_index.php'>Admin Home</a>$space";
	echo "<a href='admin_games.php'>Games List</a>$space";
	echo "<a href='logout.php'>Logout</a>";
	echo "<br>\n";
}
?>
