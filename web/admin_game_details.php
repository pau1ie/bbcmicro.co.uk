<!DOCTYPE html><?php
define('DEBUG',false);

require('includes/admin_session.php');

require_once('includes/config.php');
require_once('includes/admin_db_open.php');

require_once('includes/admin_menu.php');
?><html><head>
<script src=tinymce/tinymce.min.js></script>
<script>
tinymce.init({
   selector: '#notes',
    plugins: "advlist charmap code help hr link lists table",
    browser_spellcheck: true
});
</script>
</head>
<body><?php
show_admin_menu();


$jopts=[ 'R' => 'Required', 'O' => 'Optional' ];
$sopts=[ 'D' => 'Save to Disc', 'T' => 'Save to Tape' ];
$eopts=[ 'Y' => 'Yes' ];
$copts=[ 'Y' => 'Yes', 'N' => 'No' ];
$mcopts=[ 'Y' => 'Yes', 'N' => 'No', 'P' => 'Partial' ];
$drops=array( 'joystick','save');

# GET params means want to edit a game ...
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
	$game_id=intval($_GET['id']);
} else {
	# POST params mean an update
	if (isset($_POST) && $_POST) {
		if (isset($_POST['id']) && is_numeric($_POST['id'])) {
			$game_id=intval($_POST['id']);
		} else {
			$game_id=null;
		}
		if (!isset($_POST['electron'])) {
			$_POST['electron'] = '';
		}
		if (DEBUG) { echo "<br/>POST<pre>";print_r($_POST);echo "</pre>";}
		# We expect author,genre,publisher_01 _02 _03 _04... to be available
		$new_authors=array();
		$new_genres=array();
		$new_publishers=array();
		foreach ($_POST as $k=>$v) {
			if (preg_match('/^author_([0-9]{2})/',$k,$matches)) {
				$new_authors[$matches[1]]=$v;
			}
			if (preg_match('/^genre_([0-9]{2})/',$k,$matches)) {
				$new_genres[$matches[1]]=$v;
				#if (DEBUG ) {echo "<pre>";print_r($matches);echo "</pre>";}
			}
			if (preg_match('/^publisher_([0-9]{2})/',$k,$matches)) {
				$new_publishers[$matches[1]]=$v;
			}
		}
		# Let's make sure the database matches the new authors list we just got
		$old_authors=get_game_authors($dbh,$game_id);
		$old_genres=get_game_genres($dbh,$game_id);
		$old_publishers=get_game_pubs($dbh,$game_id);
		if (DEBUG) {
			echo "<br/>OLD Authors<hr><pre>";
			print_r($old_authors);
			echo "</pre><br>";
			echo "NEW Authors<hr><pre>";
			print_r($new_authors);
			echo "</pre><br/>OLD Genres<hr><pre>";
			print_r($old_genres);
			echo "</pre><br>";
			echo "NEW Genres<hr><pre>";
			print_r($new_genres);
			echo "</pre><br/>OLD Publishers<hr><pre>";
			print_r($old_publishers);
			echo "<br></pre>";
			echo "NEW Publishers<hr><pre>";
			print_r($new_publishers);
			echo "</pre>";
		}
		$sql_cmds=array();
		$sql_binds=array();
		$abort=False;

		if ($game_id == null) {
			# New entry
			$s="INSERT INTO games ( parent, title, year, genre, reltype, notes, players_min, players_max, joystick, save, hardware, electron, version, compilation, series, series_no, lastupdater, lastupdated, created, creator, compat_a, compat_b, compat_master) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW(),?,?,?,?)";
			if ($_POST['parent'] == '0' || $_POST['parent'] == '' ) {
				$p_parent = null;
			} else {
				$p_parent = $_POST['parent'];
			}
			if ($_POST['genre'] == '0') {
				$p_genre='';
			} else {
				$p_genre=$_POST['genre'];
			}
			if ($_POST['joystick'] == '0') {
				$p_joystick='';
			} else {
				$p_joystick=$_POST['joystick'];
			}
			if ($_POST['save'] == '0') {
				$p_save='';
			} else {
				$p_save=$_POST['save'];
			}
			if ($_POST['compat_a'] == '0') {
				$p_compat_a='N';
			} else {
				$p_compat_a=$_POST['compat_a'];
			}
			if ($_POST['compat_b'] == '0') {
				$p_compat_b='Y';
			} else {
				$p_compat_b=$_POST['compat_b'];
			}
			if ($_POST['compat_master'] == '0') {
				$p_compat_master=Null;
			} else {
				$p_compat_master=$_POST['compat_master'];
			}
			if ($_POST['compat_a'] == '0') {
				$p_compat_a='N';
			} else {
				$p_compat_a=$_POST['compat_a'];
			}
			if ($_POST['electron'] == 'Y') {
				$p_electron='Y';
			} else {
				$p_electron='';
			}
			$sbinds=array(  array('value' => $p_parent, 		'type' => PDO::PARAM_INT),
					array('value' => $_POST['title'], 	'type' => PDO::PARAM_STR),
					array('value' => $_POST['year'], 	'type' => PDO::PARAM_STR),
					array('value' => $p_genre, 		'type' => PDO::PARAM_INT),
					array('value' => $_POST['reltype'], 	'type' => PDO::PARAM_STR),
					array('value' => $_POST['notes'], 	'type' => PDO::PARAM_STR),
					array('value' => $_POST['players_min'], 'type' => PDO::PARAM_INT),
					array('value' => $_POST['players_max'], 'type' => PDO::PARAM_INT),
					array('value' => $p_joystick, 		'type' => PDO::PARAM_STR),
					array('value' => $p_save, 		'type' => PDO::PARAM_STR),
					array('value' => $_POST['hardware'], 	'type' => PDO::PARAM_STR),
					array('value' => $p_electron, 		'type' => PDO::PARAM_STR),
					array('value' => $_POST['version'], 	'type' => PDO::PARAM_STR),
					array('value' => $_POST['compilation'],	'type' => PDO::PARAM_STR),
					array('value' => $_POST['series'], 	'type' => PDO::PARAM_STR),
					array('value' => $_POST['series_no'],	'type' => PDO::PARAM_STR),
					array('value' => $_SESSION['userid'],	'type' => PDO::PARAM_INT),
					array('value' => $_SESSION['userid'],	'type' => PDO::PARAM_INT),
					array('value' => $p_compat_a,		'type' => PDO::PARAM_STR),
					array('value' => $p_compat_b,		'type' => PDO::PARAM_STR),
					array('value' => $p_compat_master,	'type' => PDO::PARAM_STR)
			);
			$sth=$dbh->prepare($s);
			if (DEBUG) {echo "<pre>$s<br/>"; print_r($sbinds);echo "</pre>";}
			if (executeWithDataTypes($sth,$sbinds)) {
				$game_id = $dbh->lastInsertId();
			} else {
				echo "Error: $s gave ".$dbh->errorCode()."<br><pre>\n";
				print_r($sbinds);
				echo "</pre>";
				exit();
			}
		} else {
			# An entry already exists. Compare it.
			$s="SELECT id, parent, title, year, genre, reltype, notes, players_min, players_max, joystick, save, hardware, electron, version, compilation, series, series_no, compat_a, compat_b, compat_master FROM games where id = ?";

			$sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			$sth->bindParam(1, $game_id, PDO::PARAM_INT);
			$diffs=array();
			if ($sth->execute()) {
				$r=$sth->fetch(PDO::FETCH_ASSOC);
				$sth->closeCursor();
				if (DEBUG) {echo "<pre>$s<br/>";print_r($r);echo "</pre>";}
				foreach ($r as $k => $v ) {
					# Will break silently if the structure changes.
					if (array_key_exists($k,$_POST)) {
						$pv = $_POST[$k];
						# Drop down menu post 0 where they mean null...
						if (!(array_search($k,$drops)===False)) {
							if ($pv === "0") {
								$pv='';
							}
						}
						if ( $v != $pv ) {
							$diffs[$k]=$pv;
							if ( $pv == '' ) {
								$pv = null;
							}
							if ($k = 'id') {
								$abort=True;
								if (DEBUG) { echo "<br/>ID Changed <br/>";}
							}
						}

					} else {
						$abort=True;
						if (DEBUG) { echo "<br/>$k Missing <br/>";}
					}
				}
			} else {
				echo "$s gave ".$dbh->errorCode()."<br>\n";
			}
			if (count($diffs)>0) {
				$diffs['lastupdater']=$_SESSION['userid'];
				$diffs['lastupdated']=$_SESSION['userid'];
				$sql_cmds[]="update games set ".join('=?, ',array_keys($diffs)).'=NOW() where id = ?';
				$bs=array();
				foreach ($diffs as $k=>$b) {
					$t=PDO::PARAM_STR;
					if ($k == 'parent' or $k == 'genre') {
						$t=PDO::PARAM_INT;
						if ($b == '' or $b == '0') {
							$t=PDO::PARAM_NULL;
							$b=null;
						}
					}
					$bs[]=array('value'=>$b,'type'=>$t);
				}
				array_pop($bs);
				$bs[]=array('value'=>$game_id,'type'=>PDO::PARAM_INT);
				$sql_binds[]=$bs;
			}
			if (DEBUG) { echo "<br/>Diffs<pre>";print_r($diffs);echo "Abort: $abort";echo "</pre><br/>";}
		}

		# If anything in the OLD list not in the new needs to be deleted (might have permissions issues
		# here?)
		foreach ($old_authors as $oid) {
			if ($oid && !in_array($oid,$new_authors)) {
				#echo "Need to remove author $oid<br>";
				$sql_cmds[]="DELETE FROM games_authors WHERE games_id=? AND authors_id=?";
				$sql_binds[]=array(	array('value'=>$game_id,	'type'=>PDO::PARAM_INT),
							array('value'=>$oid,		'type'=>PDO::PARAM_INT));
			}
		}
		foreach ($old_genres as $oid) {
			if ($oid && !in_array($oid,$new_genres)) {
				#echo "Need to remove genre $oid<br>";
				$sql_cmds[]="DELETE FROM game_genre WHERE gameid=? AND genreid=?";
				$sql_binds[]=array(	array('value'=>$game_id,	'type'=>PDO::PARAM_INT),
							array('value'=>$oid,		'type'=>PDO::PARAM_INT));
			}
		}
		foreach ($old_publishers as $oid) {
			if ($oid && !in_array($oid,$new_publishers)) {
				#echo "Need to remove publisher $oid<br>";
				$sql_cmds[]="DELETE FROM games_publishers WHERE gameid=? AND pubid=?";
				$sql_binds[]=array(	array('value'=>$game_id,	'type'=>PDO::PARAM_INT),
							array('value'=>$oid,		'type'=>PDO::PARAM_INT));
			}
		}

		# So if anything in the NEW list that is NOT in the old, we need to add
		foreach ($new_authors as $nid) {
			if ($nid && !in_array($nid,$old_authors)) {
				#echo "Need to add author $nid<br>";
				$sql_cmds[]="INSERT INTO games_authors (games_id,authors_id) VALUES(?,?)";
				$sql_binds[]=array(	array('value'=>$game_id,	'type'=>PDO::PARAM_INT),
							array('value'=>$nid,		'type'=>PDO::PARAM_INT));
			}
		}
		foreach ($new_genres as $nid) {
			if ($nid && !in_array($nid,$old_genres)) {
				#echo "Need to add genre $nid<br>";
				$sql_cmds[]="INSERT INTO game_genre (gameid,genreid) VALUES(?,?)";
				$sql_binds[]=array(	array('value'=>$game_id,	'type'=>PDO::PARAM_INT),
							array('value'=>$nid,		'type'=>PDO::PARAM_INT));
			}
		}
		foreach ($new_publishers as $nid) {
			if ($nid && !in_array($nid,$old_publishers)) {
				#echo "Need to add publisher $nid<br>";
				$sql_cmds[]="INSERT INTO games_publishers (gameid,pubid) VALUES(?,?)";
				$sql_binds[]=array(	array('value'=>$game_id,	'type'=>PDO::PARAM_INT),
							array('value'=>$nid,		'type'=>PDO::PARAM_INT));
			}
		}

		if ($sql_cmds) {	##################
			foreach ($sql_cmds as $i => $sql) {
				if (DEBUG) { echo "<br/>$i<pre>"; print_r($sql); echo "<br/>";print_r($sql_binds[$i]); echo "</pre>"; }
				$sth = $dbh->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				if (!executeWithDataTypes($sth,$sql_binds[$i])) {
					echo "$sql gave ".$dbh->errorCode()."<br>\n";
				}
			}
		} else {
			if (DEBUG) echo "<i>No changes</i>\n";
		}
	} else {
		# Present the new game screen
		$game_id = null;
	}
}

