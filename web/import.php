<?php
require 'includes/config.php';
require 'includes/db_connect.php';
$sql="select * from importgames";
$sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
if ($sth->execute()) {
  $res = $sth->fetchAll(PDO::FETCH_ASSOC);
} else {
  echo "Error:";
  echo "\n";
  $sth->debugDumpParams ();
  $res=array();
}

$genre1d="";

$sqli="insert into games (title, year, genre, reltype, notes, save, hardware, electron, version, compilation, series, lastupdater, lastupdated) values (?,?,?,?,?,?,?,?,?,?,?,1,now())";
$sthi = $db->prepare($sqli,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

$sqlf="insert into images (gameid, filename, main) values (?,?, 100)";
$sthf = $db->prepare($sqlf,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

$sqls="insert into screenshots (gameid, filename, main) values (?,?, 100)";
$sths = $db->prepare($sqls,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

$gsql = "select id from genres g where g.name = ?";
$gsth = $db->prepare($gsql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$gsth->bindParam(1, $genren, PDO::PARAM_STR);

$psql = "select id from publishers g where g.name = ?";
$psth = $db->prepare($psql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$psth->bindParam(1, $pubn, PDO::PARAM_STR);

$pisql = "insert into games_publishers (gameid, pubid, main) values (?,?,'Y')";
$pisth = $db->prepare($pisql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

$g2sql = "select id from genres g where g.name = ?";
$g2sth = $db->prepare($g2sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$g2sth->bindParam(1, $g2n, PDO::PARAM_STR);

$g2isql = "insert into game_genre (gameid, genreid, ord) values (?,?,1)";
$g2isth = $db->prepare($g2isql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

$asql = "select id from authors a where a.name = ?";
$asth = $db->prepare($asql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$asth->bindParam(1, $an, PDO::PARAM_STR);

$aisql = "insert into games_authors (games_id, authors_id) values (?,?)";
$aisth = $db->prepare($aisql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));


echo "<pre>";
foreach ($res as $line) {
  //print($line['title']."</br>");
  $sql_binds=array();
  // print_r($line);

  /// Title
  $sql_binds[]=array($line['title'],PDO::PARAM_STR);

  // Year
  $sql_binds[]=array($line['year'],PDO::PARAM_STR);

  // Genre
  if (strlen($line['genre1']) > 1) {
    $genren=$line['genre1'];
    $gsth->execute();
    if ($gsth->rowCount() == 0 ) {
      $sql_binds[]=array(null,PDO::PARAM_NULL);
      echo "Missing genre for ".$line['title']."<br/>";
    } else {
      $garr=$gsth->fetchAll(PDO::FETCH_ASSOC);
      $sql_binds[]=array($garr[0]['id'],PDO::PARAM_INT);
    }
  } else {
    $genre1 = null;
    $sql_binds[]=array(null,PDO::PARAM_NULL);
  }

  //reltype
  $sql_binds[]=array($line['commercial'],PDO::PARAM_STR);

  //notes
  if (strlen($line['problems'] > 0)) {
    $sql_binds[]=array($line['problems'],PDO::PARAM_STR);
  } else {
    $sql_binds[]=array(null,PDO::PARAM_NULL);
  }

  // Save
  if (strlen($line['save'] > 0)) {
    $sql_binds[]=array(substr($line['save'],2,1),PDO::PARAM_STR);
  } else {
    $sql_binds[]=array(null,PDO::PARAM_NULL);
  }
  
  // Hardware
  if (strlen($line['specialr'] > 0)) {
    $sql_binds[]=array(substr($line['specialr'],2,1),PDO::PARAM_STR);
  } else {
    $sql_binds[]=array(null,PDO::PARAM_NULL);
  }

  // Electron
  if (strlen($line['electron'] > 0)) {
    $sql_binds[]=array(substr($line['electron'],0,1),PDO::PARAM_STR);
  } else {
    $sql_binds[]=array(null,PDO::PARAM_NULL);
  }

  // Version
  if (strlen($line['version'] > 0)) {
    $sql_binds[]=array($line['version'],PDO::PARAM_STR);
  } else {
    $sql_binds[]=array(null,PDO::PARAM_NULL);
  }

  // Compilation
  if (strlen($line['compilation'] > 0)) {
    $sql_binds[]=array($line['compilation'],PDO::PARAM_STR);
  } else {
    $sql_binds[]=array(null,PDO::PARAM_NULL);
  }

  // Series
  if (strlen($line['seriesno'] > 0)) {
    $sql_binds[]=array($line['seriesno'],PDO::PARAM_STR);
  } else {
    $sql_binds[]=array(null,PDO::PARAM_NULL);
  }

  executeWithDataTypes($sthi, $sql_binds);
  $rowid = $db->lastInsertId();

  // Filename
  $sql_binds=array();
  $sql_binds[]=array($rowid,PDO::PARAM_INT);
  $sql_binds[]=array($line['filename'],PDO::PARAM_STR);
  executeWithDataTypes($sthf, $sql_binds);

  // Screenshot
  $sql_binds=array();
  $sql_binds[]=array($rowid,PDO::PARAM_INT);
  $scr=substr($line['filename'],0,-3) . 'jpg';
  $sql_binds[]=array($scr,PDO::PARAM_STR);
  executeWithDataTypes($sths, $sql_binds);

  // Publisher
  $sql_binds=array();
  if (strlen($line['publisher']) > 1) {
    $pubn=$line['publisher'];
    $psth->execute();
    if ($psth->rowCount() == 0 ) {
      $pubid=null;
      echo "Missing publisher for ".$line['title']." - ".$line['publisher']."<br/>";
    } else {
      $parr=$psth->fetchAll(PDO::FETCH_ASSOC);
      $pubid=$parr[0]['id'];
    }
  } else {
    $pubid = null;
  }
  if ( !($pubid === null )) {
    $sql_binds[]=array($rowid,PDO::PARAM_INT);
    $sql_binds[]=array($pubid,PDO::PARAM_INT);
    executeWithDataTypes($pisth, $sql_binds);
  }

  // Genre 2
  $sql_binds=array();
  if (strlen($line['genre2']) > 1) {
    $g2n=$line['genre2'];
    $g2sth->execute();
    if ($g2sth->rowCount() == 0 ) {
      $g2id=null;
      echo "Missing genre2 for ".$line['title']." - ".$line['genre2']."<br/>";
    } else {
      $g2arr=$g2sth->fetchAll(PDO::FETCH_ASSOC);
      $g2id=$g2arr[0]['id'];
    }
  } else {
    $g2id = null;
  }
  if ( !($g2id === null )) {
    $sql_binds[]=array($rowid,PDO::PARAM_INT);
    $sql_binds[]=array($g2id,PDO::PARAM_INT);
    executeWithDataTypes($g2isth, $sql_binds);
  }

  // Author
  $sql_binds=array();
  if (strlen($line['author']) > 1) {
    $an=$line['author'];
    $asth->execute();
    if ($asth->rowCount() == 0 ) {
      $aid=null;
      echo "Missing author for ".$line['title']." - ".$line['author']."<br/>";
    } else {
      $aarr=$asth->fetchAll(PDO::FETCH_ASSOC);
      $aid=$aarr[0]['id'];
    }
  } else {
    $aid = null;
  }
  if ( !($aid === null )) {
    $sql_binds[]=array($rowid,PDO::PARAM_INT);
    $sql_binds[]=array($aid,PDO::PARAM_INT);
    executeWithDataTypes($aisth, $sql_binds);
  }


} 


function executeWithDataTypes(PDOStatement $sth, array $values) {
    //echo "<pre>";
    //print_r($values);
    //echo "</pre>";
    $count = 1;
    foreach($values as $value) {
        $sth->bindValue($count, $value[0], $value[1]);
        $count++;
    }
    //$sth->debugDumpParams();
    return $sth->execute();
}
?>
