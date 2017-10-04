<?php
session_start();
if (!array_key_exists('bbcmicro',$_SESSION)) {
	header("Location: login.php");
	exit;
}

require_once('includes/config.php');
require_once('includes/admin_db_open.php');
require_once('includes/admin_menu.php');

show_admin_menu();

$id=null;
# GET params means want to edit a name ...
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
  $id=intval($_GET['id']);
} else {
  # POST params mean an update
  if (isset($_POST) && $_POST) {
    $name=$_POST['name'];
    if (isset($_POST['id']) && is_numeric($_POST['id'])) {
      $id=intval($_POST['id']);
    } else {
      $id=null;
    }
    if ( strlen($name) < 1 ) {
        $msg = "Name can't be blank";
    } else {
      if ( $id == null ) {
        $s="insert into publishers (name) values (?)";
        $sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(1, $name, PDO::PARAM_STR);
	if ( $sth->execute() ) {
          $id=$dbh->lastInsertId();
          $msg="New publisher added: ".$id.".";
        } else {
          $msg="Error adding publisher";
        }
      } else {
        $s="update publishers set name=? where id = ?";
        $sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(1, $name, PDO::PARAM_STR);
        $sth->bindParam(2, $id, PDO::PARAM_INT);
        $sth->execute();
	if ( $sth->execute() ) {
          $msg="publisher updated.";
        } else {
          $msg="Error updating publisher";
        }
      }
    }
  }
}

if ($id > 0) {
  $s="select * from publishers where id = ?";

  $sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
  $sth->bindParam(1, $id, PDO::PARAM_INT);

  if ($sth->execute()) {
    $r=$sth->fetch(PDO::FETCH_ASSOC);
    $sth->closeCursor();
    if ($r === False ) $rec=-1;
  } else {
    echo "$s gave ".$dbh->errorCode()."<br>\n";
    exit(3);
  }
} else {
  $r['name']='';
  $r['id']='';
  $msg="New publisher.";
}

make_form($r,$msg);

function make_form($r,$msg) {
  echo "<br><b>".$r['name']."</b>";
  echo "<hr>";
  echo "<p>$msg</p>\n";
  echo "<form name='frmGame' method='POST' action='admin_publishers_details.php'>\n";
  echo "<input type='hidden' name='id' value='".$r['id']."'>\n";

  echo "<label>Name: <input type='text' name='name' size='80' value='".htmlspecialchars($r['name'],ENT_QUOTES)."'/></label><br/><br/>";
 
  echo '<br/><input type="submit" value="Submit"></form>';
  echo '<hr/><a href="admin_publishers.php">Back to the list</a>';
}
?>
</body>
</html>
