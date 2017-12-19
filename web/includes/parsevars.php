<?php
function get_publisher($id) {
  global $db;

  $sql = 'select name from publishers where id = ?';
  $sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
  $sth->bindParam(1, $id, PDO::PARAM_INT);
  $sth->execute(); 
  $str=$sth->fetchColumn();
  return $str;
}

function get_genre($id) {
  global $db;

  $sql = 'select name from genres where id = ?';
  $sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
  $sth->bindParam(1, $id, PDO::PARAM_INT);
  $sth->execute(); 
  $str=$sth->fetchColumn();
  return $str;
}

function getstate() {
  $state=array();

  if ( isset($_GET["search"])) {
    $search=$_GET["search"];
    if (strlen($search) > 0 ) {
      $state['search']=$search;
    }
  }

  // Tick boxes
  $rtype = array();

  foreach($_GET as $k => $v ) {
    if (preg_match('/^rt_[A-Z]$/',$k)) {
      $state['rtype'][] = substr($k,-1);
    }
    if (preg_match('/^on_[A-Z]$/',$k)) {
      $state['only'][] = substr($k,-1);
    }
  }

  if (count($rtype)>0) {
    $state['rtype']=$rtype;
  }

  if ( isset($_GET["atoz"])) {
    $atoz=$_GET["atoz"];
    if (strlen($atoz) > 0 ) {
      $state["atoz"]=$atoz;
    }
  }

  if ( isset($_GET["page"])) {
    $page=intval($_GET["page"]);
    if ($page > 0 ) {
      $state["page"]=$page;
    }
  }

  // Search Order
  if ( isset($_GET["sort"])) {
    if ($_GET["sort"]=='p' || $_GET["sort"]=='a' || $_GET["sort"]=='b' || $_GET["sort"]=='u') {
      $state["sort"]=$_GET["sort"];
    }
  }

  return $state;
}
?>
