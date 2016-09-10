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
   <?php make_menu_bar("About")?>
  </div><!-- /.container -->
 </nav><!-- /.navbar -->
    <!-- Main jumbotron for a primary marketing message or call to action -->
    <div class="jumbotron">
      <div class="container">
        <h1><?php echo $site_name?></h1>
        <p>Over the last few years we have been lovingly restoring some BBC micro games from the 1980s onwards. We have put them here so you can play them in an emulator, on a real BBC Micro, or on the web. If you would like to help us improve the site, join us in the Stardot forums. See the <a href="contact.php">contact page</a> for more information.</p>
      </div>
    </div>
    <div class="container">
      <!-- Example row of columns -->
      <div class="row">
        <div class="col-md-4">
          <h2>Games</h2>
          <p>Which game should you play? There are more than 2000 games on the website, but which are the very best? In the <a href="http://stardot.org.uk/forums/viewtopic.php?f=1&t=8259">Stardot forums</a>, members contributed their faviourite games. The top 20 are:</p>
          <ol>
            <li><a href="game.php?id=366">Elite</a>. The classic space trading game.</li>
            <li><a href="game.php?id=25">Chuckie Egg</a>. There are also some unofficial variants.</li>
            <li><a href="game.php?id=425">Repton 3</a>. Search for other Repton games.</li>
            <li><a href="game.php?id=709">Exile</a></li>
            <li><a href="game.php?id=35">Starship Command</a></li>
            <li><a href="game.php?id=432">Thrust</a></li>
            <li><a href="game.php?id=290">Citadel</a></li>
            <li><a href="game.php?id=298">Repton 2</a></li>
            <li><a href="game.php?id=267">Revs</a>. See also, <a href="game.php?id=1128">Revs 4 tracks.</a></li>
            <li><a href="game.php?id=266">Repton</a></li>
            <li><a href="game.php?id=512">Imogen</a></li>
            <li><a href="game.php?id=564">Codename: Droid</a></li>
            <li><a href="game.php?id=598">Firetrack</a></li>
            <li><a href="game.php?id=20">Arcadians</a></li>
            <li><a href="game.php?id=85">Mr Ee</a></li>
            <li><a href="game.php?id=54">Zalaga</a></li>
            <li><a href="game.php?id=238">Castle Quest</a></li>
            <li><a href="game.php?id=438">Galaforce</a>. The sequel, <a href="game.php?id=692">Aliens' Revenge</a> came in at 51</li>
            <li><a href="game.php?id=822">Skirmish</a></li>
            <li><a href="game.php?id=14">Snapper</a>. This looks very much like Pacman. Hmmm</li>
          </ol>
          <p> What do you think? Have we missed your favourite game? Sign up and let us know at the forum!</p>
        </div>
        <div class="col-md-4">
          <h2>Status</h2>
          <p>The site is still under development. We believe we have most of the games written for the BBC Micro apart from a few very obscure ones which we are still trying to track down. If you know of a game we have missed, or even better if you own one, contact us in the forums and we will get it added to the database. We maintain a list of titles which we believe to be <a href="http://stardot.org.uk/forums/viewtopic.php?f=7&t=3437&start=90#p134474">missing from the archive</a> in the forums. </p>
	  <p>&nbsp;</p>
          <h2>Thanks</h2>
          <p>In addition to the people who worked on the archive and the website, we would like to give a big <strong>thank you</strong> to <a href="https://plus.google.com/+MattGodbolt">Matt Godbolt</a> for developing <a href="http://bbc.godbolt.org">jsbeeb</a>, and sharing it with the world on <a href="https://github.com/mattgodbolt/jsbeeb">github</a>. It makes the website come alive - anyone can play the BBC Micro games right in the browser!</p>
	  <p>&nbsp;</p>
          <h2>Source</h2>
          <p>This web site is open source. and the <A href="https://github.com/pau1ie/bbcmicro.co.uk">source is on github</a>. Please feel free to take it and use it for your own website. We would be interested to hear what you have done with it in the forums. </p>
       </div>
        <div class="col-md-4">
           <h2>Who we are</h2><p>
The games on this website, almost without exception, were originally curated by Mick Brown, who announced the "30th Anniversary BBC & Electron Collection" in 2014 and started <a href="http://www.stardot.org.uk/forums/viewtopic.php?f=32&t=8270">releasing disc images on the Stardot forum</a> in May of that year. 
</p><p>
The collection is made up of games that Mick had first copied to his own "unofficial" compilation discs thirty years before. 
</p><p>
Mick has enhanced the games by adding user-friendly instructions (taken from cassette inlays or other authentic sources) and ensuring that the games are compatible with a range of emulators as well as real Acorn hardware.
</p><p>
Our thanks go to Mick for the countless hours of work he has put into compiling, enhancing, testing, and re-testing the games in this collection.
</p><p>
Thanks also to Paul Houghton for developing the <a href="bbcmicro.co.uk">bbcmicro.co.uk</a> website, and to Gary for hosting it.
</p><p>
Lee "Eagle Eyes" Newsome proofread the game instructions, tested every game several times, and coordinated the work on the website project.
Huge thanks to Dave Moore (user Arcadian on Stardot) for his enduring commitment to preserving and promoting all things Acorn, online and off, including the creation of the invaluable <a href="http://www.stairwaytohell.com">Stairway To Hell</a> archive. 
</p><p>
We're grateful to the incredibly knowledgeable members of the Stardot forum for their generous help and support.
</p>
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

