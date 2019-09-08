<?php
require 'includes/config.php';
require 'includes/db_connect.php';
require 'includes/menu.php';
require 'includes/playlink.php';

$id=0;

if ( isset($_GET["id"])) {
  $id=intval($_GET["id"]);
}

$sql = "select g.id, g.title_article, g.title, g.parent, g.year, g.notes, g.joystick, g.players_min, g.players_max, g.save, g.hardware, g.version, 
g.electron, g.series, g.series_no, n.name as genre, r.id as relid, r.name as reltype, g.compat_a, g.compat_b, g.compat_master from games g left join genres n on n.id = g.genre left join reltype r on r.id = g.reltype where g.id  = ?";
$sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->bindParam(1, $id, PDO::PARAM_INT);
if ($sth->execute()) {
  $game = $sth->fetch();
} else {
  echo "Error:";
  echo "\n";
  $sth->debugDumpParams ();
  $game=array();
}

$sql = "select * from screenshots where gameid  = ?";
$sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->bindParam(1, $id, PDO::PARAM_INT);
if ($sth->execute()) {
  $shot = $sth->fetchAll();
} else {
  echo "Error:";
  echo "\n";
  $sth->debugDumpParams ();
  $shot=array();
}
if ( empty($shot) ) {
  $shot[] = array( "filename" => NULL, "subdir" => NULL );
}

$scrshot=get_scrshot($shot[0]['filename'],$shot[0]['subdir']);

$sql = "select * from images where gameid  = ?";
$sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->bindParam(1, $id, PDO::PARAM_INT);
if ($sth->execute()) {
  $img = $sth->fetch();
} else {
  echo "Error:";
  echo "\n";
  $sth->debugDumpParams ();
  $img=array();
}


$ssd = get_discloc($img["filename"],$img['subdir']);
$ssd_info = pathinfo($ssd);
$jsbeeb=JB_LOC;
$root=WS_ROOT;

$playlink=get_playlink($img,$jsbeeb,$root);

$imglink="";
if ($img['probs'] != 'N' and $playlink != NULL ) {
  if ($img['probs']=='P') {
    $imglink='<p style="text-align: center">This game doesn\'t work properly in jsbeeb.</p>' . $imglink .'<p><a type="button" class="btn btn-warning btn-lg center-block" onmousedown="log('.$id.',\'d\');" href="' . $playlink . '" >Play Anyway</a></p>';
  }else{
    $imglink=$imglink .'<p><a type="button" class="btn btn-primary btn-lg center-block" onmousedown="log('.$id.',\'d\');" href="' . $playlink . '" >Play</a></p>';
  }
} else {
  $imglink=$imglink."<p>Can't be played online.</p>";
}

if ( $ssd != NULL && file_exists($ssd)) {
  $imglink=$imglink.'<p><a type="button" download="'.$ssd_info['basename'].'" class="btn btn-primary btn-lg center-block" onmousedown="log('.$id.',\'d\');" href="' . $ssd . '">Download</a></p>';
  if ( $ssd_info['extension'] = 'ssd' ) {
    $imglink=$imglink.'<p><a href="explore.php?id='.$id.'" type="button" class="btn btn-primary btn-lg center-block">Explore Disc</a></p>';
  }
} else {
  $imglink=$imglink."<p>No disc image available</p>";
}

$sql = "select * from game_genre gg, genres g where gg.gameid  = ? and gg.genreid = g.id";
$sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->bindParam(1, $id, PDO::PARAM_INT);
if ($sth->execute()) {
  $genres = $sth->fetchAll();
} else {
  echo "Error:";
  echo "\n";
  $sth->debugDumpParams ();
  $genres=array();
}

$sql = "select * from games_publishers gp, publishers p where gp.gameid  = ? and gp.pubid = p.id";
$sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->bindParam(1, $id, PDO::PARAM_INT);
if ($sth->execute()) {
  $publishers = $sth->fetchAll();
} else {
  echo "Error:";
  echo "\n";
  $sth->debugDumpParams ();
  $genres=array();
}

$sql = "select a.name from games_authors ga, authors a where ga.games_id  = ? and ga.authors_id = a.id";
$sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->bindParam(1, $id, PDO::PARAM_INT);
if ($sth->execute()) {
  $authors = $sth->fetchAll();
} else {
  echo "Error:";
  echo "\n";
  $sth->debugDumpParams ();
  $authors=array();
}

