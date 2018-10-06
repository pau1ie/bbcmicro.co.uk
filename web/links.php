<?php
require 'includes/config.php';
require 'includes/menu.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">

    <title><?php echo $site_name?> - About us</title>

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
    <a href="index.php" class="navbar-brand"><?php echo $site_name?></a>
   </div>
   <?php make_menu_bar("Links")?>
  </div><!-- /.container -->
 </nav><!-- /.navbar -->
    <!-- Main jumbotron for a primary marketing message or call to action -->
    <div class="jumbotron">
      <div class="container">
        <h1><?php echo $site_name?></h1>

	<h2>Emulators</h2>

	<a href='http://b-em.bbcmicro.com' target='_blank'>B-Em (Windows/Linux)</a><br>
	<br>
	<a href='http://www.mkw.me.uk/beebem' target='_blank'>BeebEm (Windows)</a><br>
	<br>
	<a href='http://www.g7jjf.com/beebemmac.htm' target='_blank'>BeebEm (Mac)</a><br>
	<br>
	<a href='http://bbc.godbolt.org' target='_blank'>JSBeeb (Online/Javascript)</a><br>
	<br>
	<a href='http://www.mamedev.org/release.html' target='_blank'>MAME (Windows)</a><br>
	<h2>Acorn/BBC Micro Sites</h2>

	<a href='http://www.stardot.org.uk' target='_blank'>StarDot Forum</a><br>
	<br>
	<a href='http://www.retrosoftware.co.uk' target='_blank'>Retro Software</a><br>
	<br>
	<a href='http://www.stairwaytohell.com' target='_blank'>Stairway To Hell (No longer maintained)</a><br>
	<br>
	<a href='http://www.8bs.com' target='_blank'>8-Bit Software</a><br>
	<br>
	<a href='http://www.flaxcottage.com/educational/' target='_blank'>Educational Software Archive</a><br>

      </div>
    </div>

      <hr>



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

