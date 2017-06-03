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


$s="	SELECT 		id,title,year,
			(select filename from images where main=100 and gameid = games.id) as disc,
			(select filename from screenshots where main=100 and gameid = games.id) as screenshot,
			(SELECT GROUP_CONCAT(CONCAT(publishers.id,'|',publishers.name) SEPARATOR '@') FROM games_publishers LEFT JOIN publishers ON pubid=publishers.id WHERE gameid=games.id) AS publishers,
			(SELECT GROUP_CONCAT(CONCAT(authors.id,'|',authors.name) SEPARATOR '@') FROM games_authors LEFT JOIN authors ON authors_id=authors.id WHERE games_id=games.id) AS authors,
			(SELECT GROUP_CONCAT(CONCAT(genres.id,'|',genres.name) SEPARATOR '@') FROM game_genre LEFT JOIN genres ON genreid=genres.id WHERE gameid=games.id) AS genres
	FROM 		games 
	ORDER BY 	title";

$sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
if ($sth->execute()) {
	if ($sth->rowCount()) {
		echo $sth->rowCount()." games. <a href='admin_game_details.php?id=0'>New game</a><hr>";
		echo "<table>\n";
		echo "<tr><td><b>Title</b></td><td><b>Year</b><td><b>Publisher</b></td></td>";
//		echo "<td><b>Authors</b></td>";
		echo "<td><b>Screenshot</b></td><td><b>Disc</b></td>";
		echo "</tr>\n";
		while ($r=$sth->fetch(PDO::FETCH_ASSOC)) {
			echo "<tr><td>".$r['title']."</td><td>".$r['year']."</td>";

			echo "<td><a href='admin_game_details.php?id=".$r['id']."'>";
			$pubs=explode('@',$r['publishers']);
			$names='';
			foreach ($pubs as $pub) {
				if ($pub) {
					list($id,$name)=explode('|',$pub);
					if ($name) $names.="$name, ";
				}
			}
			if ($names) {
				echo substr($names,0,strlen($names)-2);
			} else {
				echo "<i>None</i>";
			}
			echo "</a></td>";

//			echo "<td><a href='admin_game_details.php?id=".$r['id']."'>";
//			$authors=explode('@',$r['authors']);
//			$names='';
//			foreach ($authors as $author) {
//				if ($author) {
//					list($id,$name)=explode('|',$author);
//					if ($name) $names.="$name, ";
//				}
//			}
//			if ($names) {
//				echo substr($names,0,strlen($names)-2);
//			} else {
//				echo "<i>None</i>";
//			}
//			echo "</a></td>";
			echo "<td><a href='admin_file.php?t=s&id=".$r['id']."'>".$r['screenshot']."</a></td>";
			echo "<td><a href='admin_file.php?t=d&id=".$r['id']."'>".$r['disc']."</a></td>";
			echo "</tr>\n";
		}
		echo "</table>\n";
	}
} else {
	echo "$s gave ".$dbh->errorCode()."<br>\n";
}
$sth->closeCursor();
?>
