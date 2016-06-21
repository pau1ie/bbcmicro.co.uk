<?php
require 'includes/config.php';
require 'includes/db_connect.php';
require 'includes/menu.php';

$id=0;

if ( isset($_GET["id"])) {
  $id=intval($_GET["id"]);
}

$sql = "select g.title, g.publisher, g.year, n.name as genre from games g left join genres n on n.id = g.genre where g.id  = ?";
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

if ( empty($img) ) {
  $imglink="No image available";
} else {
  $imglink='<a type="button" class="btn btn-primary btn-lg center-block" href="gameimg/discs/' . $img["filename"] . '">Download</a>';
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

$split=explode('(',$game["title"]);
$title='<h1>' . $split[0];
if (count($split) > 1 ) {
   $title = $title . '</h1><p>(' . implode('(',array_slice($split,1)) . "</p>";
}  else {
   $title = $title . '</h1>';
}


// echo $sql;
// print_r($game);
// print_r($shot);
// print_r($genres);

$s = '';
if ( count($genres) > 1) {
  $s = 's';
}

if ( ! empty($genres)) {
  $genretab='<tr><th>Secondary genre' . $s . '</th><td><table class="table" style=\"border: none\">';
  foreach ($genres as $genre) {
    $genretab=$genretab . "<tr><td>" . $genre["name"] . "</td></tr>";
  }
  $genretab=$genretab . "</table></td></tr>";
} else {
  $genretab="";
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
    <span class="navbar-brand"><?php echo $site_name?></span>
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
          <p><img src="gameimg/screenshots/<?php echo $shot[0]["filename"];?>" class="img-responsive"></p>
          
        </div>
        <div class="col-md-4">
          <h2>Details</h2>
          <table class="table">
            <tr><th>Title</th><td><?php echo $game["title"];?></td></tr>
            <tr><th>Year</th><td><?php echo $game["year"];?></td></tr>
            <tr><th>Publisher</th><td><?php echo $game["publisher"];?></td></tr>
            <tr><th>Primary genre</th><td><?php echo $game["genre"];?></td></tr>
            <?php echo $genretab;?>
          </table>
          <p><?php echo $imglink; ?></p>
          <p><a type="button" class="btn btn-primary btn-lg center-block" href="javascript:history.go(-1)" title="Back to list">Back to list</a></p>
       </div>
      </div>
      <hr>
     </div> <!-- /container -->
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery.min.js"><\/script>')</script>
    <script src="bs/js/bootstrap.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="bs/js/ie10-viewport-bug-workaround.js"></script>
  </body>
</html>