# First gobble all known authors
$s="SELECT id,name,alias FROM authors order by name";

$sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
#$sth->bindParam(1, $game_id, PDO::PARAM_INT);
if ($sth->execute()) {
	while ($r=$sth->fetch()) {
		$author_name=$r['name'];
		if ($r['alias']) $author_name.=" (".$r['alias'].")";
		$known_authors[$r['id']]=$author_name;
	}
	$sth->closeCursor();
} else {
	echo "$s gave ".$dbh->errorCode()."<br>\n";
}

$s="SELECT id,name FROM genres order by name";
$sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
#$sth->bindParam(1, $game_id, PDO::PARAM_INT);
if ($sth->execute()) {
	while ($r=$sth->fetch()) {
		$genre_name=$r['name'];
		$known_genres[$r['id']]=$genre_name;
	}
	$sth->closeCursor();
} else {
	echo "$s gave ".$dbh->errorCode()."<br>\n";
}

$s="SELECT id,name FROM publishers order by name";
$sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
#$sth->bindParam(1, $game_id, PDO::PARAM_INT);
if ($sth->execute()) {
	while ($r=$sth->fetch()) {
		$publisher_name=$r['name'];
		$known_publishers[$r['id']]=$publisher_name;
	}
	$sth->closeCursor();
} else {
	echo "$s gave ".$dbh->errorCode()."<br>\n";
}

