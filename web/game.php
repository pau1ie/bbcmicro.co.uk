<?php
require 'includes/config.php';
require 'includes/db_connect.php';
require 'includes/menu.php';
require 'includes/playlink.php';

$id=0;

if ( isset($_GET["id"])) {
  $id=intval($_GET["id"]);
}
if ( isset($_GET["h"])) {
  $h=$_GET["h"];
} else { 
  $h="i";
}

$sql = "select g.id, g.title, g.year, g.notes, g.joystick, g.players_min, g.players_max, g.save, g.hardware, g.version, g.edit, g.series, g.series_no, n.name as genre, r.name as reltype from games g left join genres n on n.id = g.genre left join reltype r on r.id = g.reltype where g.id  = ?";
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
  $shot[] = array( "filename" => 'default.jpg' );
}

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

$ssd = 'gameimg/discs/' . $img["filename"];
$jsbeeb=JB_LOC;
$root=WS_ROOT;

$playlink=get_playlink($img,$jsbeeb,$root);
if ( $ssd != NULL && file_exists($ssd)) {
  $imglink='<p><a type="button" class="btn btn-primary btn-lg center-block" href="' . $ssd . '">Download</a></p>';
} else {
  $imglink="<p>No disc image available</p>";
}

if ($playlink != NULL ) {
  $imglink=$imglink .'<p><a type="button" class="btn btn-primary btn-lg center-block" href="' . $playlink . '" >Play</a></p>';
} else {
  $imglink=$imglink."<p>Can't be played online.</p>";
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

$split=explode('(',$game["title"]);
$title='<h1>' . $split[0];
if (count($split) > 1 ) {
   $title = $title . '</h1><p>(' . implode('(',array_slice($split,1)) . "</p>";
}  else {
   $title = $title . '</h1>';
}

$back_url='index.php';
$back_desc='home page';
if ($h != "h" && array_key_exists('HTTP_REFERER', $_SERVER)) {
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
    $genretab=$genretab . $genre["name"] . "<br/>";
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
    $pubtab=$pubtab . $publisher["name"] . "<br/>";
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
    $authortab=$authortab . $author["name"] . "<br/>";
  }
  $authortab=$authortab . "</td></tr>";
} else {
  $authortab="";
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

    <title><?php echo $game["title"]; ?></title>

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
    <meta property="og:image"              content="<?php echo WS_ROOT . "/gameimg/screenshots/" . $shot[0]["filename"]; ?>" />
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
          <h2>Screen Shot</h2>
          <p><img src="gameimg/screenshots/<?php echo $shot[0]["filename"];?>" class="img-responsive"></p><p>&nbsp;</p>
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
          $el = 'Electron conversion';
      } else {
          $el=$game['electron'];
      }
      $el = "<tr><th>Source</th><td>" . $el . "</td></tr>";
   }

   $sr='';
   if (!empty($game['series'])) {
      $sr = "<tr><th>Series</th><td>" . $game['series'] . "</td></tr>";
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
         $players="Single player";
      } else {
         $players=$game["players_min"] . " players";
      }
   } else {
      $players=$game["players_min"] . " to " . $game["players_max"];
   }
?>
        </div>
        <div class="col-md-4">
          <h2>Details</h2>
          <table class="table">
            <tr><th>Title</th><td><?php echo $game["title"];?></td></tr>
            <tr><th>Year</th><td><?php echo $game["year"];?></td></tr>
            <?php echo $pubtab;?></td></tr>
            <?php echo $authortab;?>
            <tr><th>Release Type</th><td><?php echo $game["reltype"];?></td></tr>
            <tr><th>Primary genre</th><td><?php echo $game["genre"];?></td></tr>
            <?php echo $genretab;?>
            <tr><th>Joystick</th><td><?php echo $js;?></td></tr>
            <tr><th>Players</th><td><?php echo $players;?></td></tr>
            <tr><th>Save</th><td><?php echo $sa;?></td></tr>
            <?php echo $hw;?>
            <?php echo $el;?>
            <?php echo $ver;?>
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
$sql="select title,(SELECT GROUP_CONCAT(CONCAT(publishers.id,'|',publishers.name) SEPARATOR '@') FROM games_publishers LEFT JOIN publishers ON pubid=publishers.id WHERE gameid=games.id) AS publishers, year from games where parent = ? ";
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
        <div class="panel-heading">Alternative Versions.</div>
          <div class="panel-body">
            <p>This entry is representative of all versions of this game, and the disc image is what we consider to be the best version. For specific variants, refer to the list below.</p>
          </div>
          <!-- Table -->
          <table class="table">
            <thead> <tr> <th>#</th> <th>Title</th> <th>Publisher</th> <th>Year</th> </tr> </thead> <tbody> 
<?php
foreach ($children as $child) {
			$pubs=explode('@',$child["publishers"]);
			$names='';
			foreach ($pubs as $pub) {
				if ($pub) {
					list($id,$name)=explode('|',$pub);
					if ($name) $names.="$name, ";
				}
			}
			if ($names) {
				$names=substr($names,0,strlen($names)-2);
			} else {
				$names="<i>No Publisher</i>";
			}
?>
              <tr> <th scope="row">1</th> <td><?php echo $child['title'];?></td> <td><?php echo $names;?></td> <td><?php echo $child['year'];?></td> </tr>
<?php
}
?>
            </tbody> 
          </table>
        </div>
      </div>
      <hr>
<?php
}
?>
    </div> <!-- /container -->
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery.min.js"><\/script>')</script>
    <script src="bs/js/bootstrap.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="bs/js/ie10-viewport-bug-workaround.js"></script>
<?php include_once("includes/googleid.php") ?>
  </body>
</html>

