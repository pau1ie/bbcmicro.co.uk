<?php

require 'includes/config.php';
require 'includes/db_connect.php';

$sql="select g.id, g.title, g.publisher, g.year, g.genre, g.reltype, i.filename, r.name from games g left join images i on g.id = i.gameid left join genres r on g.genre = r.id order by upper(substr(filename,1,7)), upper(g.title), i.filename COLLATE utf8_bin";

$sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
if ($sth->execute()) {
  $res = $sth->fetchAll();
} else {
  echo "Error:";
  echo "\n";
  $sth->debugDumpParams ();
  $res=array();
}

$gsql="select g.name from genres g left join game_genre gg on gg.genreid = g.id where gg.gameid = ?";
$gsth = $db->prepare($gsql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$gsth->bindParam(1, $id, PDO::PARAM_INT);

$asql="select a.name, a.alias from authors a left join games_authors ga on ga.authors_id = a.id where ga.games_id = ?";
$asth = $db->prepare($asql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$asth->bindParam(1, $id, PDO::PARAM_INT);

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

  $ol=array();

  $ol[]=strtoupper($fp[0]); 	//Disc
				// Title
  if (strpos($line['title'],',') === False) { 
    $ol[]=$line['title'];
  } else {
    $ol[]='"' . $line['title'] . '"';
  }

  $ol[]=$line['filename'];	// Filename
				// Publisher
  if (strpos($line['publisher'],',') === False) {
    $ol[]=$line['publisher'];
  } else {
    $ol[]='"' . $line['publisher'] . '"';
  }

  $ol[]=$line['reltype'];	// Release Type
  $ol[]=$line['name'];		// Authors
  if ( count($gen2) > 1 ) {
    $ol[]='"'.implode(', ',$gen2).'"';
  } else {
    $ol[]=implode('',$gen2);
  }
  $ol[]=$line['year'];		// Year
  $ol[]='B';       		// Platform
  $ol[]='';        		// Problems
  $ol[]='C';       		// Source
  $ol[]='';        		// Availability
  if (count($auths) > 1) {
    $ol[]='"'.implode(', ',$auths).'"';
  } else {
    if (count($auths) == 0 ) {
      $ol[]='';
    } else {
      $ol[]=$auths[0];
    }
  }
#  if (count($auths) > 1) {
#    $sauths=array_slice($auths,0,count($auths)-1);
#    $aus='"'.implode(', ',$sauths);
#    $aus=$aus . ' & ' . $auths[count($auths)-1] . '"';
#    $ol[]=$aus;
#  } else {
#    $ol[]=implode('',$auths);
#  }
  $ol2=implode(',',$ol);
  print ($ol2 . "\n");

}


?>
