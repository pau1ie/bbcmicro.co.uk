<?php
require 'includes/config.php';
require 'includes/db_connect.php';
require 'includes/menu.php';
require 'includes/playlink.php';

$id=0;

if ( isset($_GET["id"])) {
  $id=intval($_GET["id"]);
}

$sql = "select g.id, g.title_article, g.title from games g where g.id  = ?";
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
$filename = basename($ssd);


$back_url='index.php';
$back_desc='Home Page';
if (array_key_exists('HTTP_REFERER', $_SERVER)) {
  if ( parse_url($_SERVER["HTTP_REFERER"],PHP_URL_HOST) == $_SERVER["SERVER_NAME"] ) {
    $back_url = "javascript:history.go(-1)";
    $back_desc='Back to Details';
  }
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title><?php echo $game['title']; ?> - BBC Disc Image Displayer</title>
  <link href="bs/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="icon" href="favicon.ico">

  <title><?php echo $filename; ?></title>

  <!-- Bootstrap core CSS -->
  <link href="bs/css/bootstrap.min.css" rel="stylesheet">

  <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
  <link href="bs/css/ie10-viewport-bug-workaround.css" rel="stylesheet">

  <!-- Custom styles for this template -->
  <link href="bs/css/jumbotron.css" rel="stylesheet">


  <style>
    .hidden {
      display: none;
    }

    .flexContainer {
      display: flex;
      align-items: center;
      margin-top: 20px;
    }

    .spacer {
      -ms-flex: 1;
      -webkit-flex: 1;
      flex: 1;
    }

    .flexItem {
    }

    .removeMarginsAndPadding {
      padding: 0;
      margin: 0;
    }

    .pull-right {
      float: right !important;
    }
  </style>
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
      <h1><?php echo $game['title_article'], $game['title']; ?></h1>
    </div>

    <div id="diskContents" style="display: none;">

      <div class="container-fluid">
       <div class="flexContainer">
        <h3 class="flexItem removeMarginsAndPadding">Disc Contents <small id="diskContentName"></small>
        </h3>
        <div class="spacer">&nbsp;</div>
        <div class="flexItem" id="backButton">
          <a class="btn btn-primary btn-sm" href="<?php echo $back_url; ?>"><?php echo $back_desc; ?></a>
        </div>
      </div>

      <br/>

      <p><strong>Disc Title: </strong><span id="diskTitle" class="text-info"></span></p>
      <p><strong>Disc Writes: </strong><span id="diskWrites" class="text-info"></span></p>
      <p><strong>Disc Size: </strong><span id="diskSize" class="text-info"></span></p>
      <p><strong>Boot Option: </strong><span id="bootOption" class="text-info"></span></p>

      <table id="fileTable" class="table table-bordered">
        <thead>
          <tr>
            <th>Directory</th>
            <th>File Name</th>
            <th>Load Address</th>
            <th>Exec Address</th>
            <th>Length</th>
            <th>Locked?</th>
            <th>&nbsp;</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>

      <ul class="nav nav-pills">
        <li class="active"><a id="ptxt" data-toggle="pill" href="#txt">Text</a></li>
        <li><a id="pbas"  data-toggle="pill" href="#bas">Basic</a></li>
        <li><a id="phex" data-toggle="pill" href="#hex">Hex dump</a></li>
        <li><a id="pdis" data-toggle="pill" href="#dis">Disassembly</a></li>
	<li><a id="pscr" data-toggle="pill" href="#scr">Screen Dump</a></li>
      </ul>
  
      <div class="tab-content">
        <div id="txt" class="tab-pane fade in active">
          <pre id="contentstxt"> </pre>
        </div>
        <div id="bas" class="tab-pane fade">
          <pre id="contentsbas"> </pre>
        </div>
        <div id="hex" class="tab-pane fade">
          <pre id="contentshex"> </pre>
        </div>
        <div id="dis" class="tab-pane fade">
          <pre id="contentsdis"> </pre>
        </div>
        <div id="scr" class="tab-pane fade">
          <pre id="contentsscr">
            <div><canvas class="canvas" id="beebScreen0" width="640" height="512"></canvas></div>
            <div><canvas class="canvas" id="beebScreen1" width="640" height="512"></canvas></div>
            <div><canvas class="canvas" id="beebScreen2" width="640" height="512"></canvas></div>
            <div><canvas class="canvas" id="beebScreen4" width="640" height="512"></canvas></div>
            <div><canvas class="canvas" id="beebScreen5" width="640" height="512"></canvas></div>
          </pre>
        </div>
      </div>
      <p>Page generated by the <a href="https://github.com/shawty/BBCB_DFS_Catalog">BBC Disc Image Displayer</a> <small>by <a href="https://twitter.com/shawty_ds">!Shawty!</a></small></p>

    </div>

  </div>

  <script src="bs/jquery.min.js"></script>
  <script src="bs/js/bootstrap.min.js"></script>
  <script src="dfs/dfscat.js"></script>
  <script>
    // Main runtime entry point here
    openDisk("<?php echo $ssd;?>");
  </script>
</body>
</html>