$sql = "select c.name from games_compilations gc, compilations c where gc.games_id  = ? and gc.compilations_id = c.id";
$sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->bindParam(1, $id, PDO::PARAM_INT);
if ($sth->execute()) {
  $compilations = $sth->fetchAll();
} else {
  echo "Error:";
  echo "\n";
  $sth->debugDumpParams ();
  $compilations=array();
}

//print_r($compilations);

if (strlen($game["title_article"]) > 0) {
   $ta=$game["title_article"].' ';
} else {
   $ta='';
}

$split=explode('(',$game["title"]);
$title='<h1>' . $ta . $split[0];
if (count($split) > 1 ) {
   $title = $title . '</h1><p>(' . implode('(',array_slice($split,1)) . "</p>";
}  else {
   $title = $title . '</h1>';
}

$back_url='index.php';
$back_desc='home page';
if (array_key_exists('HTTP_REFERER', $_SERVER)) {
  if ( parse_url($_SERVER["HTTP_REFERER"],PHP_URL_HOST) == $_SERVER["SERVER_NAME"] ) {
    $back_url = "javascript:history.go(-1)";
    $back_desc='list';
  }
}

$s = '';
if ( count($genres) > 1) {
  $s = 's';
}

if ( ! empty($genres)) {
  $genretab='<tr><th>Secondary genre' . $s . '</th><td>';
  foreach ($genres as $genre) {
    $genretab=$genretab . '<a href="index.php?search='.urlencode($genre["name"]).'&on_S=on">' . $genre["name"] . "</a><br/>";
  }
  $genretab=$genretab . "</td></tr>";
} else {
  $genretab="";
}

$s = '';
if ( count($publishers) > 1) {
  $s = 's';
}
$names='';
if ( ! empty($publishers)) {
  $pubtab='<tr><th>Publisher' . $s . '</th><td>';
  foreach ($publishers as $publisher) {
    $pubtab=$pubtab . '<a href="index.php?search='. urlencode($publisher["name"]) .'&on_P=on">'.$publisher["name"] . "</a><br/>";
    $names.=$publisher["name"].", ";
  }
  if ($names) {
    $names=substr($names,0,strlen($names)-2);
  } 
  $pubtab=$pubtab . "</td></tr>";
} else {
  $pubtab="";
}

$s = '';
if ( count($authors) > 1) {
  $s = 's';
}

if ( ! empty($authors)) {
  $authortab='<tr><th>Author' . $s . '</th><td>';
  foreach ($authors as $author) {
    $authortab=$authortab . '<a href="index.php?search=' . urlencode($author["name"]) . '&on_A=on">' . $author["name"] . "</a><br/>";
  }
  $authortab=$authortab . "</td></tr>";
} else {
  $authortab="";
}

$s = '';
if ( count($compilations) > 1) {
  $s = 's';
}

if ( ! empty($compilations)) {
  $compilationtab='<tr><th>Compilation' . $s . '</th><td>';
  foreach ($compilations as $compilation) {
    $compilationtab=$compilationtab . '<a href="index.php?search=' . urlencode($compilation["name"]) . '&on_C=on">' . $compilation["name"] . "</a><br/>";
  }
  $compilationtab=$compilationtab . "</td></tr>";
} else {
  $compilationtab="";
}

?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">

    <title><?php echo $ta . $game["title"]; ?></title>

    <!-- Bootstrap core CSS -->
    <link href="bs/css/bootstrap.min.css" rel="stylesheet">

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="bs/css/ie10-viewport-bug-workaround.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="bs/css/jumbotron.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- fb meta tags -->
    <meta property="og:url"                content="<?php echo WS_ROOT . "/game.php?id=" . $game["id"] . "&amp;h=h" ?>" />
    <meta property="og:type"               content="website" />
    <meta property="og:title"              content="<?php echo $game["title"]; ?>" />
    <meta property="og:description"        content="<?php echo "Published by " . $names . " in " . $game["year"];?>" />
    <meta property="og:image"              content="<?php echo WS_ROOT . '/' . $scrshot; ?>" />
  </head>

  <body>

 <nav class="navbar navbar-fixed-top navbar-inverse">
  <div class="container">
   <div class="navbar-header">
    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
     <span class="sr-only">Toggle navigation</span>
     <span class="icon-bar"></span>
     <span class="icon-bar"></span>
     <span class="icon-bar"></span>
    </button>
    <a href="index.php" class="navbar-brand"><?php echo $site_name?></a>
   </div>
   <?php make_menu_bar("Games")?>
  </div><!-- /.container -->
 </nav><!-- /.navbar -->


    <div class="container">
      <div class="jumbotron">
        <?php echo $title; ?>
      </div>
      <!-- Example row of columns -->
      <div class="row">
        <div class="col-md-8">
          <h2>Screenshot</h2>
          <p><img src="<?php echo $scrshot;?>" class="img-responsive"></p><p>&nbsp;</p>
