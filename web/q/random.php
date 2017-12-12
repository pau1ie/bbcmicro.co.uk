<?php
require '../includes/config.php';
require '../includes/db_connect.php';

function randomgame() {
  global $db;

  $sql = "select count(*) from games";
  $sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
  if ($sth->execute()) {
    $res = $sth->fetchAll();
  } else {
    echo "Error:";
    echo "\n";
    $sth->debugDumpParams ();
    $res=array();
  }
  $n=rand(1,$res[0][0])-1;

  $sql = "select id from games limit ?,1";
  $sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
  $sth->bindParam(1, $n, PDO::PARAM_INT);
  if ($sth->execute()) {
    $res = $sth->fetchAll();
  } else {
    echo "Error:";
    echo "\n";
    $sth->debugDumpParams ();
    $res=array();
  }
  $id=$res[0][0];
  header("Location: ../game.php?id=".$id);
}
randomgame();
?>
