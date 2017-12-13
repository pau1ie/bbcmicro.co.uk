<?php
require('includes/admin_session.php');
require_once('includes/config.php');
require_once('includes/admin_db_open.php');
require_once('includes/admin_menu.php');

show_admin_menu();

if ( isset($_GET['id']) && is_numeric($_GET['id']) ) {
  $id = $_GET['id'];
} else {
  echo('No id.');
  exit(1);
}

$rec=0;
if ( $_GET['t'] == 'd' ) {
  // Disc image
  $updir='gameimg/discs/';
  $t="images";
  $title="<b>Image</b>";
  $fu=false;
} elseif ( $_GET['t'] == 's' ) {
  // Screenshot
  $updir='gameimg/screenshots/';
  $t="screenshots";
  $title="<b>Screen Shot</b>";
} elseif ( $_GET['t'] == 'f' ) {
  // Screenshot
  $updir='gameimg/files/';
  $t="files";
  $title="<b>Files</b>";
} else {
  print('Missing file type');
  exit(2);
}

if ( isset($_POST['filename']) && is_numeric($_POST['filename'] )) {
  $pf=$_POST['filename'];
} else {
  $pf=-1;
}

if (isset($_POST['customurl']))  {
  $cu=$_POST['customurl'];
} else {
  $cu=-1;
}

$s="select * from ". $t . " where gameid = ? and main = 100";

$sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->bindParam(1, $id, PDO::PARAM_INT);

if ($sth->execute()) {
  $r=$sth->fetch(PDO::FETCH_ASSOC);
  $sth->closeCursor();
  //echo "<br/>SQL:<pre>";print($s);echo "</pre>";
  //echo "<br/>Result:<pre>";print_r($r);echo "</pre>";
  if ($r === False ) $rec=-1;
  //print count($r);
  $ldir=$updir . $id;
} else {
  echo "$s gave ".$dbh->errorCode()."<br>\n";
  exit(3);
}

// Check directory exists.
if (!is_dir($ldir)) {
  echo "<br/>Creating missing directory: ".$ldir;
  if (mkdir($ldir)) {
    if ($rec != -1 ) {
      if (rename($updir.'/'.$r['filename'],$ldir.'/'.$r['filename']) ) {
        $s="update ".$t." set subdir=? where gameid = ? and main = 100";
        $sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(1, $id, PDO::PARAM_STR);
        $sth->bindParam(2, $id, PDO::PARAM_INT);
        if ($sth->execute()) {
          echo "<br/>Database updated.<br/>";
          $rec=1;
        } else {
          echo "<br/>DB Update failed.<br/>";
        }
      } else {
        echo "File rename failed.";
      }
    }
  } else {
    echo "Failed. Missing permissions? These must be fixed before files can be manipulated.<br/>";
    exit();
  }
}

echo "<br/>";

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html><head><meta http-equiv="Content-Type" content="text/html; charset=windows-1256" /></head><body>
<form enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']."?t=".$_GET['t']."&id=".$id; ?>" method="POST">
<h2><?php echo $title ?></h2>
Please choose a file: <input name="file" type="file" /><br />
<input type="submit" value="Upload" /></form>
<?php 

if (!empty($_FILES["file"])) {
  if ($_FILES["file"]["error"] > 0) {
    $phpFileUploadErrors = array(
      0 => 'There is no error, the file uploaded successfully.',
      1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
      2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
      3 => 'The uploaded file was only partially uploaded.',
      4 => 'No file was uploaded.',
      6 => 'Missing a temporary folder.',
      7 => 'Failed to write file to disk.',
      8 => 'A PHP extension stopped the file upload.',
    );
    $ue=$_FILES["file"]["error"];
    echo "Error " . $ue . ': ' . $phpFileUploadErrors[$ue] . "<br/><br/>";
  } else {
    echo "Stored file:".$_FILES["file"]["name"]."<br/>Size:".($_FILES["file"]["size"]/1024)." kB<br/>";
    if ($_FILES["file"]["size"] == 0 ) {
      echo "File is empty. Please try again.<br/>";
    } else {
      $fn = preg_replace('/[^a-zA-Z0-9_\.-]/s', '', $_FILES["file"]["name"]);
      $fni=pathinfo($fn);
      if ( !(defined('ST_ALLOW_OVERWRITE') && ST_ALLOW_OVERWRITE )) {
        $i=1;
        while (is_file($ldir.'/'.$fn)) {
          $fn=$fni['filename'].'-'.$i.'.'.$fni['extension'];
          $i++;
        }
      }
      if ( mb_strlen($fn,"UTF-8") > 255 ) {
        echo "Filename is too long (> 255 chars). Please shorten it.";
      } else {
        move_uploaded_file($_FILES["file"]["tmp_name"],$ldir.'/'.$fn);
        if ($fn==$r['filename']) {
          $fu=True;
        }
      }
    }
  }
}