$s="SELECT id,name FROM reltype order by name";
$sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
#$sth->bindParam(1, $game_id, PDO::PARAM_INT);
if ($sth->execute()) {
	while ($r=$sth->fetch()) {
		$reltyp_name=$r['id'].' - '.$r['name'];
		$known_reltyps[$r['id']]=$reltyp_name;
	}
	$sth->closeCursor();
} else {
	echo "$s gave ".$dbh->errorCode()."<br>\n";
}

if ($game_id) {
	$s="	SELECT 	id,title, parent, title, year, genre, reltype, notes, players_min, players_max, joystick, save,
			hardware, electron, version, compilation, series, series_no, compat_a, compat_b, compat_master,
			(SELECT GROUP_CONCAT(CONCAT(publishers.id,'|',publishers.name) SEPARATOR '@') 
				FROM games_publishers LEFT JOIN publishers ON pubid=publishers.id WHERE gameid=games.id) AS publishers,
			(SELECT GROUP_CONCAT(CONCAT(authors.id,'|',authors.name) SEPARATOR '@') 
				FROM games_authors LEFT JOIN authors ON authors_id=authors.id WHERE games_id=games.id) AS authors,
			(SELECT GROUP_CONCAT(CONCAT(genres.id,'|',genres.name) SEPARATOR '@') 
				FROM game_genre LEFT JOIN genres ON genreid=genres.id WHERE gameid=games.id order by game_genre.id) AS genres
			FROM 		games 
			WHERE		id=?";

	$sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$sth->bindParam(1, $game_id, PDO::PARAM_INT);
	if ($sth->execute()) {
		if ($r=$sth->fetch(PDO::FETCH_ASSOC)) {
			make_form($game_id,$r);
		}
		$sth->closeCursor();
	} else {
		echo "$s gave ".$dbh->errorCode()."<br>\n";
	}
} else {
	# Make an empty form
	$r=['id'=>'','title'=>'','parent'=>'','year'=>'19XX','genre'=>'',
            'reltype'=>'W','notes'=>'','players_min'=>'1', 'players_max'=>'1',
            'joystick'=>'', 'save'=>'','hardware'=>'', 'electron'=>'',
            'version'=>'', 'compilation'=>'', 'series'=>'', 'series_no'=>'',
            'publishers'=>'','authors'=>'','genres'=>''];
	make_form(0,$r);
}

