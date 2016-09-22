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


$s="	SELECT 		id,title,publisher,year,
					(SELECT GROUP_CONCAT(CONCAT(authors.id,'|',authors.name) SEPARATOR '@') FROM games_authors LEFT JOIN authors ON authors_id=authors.id WHERE games_id=games.id) AS authors
		FROM 		games 
		ORDER BY 	title";
$q=@$dbh->query($s);
if (!$dbh->errno) {
	if ($q->num_rows) {
		echo "$q->num_rows games<hr>";
		echo "<table>\n";
		echo "<tr><td><b>Title</b></td><td><b>Publisher</b></td><td><b>Year</b></td><td><b>Authors</b></tr>\n";
		while ($r=$q->fetch_object()) {
			echo "<tr><td>$r->title</td><td>$r->publisher</td><td>$r->year</td>";

			echo "<td><a href='admin_game_author.php?id=$r->id'>";
			$authors=explode('@',$r->authors);
			$names='';
			foreach ($authors as $author) {
				if ($author) {
					list($id,$name)=explode('|',$author);
					if ($name) $names.="$name, ";
				}
			}
			if ($names) {
				echo substr($names,0,strlen($names)-2);
			} else {
				echo "<i>None</i>";
			}
			echo "</a></td>";
			echo "</tr>\n";
		}
		echo "</table>\n";
	}
	$q->free_result();
} else {
	echo "$s gave ".$dbh->error."<br>\n";
}
$dbh->close();
?>
