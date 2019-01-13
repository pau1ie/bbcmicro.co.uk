<?php
require 'includes/config.php';
require 'includes/db_connect.php';
require 'includes/playlink.php';
require 'includes/extract_db.php';

?>
<!DOCTYPE html>
<html>
 <head>
 <meta charset="utf-8">
 <meta name="robots" content="noindex">
</head>
<body>
<p>
<?php

$sqla="select max(imgupdated) as dt from games";
$asth = $db->prepare($sqla,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$asth->execute();
$ags=$asth->fetchAll();

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
$zipf=$cwd . '/' . 'tmp/allfiles.zip';
$stat=stat($zipf);

if ($stat && (strtotime($ags[0]['dt']) < $stat['mtime'])) {
   echo "Using cached file<br/>";
} else {
  $zip = new ZipArchive;
  $rc=$zip->open($zipf, Ziparchive::CREATE | ZipArchive::OVERWRITE);

  if ($rc === TRUE) {
    echo "Creating image zip file...<br/>";
    foreach ($res as $line) {
      $lfile=get_discloc($line['ifile'],$line['idir']);
      if ($lfile> " ") {
        $fn=get_fn($line['title'],$lfile,$line['id']);
        $zip->addFile($lfile, $fn);
      }
    }
    $text = get_data();
    $zip->addFromString('00index.html',$text);
  } else {
    echo "<br/>Error" . $rc . '<br/>';
  }

  $zipf=$cwd . '/' . 'tmp/allscr.zip';
  $zip = new ZipArchive;
  $rc=$zip->open($zipf, Ziparchive::CREATE | ZipArchive::OVERWRITE);

  if ($rc === TRUE) {
    echo "Creating screenshot zip file...<br/>";
    foreach ($res as $line) {
      $lfile=get_scrshot($line['sfile'],$line['sdir']);
      if ($lfile> " ") {
        $fn=get_fn($line['title'],$lfile,$line['id']);
        $zip->addFile($lfile, $fn);
      }
    }
  } else {
    echo "<br/>Error" . $rc . '<br/>';
  }

}
echo "<a href='tmp/allfiles.zip'>All files(zip)</a><br>";
echo "<a href='tmp/allscr.zip'>All screenshots(zip)</a><br>";
echo "</p></body></html>";

function get_fn($otitle,$file,$id) {
  $path_parts = pathinfo($file);
  $title=preg_split('/[,(]/',$otitle)[0];
  $title=preg_replace("/[^a-zA-Z0-9]+/","",$title);
  $tf=substr($otitle,0,1);
  if (is_numeric($tf)) {
    $tf = '0';
  } else {
    $tf = strtoupper($tf);
  }
  $fn=$tf . '/' . $title.'-'.$id.'.'.$path_parts['extension'];
  return $fn;
}

?>
