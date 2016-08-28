<?php


function atoz_line($current='',$chars) {
  global $state;

  echo "<div>";
  echo "<ul style=\"margin-bottom:0;\" class=\"pagination\">";
  foreach ($chars as $char) {
    $active=($current==$char)?' class="active"':'';
    echo "<li$active><a href='?" . url_state($state,'atoz', urlencode($char))."'>$char</a></li>";
  }
  echo "</ul>";
  echo "</div>";
}

function gameitem( $id,  $name, $image, $ssd, $publisher, $year, $pubid) {
   global $sid;

   $jsbeeb=JB_LOC;
   $root=WS_ROOT;

   $split=explode('(',$name);
   $title=$split[0];
   
?>
     <div class="col-sm-6 col-md-4 col-lg-3">
      <div class="thumbnail text-center">
       <a href="game.php?id=<?php echo $id; ?>"><img src="<?php echo $image; ?>" alt="<?php echo $image; ?>" class="pic"></a>
       <p><span class="lead"><a href="game.php?id=<?php echo $id; ?>"><?php echo $title ?></a></span><br/>
       <a href="?pubid=<?php echo $pubid ?>"><?php echo $publisher?></a>
       -&nbsp;<a href="?year=<?php echo $year ?>"><?php echo $year; ?></a></p> 
<?php
  if ($ssd != null && file_exists($ssd)) { ?>
       <p><a href="<?php echo $ssd ?>" type="button" class="btn btn-default">Download</a>
          <a id="plybtn" href="<?php echo $jsbeeb . $root . '/' . $ssd ?>" type="button" class="btn btn-default">Play</a></p>
<?php
  }
?>
      </div>
     </div>
<?php
}

function json_state($state, $ko, $vo) {

  foreach ($state as $key => $value) {
    if ( $key == 'only' ) {
       foreach ($state['only'] as $k => $v ) {
         $s2['on_'.$v]='on';
       }
    } elseif ( $key == 'rtype' ) {
       foreach ($state['rtype'] as $k => $v ) {
         $s2['rt_'.$v]='on';
       }
    } else {
      $s2[$key]=$value;
    }
  }
  $s2[$ko]=$vo;
  return json_encode($s2,JSON_HEX_QUOT);
}

function url_state($state, $k, $v) {
  $state[$k]=$v;
  $url='';

  foreach ($state as $key => $value) {
    if ( $key == 'only' ) {
       foreach ($state['only'] as $k => $v ) {
         $url=$url.'&on_'.$v.'=on';
       }
    } elseif ( $key == 'rtype' ) {
       foreach ($state['rtype'] as $k => $v ) {
         $url=$url.'&rt_'.$v.'=on';
       }
    } else {
      $url=$url.'&'.$key.'='.$value;
    }
  }
  return substr($url,1);  //Skip first &
}

function pager($limit, $rows, $page, $state) {
  global $publisher, $year;
  $pages = ceil($rows/$limit);

  echo '    <ul class="pagination">';
  if ( $page != 1 ) {
      echo '     <li><a onclick=\'$.get("getgrid.php", '. json_state($state,'page', ($page - 1)).', function(data){ $("#maingrid").html(data); }); return false;\' href="?'. url_state($state,'page', ($page - 1)). '">&laquo;</a></li>' . "\n";
  }else{
     echo '     <li class="disabled"><span>&laquo;</span></li> '. "\n";
  }
  for ( $i=1; $i <= $pages; $i++ ) {
    if ( ($i % 5 == 0 ) || (($i > ($page - 4)) && ( $i < ($page + 4))) || ( $i == 1) || ( $i == $pages) ) {
      if ($i != $page ) {
        echo '     <li><a onclick=\'$.get("getgrid.php", '.json_state($state,'page', $i).', function(data){ $("#maingrid").html(data); }); return false;\' href="?'.url_state($state,'page', $i).'">' . $i . '</a></li>' . "\n";
      } else {
        echo '     <li class="active"><a onclick=\'$.get("getgrid.php", '. json_state($state,'page', $i).', function(data){ $("#maingrid").html(data); }); return false;\' href="?'.url_state($state,'page', $i).'">' . $i . '</a></li> '. "\n";
      }
    }
  }
  if ( $page != $pages ) {
      echo '     <li><a onclick=\'$.get("getgrid.php", '. json_state($state,'page', ($page + 1)).', function(data){ $("#maingrid").html(data); }); return false;\' href="?'. url_state($state,'page', ($page + 1)). '">&raquo;</a></li>' . "\n";
  }else{
     echo '     <li class="disabled"><span>&raquo;</span></li> '. "\n";
  }
  echo "    </ul>\n";
}