exit;

function get_game_authors($dbh,$game_id) {
	$ret=array();

	$s="	SELECT 		authors.id AS authors_id,name 
		FROM 		games_authors 
		LEFT JOIN 	authors ON authors_id=authors.id 
		WHERE		games_id=?";

	$sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$sth->bindParam(1, $game_id, PDO::PARAM_INT);
	if ($sth->execute()) {
		while ($r=$sth->fetch()) {
			$ret[]=$r['authors_id'];
		}
		$sth->closeCursor();
	} else {
		echo "Error:";
		echo "\n";
		$sth->debugDumpParams ();
		echo "$s gave ".$dbh->errorCode()."<br>\n";
	}

	return $ret;
}


function get_game_genres($dbh,$game_id) {
	$ret=array();

	$s="	SELECT 		genres.id AS genres_id,name 
		FROM 		game_genre 
		LEFT JOIN 	genres ON genreid=genres.id 
		WHERE		gameid=?";


	$sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$sth->bindParam(1, $game_id, PDO::PARAM_INT);
	if ($sth->execute()) {
		while ($r=$sth->fetch()) {
			$ret[]=$r['genres_id'];
		}
		$sth->closeCursor();
	} else {
		echo "Error:";
		echo "\n";
		$sth->debugDumpParams ();
		echo "$s gave ".$dbh->errorCode()."<br>\n";
	}

	return $ret;
}

