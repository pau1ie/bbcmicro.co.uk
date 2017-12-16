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

//  if ( isset($_GET["pubid"])) {
//    $pubid=intval($_GET["pubid"]);
//    if ($pubid > 0 ) {
//      $state['pubid']=$pubid;
//    }
//  }

//  if ( isset($_GET["year"])) {
//    $year=intval($_GET["year"]);
//    if ($year > 0 ) {
//      $state['year']=$year;
//    }
//  }

  // Search Order

  if ( isset($_GET["sortr"])) {
    $state['sort']='r';
  }

  if ( isset($_GET["sortb"])) {
    $state['sort']='b';
  }

  if ( isset($_GET["sortu"])) {
    $state['sort']='u';
  }

  if ( isset($_GET["sorta"])) {
    $state['sort']='a';
  }

  if ( isset($_GET["sortp"])) {
    $state['sort']='p';
  }


  return $state;
}
?>
