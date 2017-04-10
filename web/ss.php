<?php

if ( $_SERVER['REQUEST_METHOD']== "GET" ) {
require 'includes/config.php';
require 'includes/db_connect.php';

$sql="select g.id, g.title, g.year, g.genre, g.reltype, g.players_max, g.players_min, g.joystick, i.filename, r.name, g.save, g.hardware, g.electron, g.version, g.compilation, g.series, g.series_no, g.notes from games g left join images i on g.id = i.gameid left join genres r on g.genre = r.id order by upper(substr(filename,1,7)), upper(g.title), i.filename COLLATE utf8_bin";

$sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
if ($sth->execute()) {
  $res = $sth->fetchAll();
} else {
  echo "Error:";
  echo "\n";
  $sth->debugDumpParams ();
  $res=array();
}

$gsql="select g.name from genres g left join game_genre gg on gg.genreid = g.id where gg.gameid = ? order by gg.ord";
$gsth = $db->prepare($gsql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$gsth->bindParam(1, $id, PDO::PARAM_INT);

$asql="select a.name, a.alias from authors a left join games_authors ga on ga.authors_id = a.id where ga.games_id = ?";
$asth = $db->prepare($asql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$asth->bindParam(1, $id, PDO::PARAM_INT);

$psql="select a.name from publishers a left join games_publishers ga on ga.pubid = a.id where ga.gameid =?";
$psth = $db->prepare($psql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$psth->bindParam(1, $id, PDO::PARAM_INT);

?>
<html lang="en">
<head>
 <meta charset="utf-8">
</head>
<body>
<table><tr>
<th>Disc</th><th>title</th><th>publisher</th><th>filename</th><th>Commercial</th><th>genre</th><th>genre2</th><th>year</th><th>author</th><th>playersmin</th><th>playersmax</th><th>joystick</th><th>save</th><th>hardware</th><th>series/no</th><th>hardware</th><th>electron</th><th>version</th></tr>
<?php

foreach ($res as $line) {
  // print_r($line);
  $fp=explode('-',$line['filename']);
  $id=$line['id'];
  // Secondary Genres

  $gsth->execute(); 
  $sgs=$gsth->fetchAll();
  $gen2=array();
  // print_r($sgs);
  foreach ($sgs as $sg) { 
    $gen2[]=$sg['name'];
  }

  // Author

  $asth->execute(); 
  $ags=$asth->fetchAll();
  $auths=array();
  // print_r($ags);
  foreach ($ags as $auth) { 
    if ( count($auth['alias']) > 0 ) {
      $auths[]=$auth['name'] . ' (' . $auth['alias'] . ')';
    }else{
      $auths[]=$auth['name'];
    }
  }

  // Publisher

  $psth->execute(); 
  $pgs=$psth->fetchAll();
  $pubs=array();
  // print_r($ags);
  foreach ($pgs as $pub) {
    $pubs[]=$pub['name'];
  }

  $ol=array();

  $ol[]=strtoupper($fp[0]); 	//Disc
				// Title
  $ol[]=$line['title'];

				// Publisher
  $ol[]=implode(', ',$pubs);

  $ol[]=$line['filename'];	// Filename

  $ol[]=$line['reltype'];	// Release Type
  $ol[]=$line['name'];		// Authors

  $ol[]=implode('',$gen2);

  $ol[]=$line['year'];		// Year

  $ol[]=implode(', ',$auths);

  $ol[]=$line['players_min'];
  $ol[]=$line['players_max'];
  $ol[]=$line['joystick'];
  if ( $line['save'] == 'D' or $line ['save'] =='T' ) {
    $ol[]='ST'.$line['save'];
  } else {
    $ol[]=$line['save'];
  }

  $ol[]=$line['compilation'];

  $ol[]=trim($line['series'].' '.$line['series_no']);
  $ol[]=$line['hardware'];
  if ($line['electron'] == 'Y') {
    $ol[]=$line['electron'].'es';
  } else {
    $ol[]=$line['electron'];
  }
  $ol[]=$line['version'];
#  $ol[]=$line['notes'];
  $ol2='<tr><td>'.implode('</td><td>',$ol).'</td></tr>';
  print ($ol2 . "\n");

}
}

?>
