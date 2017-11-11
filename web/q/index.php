<?php
// For use in search boxes - return JSON encoded key values.

require '../includes/config.php';
require '../includes/db_connect.php';

$limit=20;

if ( isset($_GET["qt"])) {
  $qtype=$_GET["qt"];
}

if ( isset($_GET["qv"])) {
  $qvalue=$_GET["qv"];
}

switch ($qtype) {
  case "suggestions":
    $sql = "select title from games where title like :query union select name from publishers where name like :query union select series from games where series like :query union select name from genres where name like :query union select distinct year from games where year like :query union select name from authors where name like :query union select compilation from games where compilation like :query";
    $qvalue = $qvalue.'%';
    break;
  case "publisher":
    $sql = "select id, name from publishers where name like :query";
    $qvalue = '%'.$qvalue.'%';
    break;
  case "genre1":
    $sql = "select id, name from genres where id in (select distinct genre from games) and name like :query";
    $qvalue = '%'.$qvalue.'%';
    break;
  default:
    exit();
}

$sql=$sql.' limit '.$limit;

$sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->bindParam(':query', $qvalue, PDO::PARAM_STR);

if ($sth->execute()) {
  $str=json_encode($sth->fetchAll(PDO::FETCH_ASSOC));
  echo $str . "\n";
} else {
  echo "Error:";
  echo "\n";
  $sth->debugDumpParams ();
  $res=array();
}



?>
