<?php

define('DEBUG',false);

session_start();
if (!array_key_exists('bbcmicro',$_SESSION)) {
	header("Location: login.php");
	exit;
}

require_once('includes/config.php');
require_once('includes/admin_db_open.php');

require_once('includes/admin_menu.php');
show_admin_menu();

# GET params means want to edit a game ...
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
	$game_id=intval($_GET['id']);
} else {
	# POST params mean an update
	if (isset($_POST) && $_POST) {
		if (isset($_POST['id']) && is_numeric($_POST['id'])) {
			$game_id=intval($_POST['id']);
		}
		# We expect genre _2 _3 _4 to be available
		$new_genres=array();
		foreach ($_POST as $k=>$v) {
			if (preg_match('/^genre_([0-9]{1})/',$k,$matches)) {
				$new_genres[$matches[1]]=$v;
			}
		}
		# Let's make sure the database matches the new genres list we just got
		$old_genres=get_game_genres($dbh,$game_id);
		if (DEBUG) {
			echo "OLD<hr>";
			print_r($old_genres);
			echo "<br>";
			echo "NEW<hr>";
			print_r($new_genres);
		}
		$sql_cmds=array();
		# So if anything in the NEW list that is NOT in the old, we need to add
		foreach ($new_genres as $nid) {
			if ($nid && !in_array($nid,$old_genres)) {
				#echo "Need to add genre $nid<br>";
				$sql_cmds[]="INSERT INTO game_genre (gameid,genreid) VALUES($game_id,$nid)";
			}
		}
		# If anything in the OLD list not in the new needs to be deleted (might have permissions issues
		# here?)
		foreach ($old_genres as $oid) {
			if ($oid && !in_array($oid,$new_genres)) {
				#echo "Need to remove genre $oid<br>";
				$sql_cmds[]="DELETE FROM game_genre WHERE gameid=$game_id AND genreid=$oid";
			}
		}
		if ($sql_cmds) {
			if (DEBUG) print_r($sql_cmds);
			foreach ($sql_cmds as $sql) {
				@$dbh->query($sql);
				if ($dbh->errno) {
					echo "<font color='red'>$s gave $dbh->error<br></font>\n";
				}
			}
		} else {
			if (DEBUG) echo "<i>No changes</i>\n";
		}
	} else {
		# Anything else is wrong
		echo "I do not understand";
	}
}

# First gobble all known genres
$s="SELECT id,name FROM genres order by name";
$q=@$dbh->query($s);
if (!$dbh->errno) {
	while ($r=$q->fetch_object()) {
		$genre_name=$r->name;
		$known_genres[$r->id]=$genre_name;
	}
	$q->free_result();
} else {
	echo "$s gave ".$dbh->error."<br>\n";
}

if ($game_id) {
	$s="	SELECT 		id,title,publisher,year,
						(SELECT GROUP_CONCAT(CONCAT(genres.id,'|',genres.name) SEPARATOR '@') FROM game_genre LEFT JOIN genres ON genreid=genres.id WHERE gameid=games.id order by game_genre.id) AS genres
			FROM 		games 
			WHERE		id=$game_id";

	$q=@$dbh->query($s);
	if (!$dbh->errno) {
		if ($r=$q->fetch_object()) {

			echo "<br><b>$r->title</b> $r->publisher $r->year<hr>";

			echo "<form name='frmGame' method='POST' action='admin_game_genre.php'>\n";
			echo "<input type='hidden' name='id' value='$game_id'>\n";

			$ac=1;
			$genres=explode('@',$r->genres);
			foreach ($genres as $genre) {
				list($id,$name)=explode('|',$genre);
				echo make_dd($id,$ac++);
			}
			# Allow up to 4 genres per title
			while ($ac<=4) {
				echo make_dd(0,$ac++);
			}

			echo "<br><br><br><input type='submit' value='Save'>\n";

			echo "</form>\n";
		}
		$q->free_result();
	} else {
		echo "$s gave ".$dbh->error."<br>\n";
	}
}

$dbh->close();

exit;

function get_game_genres($dbh,$game_id) {
	$ret=array();

	$s="	SELECT 		genres.id AS genres_id,name 
			FROM 		game_genre 
			LEFT JOIN 	genres ON genreid=genres.id 
			WHERE		gameid=$game_id";

	$q=@$dbh->query($s);
	if (!$dbh->errno) {
		while ($r=$q->fetch_object()) {
			$ret[]=$r->genres_id;
		}
		$q->free_result();
	} else {
		echo "$s gave ".$dbh->error."<br>\n";
	}

	return $ret;
}

function make_dd($aid,$index) {
	echo "<select name='genre_$index'>\n<option value='0'>-- Select genre --</option>";
	global $known_genres;
	foreach ($known_genres as $id=>$name) {
		$sel=($id==$aid)?' selected':'';
		echo "<option value='$id'$sel>$name</option>\n";
	}
	echo "</select>\n";
}
?>
