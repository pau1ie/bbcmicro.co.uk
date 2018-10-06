<?php
require 'includes/config.php';
require 'includes/db_connect.php';
require 'includes/playlink.php';

$sqla="select max(imgupdated) as dt from games";
$asth = $db->prepare($sqla,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$asth->execute();
$ags=$asth->fetchAll();
//print_r($ags);

$sql="select g.id, g.title, i.filename as ifile, i.subdir as idir, s.subdir as sdir, s.filename as sfile from games g left join images i on g.id = i.gameid left join screenshots s on g.id = s.gameid order by g.title";

$sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
if ($sth->execute()) {
  $res = $sth->fetchAll();
} else {
  echo "Error:";
  echo "\n";
  $sth->debugDumpParams ();
  $res=array();
}

$cwd=getcwd();
//echo $cwd . '<br/>';
$zipf=$cwd . '/' . 'tmp/allfiles.zip';
$dump=$cwd . '/' . 'tmp/db.sql';
$stat=stat($zipf);
//echo "<pre>";
//print_r($stat);
//echo "</pre>";
//echo "<br/><br/>";
//echo "<table><tr><th>Time: </th><th>" . strtotime($ags[0]['dt']) ."</th></tr>";
//echo "<tr><th>File time: </th><th>".$stat['mtime'];
//echo "</th></tr></table>";
if ($stat && (strtotime($ags[0]['dt']) < $stat['mtime'])) {
   echo "Using cached file<br/>";
} else {
  exportDatabase(DB_HOST, DB_USER, DB_PASS, DB_NAME, $dump);
  $zip = new ZipArchive;
  $rc=$zip->open($zipf, ZipArchive::CREATE);

  if ($rc === TRUE) {
    echo "Creating image zip file...<br/>";
    foreach ($res as $line) {
//      print_r($line);
      $lfile=get_discloc($line['ifile'],$line['idir']);
      if ($lfile> " ") {
//        echo $lfile . '<br/>';
        $tf=substr($line['title'],0,1);
        $zip->addFile($lfile, $tf . '/' . $line['ifile']);
      }
    }
  } else {
    echo "<br/>Error" . $rc . '<br/>';
  }
  $zipf=$cwd . '/' . 'tmp/allscr.zip';
  $zip = new ZipArchive;
  $rc=$zip->open($zipf, ZipArchive::CREATE);

  if ($rc === TRUE) {
    echo "Creating screenshot zip file...<br/>";
//    print_r($res);
    foreach ($res as $line) {
//      print_r($line);
      $lfile=get_scrshot($line['sfile'],$line['sdir']);
      if ($lfile> " ") {
//        echo $lfile . '<br/>';
        $tf=substr($line['title'],0,1);
        $zip->addFile($lfile, $tf . '/' . $line['sfile']);
      }
    }
  } else {
    echo "<br/>Error" . $rc . '<br/>';
  }

}
echo "<a href='tmp/allfiles.zip'>All files(zip)</a><br>";
echo "<a href='tmp/allscr.zip'>All screenshots(zip)</a><br>";

function exportDatabase($host, $user, $password, $database, $targetFilePath)
{
//    echo 'mysqldump --host '. $host .' --user '. $user .' --password='. $password .' '. $database .' --result-file='.$targetFilePath;
    //returns true iff successfull
    return exec('mysqldump --host '. $host .' --user '. $user .' --password='. $password .' '. $database .' --result-file='.$targetFilePath) === 0;
}
?>