<?php
   if ($game["notes"]!=Null ) {
      echo "<h2>Notes</h2><p>".$game["notes"]."</p>";
   }

   switch ($game['joystick']) {
    case "O":
        $js = "Optional";
        break;
    case "R":
        $js = "Required";
        break;
    default:
        $js = "Not Supported";
   }

   switch ($game['save']) {
    case "D":
        $sa = "Disc";
        break;
    case "T":
        $sa = "Cassette Tape";
        break;
    default:
        $sa = "Not Supported";
   }

   $hw='';
   if (!empty($game['hardware'])) {
      $hw = "<tr><th>Hardware required</th><td>" . $game['hardware'] . "</td></tr>";
   }

   $ver='';
   if (!empty($game['version'])) {
      $ver = "<tr><th>Version</th><td>" . $game['version'] . "</td></tr>";
   }

   $el='';
   if (!empty($game['electron'])) {
      if ( $game['electron'] == 'Y' ) {
          $el = 'Yes';
      } else {
          $el=$game['electron'];
      }
      $el = "<tr><th>Electron Release</th><td>" . $el . "</td></tr>";
   }

   $sr='';
   if (!empty($game['series'])) {
      $sr = '<tr><th>Series</th><td><a href="index.php?search=' . urlencode($game['series']) . '&on_Z=on">' . $game['series'] . "</a></td></tr>";
   }

   $sn='';
   if (!empty($game['series_no'])) {
      $sn = "<tr><th>Series number</th><td>" . $game['series_no'] . "</td></tr>";
   }

   $ed='';
   if (!empty($game['edit'])) {
      $ed = "<tr><th>Edit</th><td>" . $game['edit'] . "</td></tr>";
   }

   if ($game["players_min"] == $game["players_max"]) {
      if ($game["players_min"] == 1) {
         $players="Single Player";
      } else {
         $players=$game["players_min"] . " players";
      }
   } else {
      $players=$game["players_min"] . " to " . $game["players_max"];
   }

   $compat='';
   if (!empty($game['compat_a'])) {
     if ($game['compat_a'] == 'Y') {
       $compat = 'A:&#10004; ';
     } else {
       $compat = "A:&#10008; ";
     }
   }

   if (!empty($game['compat_b'])) {
     if ($game['compat_b'] == 'Y') {
       $compat = $compat . "B:&#10004; ";
     } else {
       $compat = $compat . "B:&#10008; ";
     }
   }

   if (!empty($game['compat_master'])) {
     if ($game['compat_master'] == 'Y') {
       $compat = $compat . "Master:&#10004; ";
     } elseif ($game['compat_master'] == 'N') {
       $compat = $compat . "Master:&#10008; ";
     } else {
       $compat = $compat . "Master: Partial ";
     }
   } else {
     $compat = $compat . "Master: Untested ";
   }
?>
        </div>
        <div class="col-md-4">
          <h2>Details</h2>
          <table class="table">
            <tr><th>Title</th><td><?php echo $ta . $game["title"];?></td></tr>
            <tr><th>Year</th><td><a href="index.php?search=<?php echo $game["year"];?>&on_Y=on"><?php echo $game["year"];?></a></td></tr>
            <?php echo $pubtab;?>
            <?php echo $authortab;?>
            <tr><th>Release Type</th><td><a href="index.php?rt_<?php echo $game["relid"];?>="><?php echo $game["reltype"];?></a></td></tr>
            <tr><th>Primary genre</th><td><a href="index.php?search=<?php echo urlencode($game["genre"]);?>&on_G=on"><?php echo $game["genre"];?></a></td></tr>
            <?php echo $genretab;?>
            <tr><th>Joystick</th><td><?php echo $js;?></td></tr>
            <tr><th>Players</th><td><?php echo $players;?></td></tr>
            <tr><th>Save</th><td><?php echo $sa;?></td></tr>
            <tr><th>Compatibility</th><td><?php echo $compat;?></td></tr>
            <?php echo $hw;?>
            <?php echo $el;?>
            <?php echo $ver;?>
            <?php echo $compilationtab;?>
            <?php echo $sr;?>
            <?php echo $sn;?>
            <?php echo $ed;?>

          </table>
          <?php echo $imglink; ?>
          <p><a type="button" class="btn btn-primary btn-lg center-block" href="<?php echo $back_url ?>" title="Back">Back to <?php echo $back_desc ?></a></p>
        </div>
      </div>
      <hr>