$ignored = array('.', '..', '.svn', '.htaccess');

$dfiles = array();    
foreach (scandir($ldir) as $file) {
  if (in_array($file, $ignored)) continue;
  $dfiles[$file] = filemtime($ldir . '/' . $file);
}

arsort($dfiles);
$dirArray = array_keys($dfiles);
$indexCount=count($dirArray);

if ($pf >= 0 && $pf < $indexCount) {
  if ($dirArray[$pf] != $r['filename']) {
    if ($rec != -1 ) {
      $s="update ".$t." set filename=? where gameid = ? and main = 100";
    } else {
      $s="insert into ".$t." (filename, gameid, main, subdir) values (?,?,100,".$id.")";
    }

    $sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->bindParam(1, $dirArray[$pf], PDO::PARAM_STR);
    $sth->bindParam(2, $id, PDO::PARAM_INT);
//    echo $s."<br/>";
    if ($sth->execute()) {
      echo "<br/>Database updated.";
      $r['filename']=$dirArray[$pf];
      $fu=True;
    } else {
      echo "<br/>DB Update failed.";
    }
  }
}
//echo "cu=".$cu;
if (($cu != -1) && ($_GET['t'] == 'd')) {
  if ($cu != $r['customurl']) {
    $s="update ".$t." set customurl=? where gameid = ? and main = 100";
    $sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->bindParam(1, $cu, PDO::PARAM_STR);
    $sth->bindParam(2, $id, PDO::PARAM_INT);
//    echo $s."<br/>";
    if ($sth->execute()) {
      echo "<br/>Database updated.";
      $r['customurl']=$cu;
      $fu=True;
    } else {
      echo "<br/>DB Update failed.";
    }
  }
}

$date=date('Y-m-d H:i:s');
if ($fu == True && $_GET['t']=='d') {
  $s="update games set imgupdated=?, imgupdater=? where id = ?";
  $sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
  $sth->bindParam(1, $date, PDO::PARAM_STR);
  $sth->bindParam(2, $_SESSION['userid'], $_SESSION['userid']);
  $sth->bindParam(3, $id, PDO::PARAM_INT);
//  echo $s."<br/>";
  if ($sth->execute()) {
    echo "<br/>Database timestamp updated.";
  } else {
    echo "<br/>DB Update timestamp failed.";
    echo "<pre>Error:";
    echo "\n";
    $sth->debugDumpParams ();
    $res=array();
    print_r($sth->ErrorInfo());
    echo "</pre>";
  }
}

if ( $indexCount > 0) { 
echo "<TABLE border=1 cellpadding=5 cellspacing=0 class=whitelinks><TR><TH>Selected</TH><TH>Filename</TH><th>Filetype</th><th>Content type</th><th>Filesize</th><th>Date</th></TR>\n";

echo "<form ". $_SERVER['PHP_SELF']."?t=".$_GET['t']."&id=".$id ." method=\"POST\">";
for($index=0; $index < $indexCount; $index++) {
  if ($dirArray[$index] == $r['filename']) { $ticked='checked'; } else { $ticked=''; }
    echo "<TR>
      <td><input type=\"radio\" name=\"filename\" value=\"".$index."\"  ".$ticked."/></td>
      <td><a href=\"".$ldir.'/'.$dirArray[$index]."\">$dirArray[$index]</a></td>
      <td>".filetype($ldir.'/'.$dirArray[$index])."</td>
      <td>".mime_content_type($ldir.'/'.$dirArray[$index])."</td>
      <td>".filesize($ldir.'/'.$dirArray[$index])."</td>
      <td>".date("d/m/Y H:i:s", filemtime($ldir.'/'.$dirArray[$index]))."</td>
      </TR>";
}
?>
</TABLE></br/>
<?php
if ( $_GET['t'] == 'd' ) {
  echo "<label> Custom URL for jsbeeb <input type='text' name='customurl' size='40' value='".$r['customurl']."'/> Enter NONE to not play in jsbeeb. %jsbeeb% for the jsbeeb location, and %wsroot% for the base URL of the website.</label><br/><br/>";
}
?>
<input type="submit" value="Submit"></form>
<?php } ?>
</body>
</html>
