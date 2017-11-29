<?php
require 'includes/config.php';
require 'includes/db_connect.php';

if ($_GET['t'] == "d" ) {
  $sql='insert into game_downloads (id, year, downloads) values (?,?,1) on duplicate key update downloads=downloads+1';
} elseif ($_GET['t'] == "g" ) {
  $sql='insert into game_downloads (id, year, gamepages) values (?,?,1) on duplicate key update gamepages=gamepages+1';
} else {
  exit;
}

$id=(int)$_GET['id'];
$year=(int)date("Y");

$sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->bindParam(1, $id, PDO::PARAM_INT);
$sth->bindParam(2, $year, PDO::PARAM_INT);
if ($sth->execute()) {
  echo "OK";
} else {
  echo "<pre>Error:";
  echo "\n";
  $sth->debugDumpParams ();
  echo "</pre>";
}

?>
