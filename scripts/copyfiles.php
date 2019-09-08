<?php
require dirname(__FILE__) . '/../web/includes/config.php';
require dirname(__FILE__) . '/../web/includes/db_connect.php';
require dirname(__FILE__) . '/../web/includes/playlink.php';

define('WEBBASE','/vagrant/web');
define('UNZIPBASE','/home/vagrant');

$sql = "select * from screenshots";
$sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->bindParam(1, $id, PDO::PARAM_INT);
if ($sth->execute()) {
  $shots = $sth->fetchAll();
} else {
  echo "Error:";
  echo "\n";
  $sth->debugDumpParams ();
  $shots=array();
}

foreach ($shots as $shot) {
	//$imagefile = WEBBASE . '/' . get_scrshot($shot['filename'],$shot['subdir']);
	$subdir = $shot['subdir'];
	$file = $shot['filename'];
	if ($subdir === NULL or $subdir === '') {
		$imagefile = WEBBASE . '/gameimg/screenshots/' . $file;
	} else {
		$imagefile = WEBBASE . '/gameimg/screenshots/' . $subdir . '/' . $file;
	}
	$dir = dirname($imagefile);
	if ( ! is_dir($dir)) {
		mkdir($dir , 0777,True);
	}
	$files=glob(UNZIPBASE . '/screens/?/*-' . $shot['gameid'] . '.???');
	if (sizeof($files) > 1) {
		print("Too many images match");
	        print_r($shot);
	}
	if (sizeof($files)!=0) {
		copy($files[0], $imagefile);
	} else {
		print("Screnshot missing for:");
		print_r($shot);
	}
}

$sql = "select * from images";
$sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->bindParam(1, $id, PDO::PARAM_INT);
if ($sth->execute()) {
  $shots = $sth->fetchAll();
} else {
  echo "Error:";
  echo "\n";
  $sth->debugDumpParams ();
  $shots=array();
}

foreach ($shots as $shot) {
	$subdir=$shot['subdir'];
	$file=$shot['filename'];
	if ($subdir === NULL or $subdir === '') {
		$imagefile = WEBBASE . '/gameimg/discs/' . $file;
	} else {
		$imagefile = WEBBASE . '/gameimg/discs/' . $subdir . '/' . $file;
	}
	$dir = dirname($imagefile);
	if ( ! is_dir($dir)) {
		mkdir($dir , 0777,True);
	}
	$files=glob(UNZIPBASE . '/files/?/*-' . $shot['gameid'] . '.???');
	if (sizeof($files) > 1) {
		print("Too many images match");
	        print_r($shot);
	}
	if (sizeof($files)!=0) {
		copy($files[0], $imagefile);
	} else {
		print("Screnshot missing for:");
		print_r($shot);
	}
}
?>
