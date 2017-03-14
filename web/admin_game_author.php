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
		# We expect author_1 _2 _3 _4 to be available
		$new_authors=array();
		foreach ($_POST as $k=>$v) {
			if (preg_match('/^author_([0-9]{1})/',$k,$matches)) {
				$new_authors[$matches[1]]=$v;
			}
		}
		# Let's make sure the database matches the new authors list we just got
		$old_authors=get_game_authors($dbh,$game_id);
		if (DEBUG) {
			echo "OLD<hr>";
			print_r($old_authors);
			echo "<br>";
			echo "NEW<hr>";
			print_r($new_authors);
		}
		$sql_cmds=array();
		# So if anything in the NEW list that is NOT in the old, we need to add
		foreach ($new_authors as $nid) {
			if ($nid && !in_array($nid,$old_authors)) {
				#echo "Need to add author $nid<br>";
				$sql_cmds[]="INSERT INTO games_authors (games_id,authors_id) VALUES($game_id,$nid)";
			}
		}
		# If anything in the OLD list not in the new needs to be deleted (might have permissions issues
		# here?)
		foreach ($old_authors as $oid) {
			if ($oid && !in_array($oid,$new_authors)) {
				#echo "Need to remove author $oid<br>";
				$sql_cmds[]="DELETE FROM games_authors WHERE games_id=$game_id AND authors_id=$oid";
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

# First gobble all known authors
$s="SELECT id,name,alias FROM authors";
$q=@$dbh->query($s);
if (!$dbh->errno) {
	while ($r=$q->fetch_object()) {
		$author_name=$r->name;
		if ($r->alias) $author_name.=" ($r->alias)";
		$known_authors[$r->id]=$author_name;
	}
	$q->free_result();
} else {
	echo "$s gave ".$dbh->error."<br>\n";
}

if ($game_id) {
	$s="	SELECT 		id,title,(SELECT GROUP_CONCAT(CONCAT(publishers.id,'|',publishers.name) SEPARATOR '@') FROM games_publishers LEFT JOIN publishers ON pubid=publishers.id WHERE gameid=games.id) AS publishers ,year,
						(SELECT GROUP_CONCAT(CONCAT(authors.id,'|',authors.name) SEPARATOR '@') FROM games_authors LEFT JOIN authors ON authors_id=authors.id WHERE games_id=games.id) AS authors
			FROM 		games 
			WHERE		id=$game_id";

	$q=@$dbh->query($s);
	if (!$dbh->errno) {
		if ($r=$q->fetch_object()) {
			$pubs=explode('@',$r->publishers);
			$names='';
			foreach ($pubs as $pub) {
				if ($pub) {
					list($id,$name)=explode('|',$pub);
					if ($name) $names.="$name, ";
				}
			}
			echo "<br><b>$r->title</b> $names $r->year<hr>";

			echo "<form name='frmGame' method='POST' action='admin_game_author.php'>\n";
			echo "<input type='hidden' name='id' value='$game_id'>\n";

			$ac=1;
			$authors=explode('@',$r->authors);
			foreach ($authors as $author) {
				list($id,$name)=explode('|',$author);
				echo make_dd($id,$ac++);
			}
			# Allow up to 4 authors per title
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

function get_game_authors($dbh,$game_id) {
	$ret=array();

	$s="	SELECT 		authors.id AS authors_id,name 
			FROM 		games_authors 
			LEFT JOIN 	authors ON authors_id=authors.id 
			WHERE		games_id=$game_id";

	$q=@$dbh->query($s);
	if (!$dbh->errno) {
		while ($r=$q->fetch_object()) {
			$ret[]=$r->authors_id;
		}
		$q->free_result();
	} else {
		echo "$s gave ".$dbh->error."<br>\n";
	}

	return $ret;
}

function make_dd($aid,$index) {
	echo "<select name='author_$index'>\n<option value='0'>-- Select author --</option>";
	global $known_authors;
	foreach ($known_authors as $id=>$name) {
		$sel=($id==$aid)?' selected':'';
		echo "<option value='$id'$sel>$name</option>\n";
	}
	echo "</select>\n";
}
?>