function grid($state) {
  global $db;

  $limit=GD_IPP;

  $wc=array();
  $sls=array();
  $binds=array();

  $all=( !array_key_exists('only', $state) || count($state['only']) == 0);

  if (array_key_exists ('search', $state)) {
    if ( $all || !(array_search('T',$state['only'])===False )) {
      $sls[] = "title like :search\n";
    }
    if ( $all || !(array_search('P',$state['only'])===False )) {
      $sls[] = "pubid in (select id from publishers where name like :search)\n";
    }
    if ( $all || !(array_search('Y',$state['only'])===False )) {
      $sls[] = "year like :search\n";
    }
    if ( $all || !(array_search('G',$state['only'])===False )) {
      $sls[] = "genre in (select id from genres where name like :search)\n";
    }
    if ( $all || !(array_search('S',$state['only'])===False )) {
      $sls[] = "id in (select gameid from game_genre m, genres g where g.id = m.genreid and g.name like :search)\n";
    }
  }

  if (count($sls) > 0) {
    $wc[] = '(' . implode (' OR ',$sls) . ')';
  }

  if (array_key_exists ('pubid', $state)) {
    $wc[] = "pubid = :pubid\n";
  }

  if (array_key_exists ('year', $state)) {
    $wc[] = "year = :year\n";
  }

  $doing_atoz_numbers=false;
  if (array_key_exists('atoz',$state)) {
    if ($state['atoz']=='#') {
      $doing_atoz_numbers=true;
      $wc[]="title REGEXP \"^[0-9]\"\n";
    } else {
      $atoz=substr($state['atoz'],0,1)."%";
      $wc[]="title like :atoz\n";
    }
  }

  if (array_key_exists('rtype',$state)) {
    $wc[]="FIND_IN_SET(reltype,:array)\n";
  } else {
    $wc[]=" reltype in (select id from reltype where selected = 'Y')\n";
  }

  if (array_key_exists('page',$state)) {
    $page=$state['page'];
  } else {
    $page=1;
  }

  $offset = $limit * ($page -1);
  $sql ='select SQL_CALC_FOUND_ROWS * from games WHERE ' . implode(' AND ',$wc) . ' order by title LIMIT :limit OFFSET :offset';
  $sql2 = 'select distinct upper(substring(title,1,1)) AS c1 from games WHERE ' . implode(' AND ',$wc) . "order by c1"; 

  $sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
  $sth2 = $db->prepare($sql2,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

  if (array_key_exists('search',$state)) {
    $search="%".str_replace(' ','%',$state['search'])."%";
    $sth->bindParam(':search', $search, PDO::PARAM_STR);
    $sth2->bindParam(':search', $search, PDO::PARAM_STR);
  }
  if (array_key_exists('atoz',$state) && !$doing_atoz_numbers) {
    $atoz=$state['atoz'].'%';
    $sth->bindParam(':atoz',$atoz, PDO::PARAM_STR);
    $atoz2='%';
    $sth2->bindParam(':atoz',$atoz2, PDO::PARAM_STR);
  }
  if (array_key_exists('rtype',$state)) {
    $t=implode(',',$state['rtype']);
    $sth->bindParam(':array',$t);
    $sth2->bindParam(':array',$t);
  }
  if (array_key_exists ('pubid', $state)) {
    $sth->bindParam(':pubid', $state['pubid'], PDO::PARAM_STR);
    $sth2->bindParam(':pubid', $state['pubid'], PDO::PARAM_STR);
  }
  if (array_key_exists ('year', $state)) {
    $sth->bindParam(':year', $state['year'], PDO::PARAM_STR);
    $sth2->bindParam(':year', $state['year'], PDO::PARAM_STR);
  }

  $sth->bindParam(':limit',$limit, PDO::PARAM_INT);
  $sth->bindParam(':offset',$offset, PDO::PARAM_INT);
  if ($sth->execute()) {
    $res = $sth->fetchAll();
  } else {
    echo "<pre>Error:";
    echo "\n";
    $sth->debugDumpParams ();
    $res=array();
    print_r($sth->ErrorInfo());
    echo "</pre>";
  }

  $sfr = $db->prepare("SELECT FOUND_ROWS();");
  if ($sfr->execute()) {
    $sfr_result = $sfr->fetch(PDO::FETCH_ASSOC);
    $rows = $sfr_result['FOUND_ROWS()'];
  } else {
    echo "Error:";
    $sfr->debugDumpParams ();
  }

  if ($sth2->execute()) {
    $res2 = $sth2->fetchAll();
  } else {
    echo "<pre>Error2:";
    echo "\n";
    $sth2->debugDumpParams ();
    $res2=array();
    print_r($sth2->ErrorInfo());
    echo "</pre>";
  }

  $scrsql = 'select filename from screenshots where gameid = :gameid order by main, id limit 1';
  $dscsql = 'select filename from images where gameid = :gameid order by main, id limit 1';
  $scrpdo = $db->prepare($scrsql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
  $dscpdo = $db->prepare($dscsql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

  $chars=array();
  if ( $rows > 0 ) {
    // Loop through and get relevant starting letters
    foreach ($res2 as $game ) {
      $a=strtoupper(substr($game['c1'],0,1));
      
      if (is_numeric($a)) {
         $a='#';
      }

      if (array_search($a,$chars) === False) {
         $chars[]=$a;
      }
    }
    if (array_key_exists('atoz',$state)) {
      $atoz=$state['atoz'];
    } else {
      $atoz="";
    }
    atoz_line($atoz,$chars);

    pager($limit,$rows,$page,$state);
    echo '    <div class="row" style="display:flex; flex-wrap: wrap;">'."\n";
    foreach ( $res as $game ) {
      $scrpdo->bindParam(':gameid',$game["id"], PDO::PARAM_INT);
      $dscpdo->bindParam(':gameid',$game["id"], PDO::PARAM_INT);
      if ($scrpdo->execute()) {
        $img=$scrpdo->fetch(PDO::FETCH_ASSOC);
        if (is_null($img["filename"])||!file_exists('gameimg/screenshots/'.$img["filename"])) {
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
     pager($limit,$rows,$page,$state);
  } else {
    echo '    <div class="row" style="display:flex; flex-wrap: wrap;">'."\n<h2>No games found!</h2>";
    echo "    </div>\n";
  }

  if ( defined('GD_DEBUG') && GD_DEBUG == True ) {
    echo "<pre>";
    echo "SQL:\n";
    echo $sql;
    echo "\n\nSQL2:\n";
    echo $sql2;
    echo "\n\nState:\n";
    print_r($state);
    echo "</pre>";
  }
  echo "   </div>\n";

}
?>