function get_game_pubs($dbh,$game_id) {
	$ret=array();

	$s="	SELECT 		publishers.id AS publishers_id,name 
		FROM 		games_publishers 
		LEFT JOIN 	publishers ON pubid=publishers.id 
		WHERE		gameid=?";

	$sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$sth->bindParam(1, $game_id, PDO::PARAM_INT);
	if ($sth->execute()) {
		while ($r=$sth->fetch()) {
			$ret[]=$r['publishers_id'];
		}
		$sth->closeCursor();
	} else {
		echo "Error:";
		echo "\n";
		$sth->debugDumpParams ();
		echo "$s gave ".$dbh->errorCode()."<br>\n";
	}

	return $ret;
}

function make_dd($aid,$nam,$typ,$known) {
	echo "<select name='$nam'>\n<option value='0'>-- Select $typ --</option>";
	foreach ($known as $id=>$name) {
		$sel=($id==$aid)?' selected':'';
		echo "<option value='$id'$sel>$name</option>\n";
	}
	echo "</select>\n";
}

function make_form($game_id,$r) {
	global $known_genres, $known_reltyps, $known_publishers, $known_authors, $jopts, $sopts, $eopts, $copts, $mcopts;
	if (DEBUG) { echo "<pre>"; print_r($r); echo "</pre>";}
	$pubs=explode('@',$r['publishers']);
	$names='';
	foreach ($pubs as $pub) {
		if ($pub) {
			list($id,$name)=explode('|',$pub);
			if ($name) $names.="$name, ";
		}
	}
	echo "<br><b>".$r['title']."</b> $names ".$r['year']."<hr>";

	echo "<form name='frmGame' method='POST' action='admin_game_details.php'>\n";
	echo "<input type='hidden' name='id' value='$game_id'>\n";

	echo "<label> Title <input type='text' name='title' size='80' value='".htmlspecialchars($r['title'],ENT_QUOTES)."'/></label><br/><br/>";
	echo "<label> Parent ID <input type='text' name='parent' size='4' value='".$r['parent']."'/> ";
	echo "Note: If populated, this game won't appear in the list, the parent needs to be ";
	echo "returned in all relevant searches for this game.<br/><br/></label>";
	echo "<label> Year. 19XX if unknown. <input type='text' name='year' size='4' value='".$r['year']."'/></label>";

	echo "<label> Primary Genre ";
	echo make_dd($r['genre'], 'genre','Primary Genre',$known_genres);
	echo "</label>";

	echo "<label> Release Type ";
	echo make_dd($r['reltype'], 'reltype','Release',$known_reltyps);
	echo "</label>";

	echo "<br/><br/>";

	echo "<label> Number of players: Min: <input type='number' name='players_min' size='2' min='0' ";
	echo "max='99' value='".$r['players_min']."'/>";
	echo " Max: <input type='number' name='players_max' size='2' min='0' max='99' value='".$r['players_max']."'/></label>";
	echo "<br/><br/>";

	# Joystick
	echo "<label>If a joystick is used, select whether it is optional or required.  ";
	echo make_dd($r['joystick'], 'joystick','Joystick',$jopts);
	echo "</label>";

	# Save
	echo "<label>If game state can be saved, select disc or tape as the target.  ";
	echo make_dd($r['save'], 'save','Save',$sopts);
	echo "</label>";

	echo "<br/><br/>";

	echo "<label>Any special hardware required ";
	echo "<input type='text' name='hardware' size='20' value='".htmlspecialchars($r['hardware'],ENT_QUOTES)."'/></label> ";
	echo "</label>";

	echo "<label> Electron conversion ";
//	echo make_dd($r['electron'], 'electron','if converted',$eopts);
        if ( $r['electron'] == 'Y' ) {
		$checked='checked';
	} else {
		$checked='';
	}
	echo '<input type="checkbox" name="electron" '.$checked.' value="Y"/>';
	echo "</label>";

	echo "<label> Version <input type='text' name='version' size='5' value='".$r['version']."'/></label> ";
	echo "<br/><br/>";

	# Model A Compatibility
	echo "<label>Model A Compatibility.  ";
	echo make_dd($r['compat_a'], 'compat_a','Model A',$copts);
	echo "</label>";

	# Model B Compatibility
	echo "<label>Model B Compatibility.  ";
	echo make_dd($r['compat_b'], 'compat_b','Model B',$copts);
	echo "</label>";

	# Master Compatibility
	echo "<label>Master Compatibility.  ";
	echo make_dd($r['compat_master'], 'compat_master','Master',$mcopts);
	echo "</label><br/><br/>";

	echo "<label> Compilation: <input type='text' name='compilation' size='20' value='".htmlspecialchars($r['compilation'],ENT_QUOTES)."'/></label> ";
	echo "<label> Series - must be identical for each game in series ";
	echo "<input type='text' name='series' size='20' value='".htmlspecialchars($r['series'],ENT_QUOTES)."'/></label> ";
	echo "<label> Number in series <input type='text' name='series_no' size='15' value='".$r['series_no']."'/></label> ";
	echo "<br/><br/>";

	echo "<label>Authors<br/>";
	# Authors
	$ac=1;
	$authors=explode('@',$r['authors']);
	#if (DEBUG) { echo "<pre>"; print_r($authors); echo "</pre>";}
	foreach ($authors as $author) {
		if (!(False === strpos($author,'|' ))) {
			list($id,$name)=explode('|',$author);
			echo make_dd($id,'author_'.sprintf("%02d",$ac++),'author',$known_authors);
		}
	}
	# Allow up to 4 authors per title
	do {
		echo make_dd(0,'author_'.sprintf("%02d",$ac++),'author',$known_authors);
	} while ($ac<=4);

	echo "</label><br/><br/><label>Secondary Genres<br/>";

	$ac=1;
	$genres=explode('@',$r['genres']);
	#if (DEBUG) {echo "<pre>"; print_r($genres); echo "</pre>";}
	foreach ($genres as $genre) {
		if (!(False === strpos($genre,'|' ))) {
			list($id,$name)=explode('|',$genre);
			echo make_dd($id,'genre_'.sprintf("%02d",$ac++),'genre',$known_genres);
		}
	}
	# Allow up to 4 genres per title
	do {
		echo make_dd(0,'genre_'.sprintf("%02d",$ac++),'genre',$known_genres);
	} while ($ac<=4);

	echo "</label><br/><br/>Publishers<br/>";

	$ac=1;

	foreach ($pubs as $pub) {
		if (!(False === strpos($pub,'|' ))) {
			list($id,$name)=explode('|',$pub);
			echo make_dd($id,'publisher_'.sprintf("%02d",$ac++),'publisher',$known_publishers);
		}
	}
	# Allow up to 4 publishers per title
	do {
		echo make_dd(0,'publisher_'.sprintf("%02d",$ac++),'publisher',$known_publishers);
	} while ($ac<=4);

	echo "<br/><br/><label> Notes: <textarea id='notes' name='notes' rows='5' cols='132' >".htmlspecialchars($r['notes'])."</textarea></label><br/>";
	echo "Take care with this field. It allows any HTML to be entered so it is possible to completely mess up the formatting of the page!";

	echo "<br><br><input type='submit' value='Save'>\n";

	echo "</form>\n";
?></body></html><?php
}

function executeWithDataTypes(PDOStatement $sth, array $values) {
    $count = 1;
    foreach($values as $value) {
        $sth->bindValue($count, $value['value'], $value['type']);
        $count++;
    }
    return $sth->execute();
}
