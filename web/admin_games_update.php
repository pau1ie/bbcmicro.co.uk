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

//echo "<pre>";
//print_r($_POST);
//echo "</pre>";

$s="	SELECT 		id,title,year,
			(select filename from images where main=100 and gameid = games.id) as disc,electron,";
		$s=$s."
			(SELECT GROUP_CONCAT(CONCAT(publishers.id,'|',publishers.name) SEPARATOR '@') FROM games_publishers LEFT JOIN publishers ON pubid=publishers.id WHERE gameid=games.id) AS publishers
	FROM 		games 
	ORDER BY 	title";

$sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

echo "<pre>";
if (isset($_POST['games'])) {
	if ($sth->execute()) {
		$u='update games set electron = ? where id = ?';
		$sthu=$dbh->prepare($u,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		while ($r=$sth->fetch(PDO::FETCH_ASSOC)) {
			$id=$r['id'];
			if ((($r['electron'] == 'Y') && (!isset($_POST['games'][$id]))) ||
			       (isset($_POST['games'][$id]) && ($r['electron'] !='Y'))) {
				print_r($r);
				echo "Update needed!";
				if (isset($_POST['games'][$id])) {
					$y='Y';
				} else {
					$y=null;
				}
				$sthu->bindParam(1, $y, PDO::PARAM_STR,1);
				$sthu->bindParam(2, $id, PDO::PARAM_INT);
				if (!$sthu->execute()) {
					echo "Error running ".$u." on ".$r['id'];
				}
			}	
		}
	} else {
		echo "Error updating";
	}			
}
echo "</pre>";

if ($sth->execute()) {
	echo $sth->rowCount()." games. <a href='admin_game_details.php?id=0'>New game</a><hr>";
	if ($sth->rowCount()) {
		echo "<form name='frmGame' method='POST' action='admin_games_update.php'>\n";
		echo "<table>\n";
		echo "<tr><td><b>Title</b></td><td><b>Year</b></td><td><b>Publisher</b></td><td><b>Disc</b></td>";
		echo "<td><b>Electron</b></td>";
		echo "</tr>\n";
		while ($r=$sth->fetch(PDO::FETCH_ASSOC)) {
			if ($r['id']) {
				$t=$r['id'];
			} else {
				$t='<i>None</i>';
			}
			echo "<tr><td><a href='admin_game_details.php?id=".$r['id']."'>".$r['title']."</td><td>".$r['year']."</td>";

			echo "<td>";
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
			echo "</td>";

			echo "<td><a href='admin_file.php?t=d&id=".$r['id']."'>";
			if (empty($r['disc'])) echo "None"; else echo $r['disc'];
			echo "</a></td>";

			echo "<td>";
		        if ( $r['electron'] == 'Y' ) {
				$checked='checked';
			} else {
				$checked='';
			}
			echo '<input type="checkbox" name="games['.$r['id'].'][electron]" '.$checked.' value="Y"/>';
			echo "</label>";
			echo "</td>";

			echo "</tr>\n";
		}
		echo "</table>\n";
		echo "<br><br><input type='submit' value='Save'>\n";
		echo "</form>\n";
	}
} else {
	echo "$s gave ".$dbh->errorCode()."<br>\n";
}
$sth->closeCursor();
?>
