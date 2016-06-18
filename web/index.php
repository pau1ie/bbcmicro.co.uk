<?php

include 'db_connect.php';
include 'header.php';

$url='';
$year=0;
$pubid=0;
$sid=0;
$title="";

if ( isset($_GET["title"])) {
  $title=$_GET["title"];
  if (strlen($title) > 0 ) {
    $url=$url . '&title='.$title;
  }
}

if ( isset($_GET["pubid"])) {
  $pubid=intval($_GET["pubid"]);
  if ($pubid > 0 ) {
    $url=$url . '&pubid='.$pubid;
    $publisher=get_publisher($pubid);
  }
}

if ( isset($_GET["year"])) {
  $year=intval($_GET["year"]);
  if ($year > 0 ) {
    $url=$url . '&year='.$year;
  }
}

if ( isset($_GET["sid"])) {
  $sid=intval($_GET["sid"]);
  if ($sid > 0 ) {
    $url=$url . '&sid='.$sid;
  }
}

$url=htmlspecialchars($url);

function get_publisher($id) {
  global $db;

  $sql = 'select name from publishers where id = ?';
  $sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
  $sth->bindParam(1, $id, PDO::PARAM_INT);
  $sth->execute(); 
  $str=$sth->fetchColumn();
  return $str;
}


function gameitem( $id,  $name, $image, $ssd, $publisher, $year, $pubid) {
   global $sid;

   $split=explode('(',$name);
   $title=$split[0];
   $rest="";
   if (count($split) > 1 ) {
      $rest = '<p>(' . implode('(',array_slice($split,1)) . "</p>";
   }
   if ($sid > 0 ) {
      $rest = $rest . $id . "<br/>";
   }
   
?>
     <div class="col-sm-6 col-md-4 col-lg-3">
      <div class="thumbnail text-center">
       <a href="game.php?id=<?php echo $id; ?>"><img src="<?php echo $image; ?>" alt="<?php echo $image; ?>" class="pic"></a>
       <h3><a href="game.php?id=<?php echo $id; ?>"><?php echo $title ?></a></h3><?php echo $rest; ?>
       <a href="?year=<?php echo $year ?>"><?php echo $year; ?></a>
       <a href="?pubid=<?php echo $pubid ?>"><?php echo $publisher ?></a>
<?php
  if ($ssd != null ) { ?>
       <p><a href="<?php echo $ssd ?>" type="button" class="btn btn-default">Download</a></p>
<?php
  }
?>
      </div>
     </div>
<?php
}

function pager($limit, $rows, $page, $url) {
  global $publisher, $year;
  $pages = ceil($rows/$limit);

  echo '    <ul class="pagination">';
  if ( $page != 1 ) {
     echo '     <li><a href="?page=' . ($page - 1) . $url . '">&laquo;</a></li>' . "\n";
  }else{
     echo '     <li class="disabled"><a href="'. $page . '">&laquo;</a></li> '. "\n";
  }
  for ( $i=1; $i <= $pages; $i++ ) {
    if ( ($i % 5 == 0 ) || (($i > ($page - 4)) && ( $i < ($page + 4))) || ( $i == 1) || ( $i == $pages) ) {
      if ($i != $page ) {
        echo '     <li><a href="?page=' . $i . $url . '">' . $i . '</a></li>' . "\n";
      } else {
        echo '     <li class="active"><a href="?page=' . $i . $url . '">' . $i . '</a></li> '. "\n";
      }
    }
  }
  if ( $page != $pages ) {
     echo '     <li><a href="?page=' . ($page + 1) . $url . '">&raquo;</a></li> '. "\n";
  }else{
     echo '     <li class="disabled"><span>&raquo;</span></li> '. "\n";
  }
  echo "    </ul>\n";
}


function grid($url, $title, $year, $publisher) {
  global $db;

  $limit = 30;
  if ( isset($_GET["page"])) {
    $page=$_GET["page"];
    if ($page == 0 ) {
      $page = 1;
    }
  } else {
    $page=1;
  }
  $wc=array();
  $binds=array();
  $where='';

  if ($publisher > 0 ) {
    $wc[]="pubid = :publisher";
    $where="WHERE ";
  }

  if ($year > 0 ) {
    $wc[]="year = :year";
    $where="WHERE ";
  }

  if (strlen($title) > 0) {
    $title="%".$title."%";
    $wc[]="title like :title";
    $where="WHERE ";
  }

  $offset = $limit * ($page -1);
  $sql ='select SQL_CALC_FOUND_ROWS * from bbc.games ' . $where . implode(' AND ',$wc) . ' order by title LIMIT :limit OFFSET :offset';

  $sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
  if ($publisher > 0 ) {
    $sth->bindParam(':publisher',$publisher, PDO::PARAM_INT);
  }
  if ($year > 0 ) {
    $sth->bindParam(':year',$year, PDO::PARAM_INT);
  }
  if (strlen($title) > 0) {
    $sth->bindParam(':title',$title, PDO::PARAM_STR);
  }
  $sth->bindParam(':limit',$limit, PDO::PARAM_INT);
  $sth->bindParam(':offset',$offset, PDO::PARAM_INT);
  if ($sth->execute()) {
    $res = $sth->fetchAll();
  } else {
    echo "Error:";
    echo "\n";
    $sth->debugDumpParams ();
    $res=array();
  }

  $sfr = $db->prepare("SELECT FOUND_ROWS();");
  if ($sfr->execute()) {
    $sfr_result = $sfr->fetch(PDO::FETCH_ASSOC);
    $rows = $sfr_result['FOUND_ROWS()'];
  } else {
    echo "Error:";
    $sfr->debugDumpParams ();
  }

  $scrsql = 'select filename from screenshots where gameid = :gameid order by main, id limit 1';
  $dscsql = 'select filename from images where gameid = :gameid order by main, id limit 1';
  $scrpdo = $db->prepare($scrsql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
  $dscpdo = $db->prepare($dscsql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

  pager($limit,$rows,$page,$url);
  echo '    <div class="row" style="display:flex; flex-wrap: wrap;">'."\n";
  foreach ( $res as $game ) {
    $scrpdo->bindParam(':gameid',$game["id"], PDO::PARAM_INT);
    $dscpdo->bindParam(':gameid',$game["id"], PDO::PARAM_INT);
    if ($scrpdo->execute()) {
      $img=$scrpdo->fetch(PDO::FETCH_ASSOC);
      if (is_null($img["filename"])) {
        $shot="default.jpg";
      } else {
        $shot = $img["filename"];
      }
    } else {
      echo "Error:";
      $sim->debugDumpParams ();
    }
    if ($dscpdo->execute()) {
      $dnl=$dscpdo->fetch(PDO::FETCH_ASSOC);
      if (is_null($dnl["filename"])) {
        $ssd=null;
      } else {
        $ssd = 'gameimg/discs/' . $dnl["filename"];
      }
    } else {
      echo "Error:";
      $sim->debugDumpParams ();
    }

    gameitem($game["id"],htmlspecialchars($game["title"]),'gameimg/screenshots/' . $shot, $ssd ,htmlspecialchars($game["publisher"]),$game["year"],$game["pubid"]);
  }
  echo "    </div>\n";

  pager($limit,$rows,$page,$url);
  echo "   </div>\n";

}

htmlhead();
nav();
containstart();
grid($url, $title, $year, $pubid);
sidebar($title,$year,$pubid,$publisher);
containend();
htmlfoot();

