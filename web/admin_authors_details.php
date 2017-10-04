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
$msg='';
# GET params means want to edit a name ...
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
  $id=intval($_GET['id']);
} else {
  # POST params mean an update
  if (isset($_POST) && $_POST) {
    $name=$_POST['name'];
    $alias=$_POST['alias'];
    if (isset($_POST['id']) && is_numeric($_POST['id'])) {
      $id=intval($_POST['id']);
    } else {
      $id=null;
    }
    if ( strlen($name) < 1 ) {
        $msg = "Name can't be blank";
    } else {
      if ( $id == null ) {
        $s="insert into authors (name, alias) values (?,?)";
        $sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(1, $name, PDO::PARAM_STR);
	if ( strlen($alias)== 0 ) {
          $alias=null;
          $sth->bindParam(2, $alias, PDO::PARAM_NULL);
        } else {
          $sth->bindParam(2, $alias, PDO::PARAM_STR);
        }
	if ( $sth->execute() ) {
          $id=$dbh->lastInsertId();
          $msg="New author added: ".$id.".";
        } else {
          $msg="Error adding author";
        }
      } else {
        $s="update authors set name=?, alias=? where id = ?";
        $sth = $dbh->prepare($s,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(1, $name, PDO::PARAM_STR);
	if ( strlen($alias)== 0 ) {
          $alias=null;
          $sth->bindParam(2, $alias, PDO::PARAM_NULL);
        } else {
          $sth->bindParam(2, $alias, PDO::PARAM_STR);
        }
        $sth->bindParam(3, $id, PDO::PARAM_INT);
        $sth->execute();
	if ( $sth->execute() ) {
          $msg="Author updated.";
        } else {
          $msg="Error updating author";
        }
      }
    }
  }
}

if ($id > 0) {
  $s="select * from authors where id = ?";

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
  $r['alias']='';
  $r['id']='';
  $msg="New author.";
}

make_form($r,$msg);

function make_form($r,$msg) {
  echo "<br><b>".$r['name']."</b>";
  if ( strlen($r['alias'])>0) {
    echo " (".$r['alias'].")";
  }
  echo "<hr>";
  echo "<p>$msg</p>\n";
  echo "<form name='frmGame' method='POST' action='admin_authors_details.php'>\n";
  echo "<input type='hidden' name='id' value='".$r['id']."'>\n";

  echo "<label>Name: <input type='text' name='name' size='80' autofocus='autofocus' value='".htmlspecialchars($r['name'],ENT_QUOTES)."'/></label><br/><br/>";
  echo "<label>Alias: <input type='text' name='alias' size='80' value='".htmlspecialchars($r['alias'],ENT_QUOTES)."'/></label><br/><br/>";
 
  echo '<br/><input type="submit" value="Submit"></form>';
  echo '<hr/><a href="admin_authors.php">Back to the list</a>';
}
?>
</body>
</html>
