<?php
// For use in search boxes - return JSON encoded key values.

require '../includes/config.php';
require '../includes/db_connect.php';

if ( isset($_GET["qt"])) {
  $qtype=$_GET["qt"];
}

if ( isset($_GET["qv"])) {
  $qvalue=$_GET["qv"];
}

switch ($qtype) {
  case "publisher":
    $sql = "select id, name from publishers where name like ?";
    $qvalue = '%'.$qvalue.'%';
    break;
  case "genre1":
    $sql = "select id, name from genres where id in (select distinct genre from games) and name like ?";
    $qvalue = '%'.$qvalue.'%';
    break;
  default:
    exit();
}


$sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->bindParam(1, $qvalue, PDO::PARAM_STR);

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