<?php
$sql="select id, title,(SELECT GROUP_CONCAT(CONCAT(publishers.id,'|',publishers.name) SEPARATOR '@') FROM games_publishers LEFT JOIN publishers ON pubid=publishers.id WHERE gameid=games.id) AS publishers, year from games where parent = ? ";
$sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->bindParam(1, $id, PDO::PARAM_INT);
if ($sth->execute()) {
  $children = $sth->fetchAll();
} else {
  echo "Error:";
  echo "\n";
  $sth->debugDumpParams ();
  $children=array();
}
if ( ! empty($children)) {
?>
      <div class="panel panel-default">
        <!-- Default panel contents -->
        <div class="panel-heading"><p class="lead">Alternative Versions</p></div>
        <div class="panel-body">
          <p>This page is the entry for the canonical version of the game. For specific variants, see the list below.</p>
        </div>
        <!-- Table -->
        <table class="table">
          <thead> <tr> <th>Title</th> <th>Publisher</th> <th>Year</th> </tr> </thead> <tbody> 
<?php
foreach ($children as $child) {
			$pubs=explode('@',$child["publishers"]);
			$names='';
			foreach ($pubs as $pub) {
				if ($pub) {
					list($pid,$pname)=explode('|',$pub);
					if ($pname) $names.="$pname, ";
				}
			}
			if ($names) {
				$names=substr($names,0,strlen($names)-2);
			} else {
				$names="<i>No Publisher</i>";
			}
?>
              <tr> <td><a href="game.php?id=<?php echo $child['id']; ?>"><?php echo $child['title'];?></a></td> <td><?php echo $names;?></td> <td><?php echo $child['year'];?></td> </tr>
<?php
}
?>
          </tbody> 
        </table>
      </div>
      <hr>
<?php
}
if ( ! is_null($game['parent'])) {
$sql="select id, title,(SELECT GROUP_CONCAT(CONCAT(publishers.id,'|',publishers.name) SEPARATOR '@') FROM games_publishers LEFT JOIN publishers ON pubid=publishers.id WHERE gameid=games.id) AS publishers, year from games where id = ? ";
$sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->bindParam(1, $game['parent'], PDO::PARAM_INT);
if ($sth->execute()) {
  $children = $sth->fetchAll();
} else {
  echo "Error:";
  echo "\n";
  $sth->debugDumpParams ();
  $children=array();
}
?>
      <div class="panel panel-default">
        <!-- Default panel contents -->
        <div class="panel-heading"><p class="lead">Other Versions</p></div>
        <div class="panel-body">
          <p>Click the link below to see the canonical version of the game, plus any variants.</p>
        </div>
        <!-- Table -->
        <table class="table">
          <thead> <tr> <th>Title</th> <th>Publisher</th> <th>Year</th> </tr> </thead> <tbody> 
<?php
foreach ($children as $child) {
			$pubs=explode('@',$child["publishers"]);
			$names='';
			foreach ($pubs as $pub) {
				if ($pub) {
					list($pid,$pname)=explode('|',$pub);
					if ($pname) $names.="$pname, ";
				}
			}
			if ($names) {
				$names=substr($names,0,strlen($names)-2);
			} else {
				$names="<i>No Publisher</i>";
			}
?>
              <tr> <td><a href="game.php?id=<?php echo $child['id']; ?>"><?php echo $child['title'];?></a></td> <td><?php echo $names;?></td> <td><?php echo $child['year'];?></td> </tr>
<?php
}
?>
          </tbody> 
        </table>
      </div>
      <hr>
<?php
}

?>
    </div> <!-- /container -->
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="bs/jquery.min.js"></script>
    <script src="bs/js/bootstrap.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="bs/js/ie10-viewport-bug-workaround.js"></script>
    <script>
// Log downloads.
function log(a,b) {
  var i = document.createElement("img");
  i.src = "count.php?t="+b+"&id="+a;
  return true;
}
log(<?php echo $id ?>,'g');
    </script>
<?php include_once("includes/googleid.php") ?>
  </body>
</html>

