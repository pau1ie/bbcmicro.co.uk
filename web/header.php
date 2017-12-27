<?php
function htmlhead() {
global $site_name;
?><!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico" type="image/png">
    <link type="image/png" rel="shortcut icon" href="favicon.ico"/>

    <title><?php echo $site_name?></title>

    <!-- Bootstrap core CSS -->
    <link href="bs/css/bootstrap.min.css" rel="stylesheet">
    <link href="bs/css/bootstrap-theme.min.css" rel="stylesheet">
    <link rel="stylesheet" href="bs/offcanvas.css">
    <link rel="stylesheet" href="bs/css/typeahead.css">
    <link rel="stylesheet" href="bs/css/grid.css">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body><?php
}

function nav() {
global $site_name;
?>
 <nav class="navbar navbar-fixed-top navbar-inverse">
  <div class="container">
   <div class="navbar-header">
    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
     <span class="sr-only">Toggle navigation</span>
     <span class="icon-bar"></span>
     <span class="icon-bar"></span>
     <span class="icon-bar"></span>
    </button>
    <a class="navbar-brand" href="index.php"><?php echo $site_name?></a>
   </div>
   <?php make_menu_bar("Games")?>
  </div><!-- /.container -->
 </nav><!-- /.navbar -->
<?php
}


function sidebar($state) {
?>   <div class="col-xs-3 col-sm-2 sidebar-offcanvas" id="sidebar">
<?php
  searchbox($state);
  if (array_key_exists('search',$state)) {
    refines($state);
  }
  searchbuttons();
  if (!array_key_exists('search',$state)) {
    randomgame();
  }
  echo "    </div>\r";
}

function searchbox($state) {
  if (array_key_exists('search',$state)) {
    $search = htmlspecialchars($state['search'],ENT_QUOTES);
  } else {
    $search= "";
  }
?>
     <fieldset class="form-group" id="search">
      <label for="search"><h3>Search</h3></label>
      <input id="searchbox" name="search" class="typeahead form-control" type="text" placeholder="Search" value="<?php echo $search ; ?>" />
     </fieldset>
     <fieldset class="form-group" id="order">
<?php
}

function randomgame() {
?>     <p>&nbsp;</p><h3>Random Game</h3>
       <p><a href="q/random.php" class="btn btn-default btn-lg btn-block">Lucky Dip</a></p>
<?php
}

function refines($state) { ?>
     <h4>Only include matches on:</h4>
<?php
  $types=array('T'=>'Title','Y'=>'Year','P'=>'Publisher','A'=>'Author','G'=>'Primary Genre','S'=>'Secondary Genre','Z'=>'Series','C'=>'Compilation');
  foreach ( $types as $tid => $type ) {
    $checked='';
    if (array_key_exists('only',$state) && count($state['only'])==0){
      $checked='checked';
    } else {
      if (array_key_exists('only',$state) && array_search($tid,$state['only'])===False) {
        ;
      }else{
        $checked='checked';
      }
    }
?>
      <div class="checkbox">
       <label><input type="checkbox" name="on_<?php echo $tid; ?>" <?php echo $checked ?>/><?php echo $type ?></label>
      </div>
<?php
  }
}


function searchbuttons() {
  global $state;

  $s='b'; // Default sort order - Releases. 
  $sortbtn='';
  if (isset($state['sort'])) {
    $sortbtn='name="sort'.$state['sort'].'"';
    $s=$state['sort'];
  }
  $sel='<span style="float:right">&#10004;</span>';
?>
<br/>
<h4>Sort by</h4>
<label style="font-weight: 400"><input type="radio" name="sort" value="p" <?php if ($s=="p") echo "checked"; ?>/> Popular</label><br/>
<label style="font-weight: 400"><input type="radio" name="sort" value="a" <?php if ($s=="a") echo "checked"; ?>/> Alphabetic</label><br/>
<label style="font-weight: 400"><input type="radio" name="sort" value="u" <?php if ($s=="u") echo "checked"; ?>/> Latest Updates</label><br/>
<label style="font-weight: 400"><input type="radio" name="sort" value="b" <?php if ($s=="b") echo "checked"; ?>/> Latest Releases</label><br/>
<div class="btn-group btn-block">
  <button type="submit" class="btn btn-default btn-lg btn-block">Search</button>
</div> 
<?php
}

function containstart($state) {
?>
 <form id="searchform" action="index.php" method="get">
 <div class="container">
  <div class="row row-offcanvas row-offcanvas-right">
   <div class="col-xs-12 col-sm-10">
    <p class="pull-right visible-xs">
     <button type="button" class="btn btn-primary btn-xs" data-toggle="offcanvas">Search</button>
    </p><?php // Add a button to catch searches using the enter key ?>
    <button style="overflow: visible !important; height: 0 !important; width: 0 !important; margin: 0 !important; border: 0 !important; padding: 0 !important; display: block !important;" type="submit"></button>
<?php
}

function containend() { 
?>
  </div><!-- Container -->
 </div>
 </form>
<?php
}

function htmlfoot() {
?>

 <script src="bs/jquery.min.js"></script>
 <script src="bs/js/bootstrap.min.js"></script>
 <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
 <script src="bs/js/ie10-viewport-bug-workaround.js"></script>
 <script src="bs/offcanvas.js"></script>
 <script src="bs/js/typeahead.js"></script>
 <script>
<?php // Set up the typeahead search ?>
$(document).ready(function() {
  var suggestions = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('title'),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    remote: {
      url: 'q?qt=suggestions&qv=%QUERY%',
      wildcard: '%QUERY'
    }
  });

  $('#search .typeahead').typeahead(null, {
    name: 'suggestions',
    displayKey: 'title',
    source: suggestions,
    matcher: function (t) {
        return t;
    }
  });
});
// Log downloads.
function log(a) {
  var i = document.createElement("img");
  i.src = "count.php?t=d&id="+a;
  return true;
}
  </script>
<?php include_once("includes/googleid.php") ?>
</body>
</html><?php
}

