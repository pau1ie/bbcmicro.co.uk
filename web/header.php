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
    <link rel="icon" href="../../favicon.ico">

    <title><?php echo $site_name?></title>

    <!-- Bootstrap core CSS -->
    <link href="bs/css/bootstrap.min.css" rel="stylesheet">
    <link href="bs/css/bootstrap-theme.min.css" rel="stylesheet">
    <link rel="stylesheet" href="bs/offcanvas.css">
    <link rel="stylesheet" href="bs/css/typeahead.css">

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
    <span class="navbar-brand"><?php echo $site_name?></span>
   </div>
   <div id="navbar" class="collapse navbar-collapse">
    <ul class="nav navbar-nav">
     <li><a href="index.php">Games</a></li>
     <li><a href="about.php">About</a></li>
     <li><a href="contact.php">Contact</a></li>
    </ul>
   </div><!-- /.nav-collapse -->
  </div><!-- /.container -->
 </nav><!-- /.navbar -->
<?php
}

function sidebar($title, $year, $pubid, $publisher) {
  $title = htmlspecialchars($title,ENT_QUOTES);

?>
   <div class="col-xs-3 col-sm-2 sidebar-offcanvas" id="sidebar"><!--div class="sidebar-nav-fixed pull-right affix" -->
    <h3>Search</h3>
    <form id="searchform" action="index.php" method="get">
     <fieldset class="form-group" >
      <label for="title">Title</label>
      <input id="title" name="title" class="form-control" type="text" placeholder="Title" value="<?php echo $title ; ?>" />
     </fieldset>
     <fieldset class="form-group" id="year-search" >
      <label for="year">Publication Year</label>
      <input id="year" name="year" class="typeahead form-control" type="text" placeholder="Year" value="<?php echo ($year > 0) ? $year :  ""; ?>" />
     </fieldset>
     <fieldset class="form-group" id="pub-search" >
      <label for="publisher">Publisher</label>
      <input name="pubid" id="pubid" class="hidden" type="hidden" value="<?php echo ($pubid > 0 ) ? $pubid : ""; ?>" />
      <input id="publisher" class="typeahead form-control" type="text" placeholder="Publisher" value="<?php echo ($pubid > 0 ) ? $publisher : ""; ?>" />
     </fieldset>
     <div class="form-actions center-block" >
       <button type="submit" class="btn btn-default">Search</button>
       <button id="reset" type="reset" class="btn btn-default">Clear</button>
     </div><!--/div-->
    </form>
   </div><!--/.sidebar-offcanvas-->

<?php
}

function containstart() {
?>
 <div class="container">
  <div class="row row-offcanvas row-offcanvas-right">
   <div class="col-xs-12 col-sm-10">
    <p class="pull-right visible-xs">
     <button type="button" class="btn btn-primary btn-xs" data-toggle="offcanvas">Toggle nav</button>
    </p>
<?php
}

function containend() { 
?>
  </div><!-- Container -->
 </div>
<?php
}

function yeararray() {
  global $db;

  $sql = "select distinct year from games order by year";
  $sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
  if ($sth->execute()) {
    $res = $sth->fetchAll();
  } else {
    echo "Error:";
    echo "\n";
    $sth->debugDumpParams ();
    $res=array();
  }
  $str= "var dates = [";
  foreach ($res as $row) {
    $str=$str . "'" . htmlspecialchars($row["year"]) . "',"; 
  }
  $str=rtrim($str,", ");
  $str = $str . '];';
  echo $str . "\n";
}


function htmlfoot() {
?>
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
 <script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery.min.js"><\/script>')</script>
 <script src="bs/js/bootstrap.min.js"></script>
 <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
 <script src="bs/js/ie10-viewport-bug-workaround.js"></script>
 <script src="bs/offcanvas.js"></script>
 <script src="bs/js/typeahead.js"></script>
 <script>
var substringMatcher = function(strs) {
  return function findMatches(q, cb) {
    var matches, substringRegex;

    // an array that will be populated with substring matches
    matches = [];

    // regex used to determine if a string contains the substring `q`
    substrRegex = new RegExp(q, 'i');

    // iterate through the pool of strings and for any string that
    // contains the substring `q`, add it to the `matches` array
    $.each(strs, function(i, str) {
      if (substrRegex.test(str)) {
        matches.push(str);
      }
    });

    cb(matches);
  };
};

<?php 
yeararray();

// Set up the year typeahead search
?>

$('#year-search .typeahead').typeahead({
  hint: true,
  highlight: true,
  minLength: 0
},
{
  name: 'dates',
  source: substringMatcher(dates),
  limit: 11
}).on('typeahead:selected', function(e){
    e.target.form.submit();
});

<?php // Set up the publisher typeahead search ?>
$(document).ready(function() {
  setSearchAutocomplete();
  $( "#searchform" ).submit(function( event ) {
      if ($("#publisher" ).val().length==0 ) {
        $("#pubid" ).val("");
      }
      $("#publisher" ).val("");
  });
  // Make the clear button work
  $( "#reset" ).click(function() {
    $("#title").val('');
    $("#year").val('');
    $("#publisher").val('');
    return false;
  });
});

function setSearchAutocomplete() {
  var publishers = new Bloodhound({
    datumTokenizer: function(d) {return Bloodhound.tokenizers.whitespace(d.name); },
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    remote: {
      url: 'q?qt=publisher&qv=%QUERY%',
      wildcard: '%QUERY%'
    }
  });
  setTypeaheadBinding('#pub-search .typeahead', publishers);
}

function setTypeaheadBinding(selector, adapter) {
  $(selector).typeahead(null, {
    name: 'publishers',
    displayKey: 'name',
    source: adapter.ttAdapter(),
    templates: {
      empty: [
        '<div class="empty-message text-center">',
        'No publishers found.<br>',
        '</div>',
      ].join('\n')
    }
  }).on('typeahead:selected', function(e){
    e.target.form.submit();
  });
}

$('.typeahead').on('typeahead:selected typeahead:autocompleted', function(e, datum) {
  $("#pubid" ).val(datum.id);
});
  </script>
</body>
</html><?php
}
