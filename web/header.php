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
    <style type="text/css">
      .thumbnail {
        height: 100%;
        margin-bottom: 0;
      }
      .thumb1 {
        padding-bottom: 20px;
      }
    </style>

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
   <div id="navbar" class="collapse navbar-collapse">
   <?php make_menu_bar("Games")?>
   </div><!-- /.nav-collapse -->
  </div><!-- /.container -->
 </nav><!-- /.navbar -->
<?php
}

function get_reltypes() {
  global $db;

  $sql = "select distinct id, name, selected from reltype order by name";
  $sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
  if ($sth->execute()) {
    $res = $sth->fetchAll();
  } else {
    echo "Error:";
    echo "\n";
    $sth->debugDumpParams ();
    $res=array();
  }
  return $res;
}


function sidebar($state) {
  searchbox($state);
  if (array_key_exists('search',$state)) {
    refines($state);
  }
  searchbuttons();
}

function searchbox($state) {
  if (array_key_exists('search',$state)) {
    $search = htmlspecialchars($state['search'],ENT_QUOTES);
  } else {
    $search= "";
  }
?>
   <div class="col-xs-3 col-sm-2 sidebar-offcanvas" id="sidebar"><!--div class="sidebar-nav-fixed pull-right affix" -->
    

     <fieldset class="form-group" id="search">
      <label for="search"><h3>Search</h3></label>
      <input id="searchbox" name="search" class="typeahead form-control" type="text" placeholder="Search" value="<?php echo $search ; ?>" />
     </fieldset>
<?php
}

function refines($state) { ?>
     <h4>Only include matches on:</h4>
<?php
  $types=array('T'=>'Title','Y'=>'Year','P'=>'Publisher','G'=>'Primary Genre','S'=>'Subgenre');
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
       <label><input type="checkbox" name="on_<?php echo $tid; ?>" <?php echo $checked ?>><?php echo $type ?></label>
      </div>
<?php
  }
}

function reltypes($state) {
?>
     <h4>Browse release types:</h4>
      <div id="reltypes" class="form-inline">
<?php
   $reltyps=get_reltypes();
   foreach ( $reltyps as $reltyp ) {
      $checked='';
      if (!array_key_exists('rtype',$state) || count($state['rtype'])==0){
         if ($reltyp['selected'] == 'Y') {
            $checked='checked';
         }
      } else {
         if (array_key_exists('rtype',$state) && array_search($reltyp['id'],$state['rtype'])===False) {
            ;
         }else{
            $checked='checked';
         }
      }
?>
      <div class="checkbox">
       <label><input type="checkbox" name="rt_<?php echo $reltyp['id']; ?>" <?php echo $checked ?>> <?php echo $reltyp['name'] ?>&emsp; </label>
      </div>
<?php
   }
   echo "      </div>";
}

function searchbuttons() {
?>
     <div id="refine" class="form-actions center-block" >
      <div class="form-actions center-block" >
       <button type="submit" class="btn btn-default">Search</button>
       <button id="reset" type="reset" class="btn btn-default">Clear</button>
      </div>
     </div>
    </div><!--/.sidebar-offcanvas-->
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
    </p>
<?php
  reltypes($state);
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
// Make the boxes the same height
function equalHeight(group) {    
    var tallest = 0;    
    group.each(function() {       
        var thisHeight = $(this).height();       
        if(thisHeight > tallest) {          
            tallest = thisHeight;       
        }    
    });    
    group.each(function() { $(this).height(tallest); });
} 

<?php // Set up the typeahead search ?>
$(document).ready(function() {
  var suggestions = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('title'),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    //prefetch: '../data/films/post_1960.json',
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
  // Make the clear button work
  $( "#reset" ).click(function() {
    $("#searchbox").val('');
    return false;
  });
  // Make release type tick boxes dynamic
  $( "#reltypes" ).change(function() {
     $("form").submit();
  });

 // equalHeight($(".row"));
});

  </script>
<?php include_once("includes/googleid.php") ?>
</body>
</html><?php
}

