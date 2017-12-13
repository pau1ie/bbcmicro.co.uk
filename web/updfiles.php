<?php
require 'includes/config.php';
require 'includes/db_connect.php';
require 'includes/playlink.php';
  $sql = "select id, gameid, subdir, filename from images";
  $sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
  if ($sth->execute()) {
    $res = $sth->fetchAll();
  } else {
    echo "Error:";
    echo "\n";
    $sth->debugDumpParams ();
    exit(2);
  }
  $sql="update games set imgupdated = ? where id = ?";
  $sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

  foreach ($res as $file) {
    $imgfile=get_discloc($file['filename'],$file['subdir']);
    if ($imgfile != null ) {

      $mtime=filemtime($imgfile);
      $dt=date("Y-m-d H:i:s",$mtime);
      echo "<br/>$dt - $imgfile";

      $sth->bindParam(1, $dt, PDO::PARAM_STR, 19);
      $sth->bindParam(2, $file['gameid'], PDO::PARAM_INT);
      if ($sth->execute()) {
        echo " - Updated OK";
      } else {
        echo " - Error updating ".$file['id'];
        $sth->debugDumpParams ();
        exit(2);
      }
//      echo "<br/>";
    }
  }
?>
