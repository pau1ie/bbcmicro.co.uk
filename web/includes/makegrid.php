<?php
require 'playlink.php';

function get_reltypes() {
  global $db;

  $sql = "select distinct id, name, selected from reltype order by rel_order";
  $sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
  if ($sth->execute()) {
    $res = $sth->fetchAll();
  } else {
    echo "Error:";
    echo "\n";
    $sth->debugDumpParams ();
    $res=array();
  }
  return $res;
}


function reltypes($state) {
?>
     <h4>Browse release types:</h4>
      <div id="reltypes">
<?php
   $reltyps=get_reltypes();
   foreach ( $reltyps as $reltyp ) {
      $checked='';
      $active=' btn-default';
      if (!array_key_exists('rtype',$state) || count($state['rtype'])==0){
         if ($reltyp['selected'] == 'Y') {
            $checked='  <input type="hidden" name="rt_' . $reltyp['id'] .'">';
            $active=' btn-primary';
         }
      } else {
         if (array_key_exists('rtype',$state) && array_search($reltyp['id'],$state['rtype'])===False) {
            ;
         }else{
            $checked='  <input type="hidden" name="rt_' . $reltyp['id'] .'">';
            $active=' btn-primary';
         }
      }
?>

<div class="btn-group" style="margin-bottom:5px;">
  <button type="submit" name="rs_<?php echo $reltyp['id']; ?>" title="Toggle <?php echo $reltyp['name'] ?>" class="btn<?php echo $active; ?>"><?php echo $reltyp['name'] ?></button>
  <button type="submit" name="ro_<?php echo $reltyp['id']; ?>" title="Select only <?php echo $reltyp['name'] ?>" class="btn<?php echo $active; ?>">&#9675;</button></div>
<?php echo $checked; 
   }
   echo "      </div>";
}

function atoz_line($current='',$chars,$margin) {
  global $state;

  echo "<div>";
  echo "<ul style=\"margin-$margin:0;\" class=\"pagination\">";
  foreach ($chars as $char) {
    $active=($current==$char)?' class="active"':'';
    echo "<li$active><a href='?" . url_state($state,'atoz', $char)."'>$char</a></li>";
  }
  echo "</ul>";
  echo "</div>";
}

function gameitem( $id, $ta, $name, $image, $img, $publisher, $year, $pubid) {
   global $sid;

   $jsbeeb=JB_LOC;
   $root=WS_ROOT;

   $split=explode('(',$name);
   $title=trim($split[0]);
   if (strlen($ta)>0){
     $title=$ta.' '.$title;
   }
   
   $ssd = get_discloc($img["filename"],$img['subdir']);
?>
     <div class="col-sm-6 col-md-4 col-lg-3 thumb1">
      <div class="thumbnail text-center">
       <a href="game.php?id=<?php echo $id; ?>"><img src="<?php echo $image; ?>" alt="<?php echo $image; ?>" class="pic"></a>
       <div class="row-title"><span class="row-title"><a href="game.php?id=<?php echo $id; ?>"><?php echo $title ?></a></span></div>
       <div class="row-pub"><?php echo $publisher ?></div>
       <div class="row-dt"><a href="?search=<?php echo urlencode($year) ?>&on_Y=on"><?php echo $year; ?></a></div>
<?php
  $playlink=get_playlink($img,$jsbeeb,$root);
  if ($ssd != null && file_exists($ssd)) { ?>
       <p><a href="<?php echo $ssd ?>" type="button" onmousedown="log(<?php echo $id; ?>);" class="btn btn-default">Download</a><?php
  }
  if (($img['probs'] != 'N' and $img['probs'] != 'P') and $playlink != null) { ?>
          <a id="plybtn" href="<?php echo $playlink ?>" type="button" onmousedown="log(<?php echo $id; ?>);" class="btn btn-default">Play</a></p>
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
//    } elseif ( $key == 'sort' ) {
//      $s2['sort']=$value;
    } else {
      $s2[$key]=$value;
    }
  }
  $s2[$ko]=$vo;
  return json_encode($s2,JSON_HEX_QUOT);
}

function url_state($state, $k, $v) {
  unset($state['page']);
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
//    }  elseif ( $key == 'sort' ) {
//      $url=$url.'&'.$key.$value.'=';
    } else {
      $url=$url.'&'.$key.'='.urlencode ( $value );
    }
  }
  return substr($url,1);  //Skip first &
}

function pager($limit, $rows, $page, $state) {
  global $publisher, $year;
  $pages = ceil($rows/$limit);
  $pl='';

  $pl.= '    <ul class="pagination">';
  if ( $page != 1 ) {
      $pl.= '     <li><a onclick=\'$.get("getgrid.php", '. json_state($state,'page', ($page - 1)).', function(data){ $("#maingrid").html(data); window.scrollTo(0,0); }); return false;\' href="?'. url_state($state,'page', ($page - 1)). '">&laquo;</a></li>' . "\n";
  }else{
     $pl.= '     <li class="disabled"><span>&laquo;</span></li> '. "\n";
  }
  for ( $i=1; $i <= $pages; $i++ ) {
    if ( ($i % 5 == 0 ) || (($i > ($page - 4)) && ( $i < ($page + 4))) || ( $i == 1) || ( $i == $pages) ) {
      if ($i != $page ) {
        $pl.= '     <li><a onclick=\'$.get("getgrid.php", '.json_state($state,'page', $i).', function(data){ $("#maingrid").html(data); }); window.scrollTo(0,0); return false;\' href="?'.url_state($state,'page', $i).'">' . $i . '</a></li>' . "\n";
      } else {
        $pl.= '     <li class="active"><a onclick=\'$.get("getgrid.php", '. json_state($state,'page', $i).', function(data){ $("#maingrid").html(data); }); window.scrollTo(0,0); return false;\' href="?'.url_state($state,'page', $i).'">' . $i . '</a></li> '. "\n";
      }
    }
  }
  if ( $page < $pages ) {
      $pl.= '     <li><a onclick=\'$.get("getgrid.php", '. json_state($state,'page', ($page + 1)).', function(data){ $("#maingrid").html(data); }); window.scrollTo(0,0); return false;\' href="?'. url_state($state,'page', ($page + 1)). '">&raquo;</a></li>' . "\n";
  }else{
     $pl.= '     <li class="disabled"><span>&raquo;</span></li> '. "\n";
  }
  $pl.= "    </ul>\n";
  return $pl;
}

function prepare_search($string) {
  $string=preg_replace('/^(A|An|The) /i','',$string);
  $string=preg_replace('/,? (A|An|The)$/i','',$string);
  $string="%".str_replace(' ','%',$string)."%";
  return $string;
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
      $sls[] = "g.title like :search\n";
    }
    if ( $all || !(array_search('P',$state['only'])===False )) {
      $sls[] = "g.id in (select gameid\n" .
               "   from games_publishers gp, publishers p\n" .
               "   where p.id = gp.pubid and p.name like :search)\n";
    }
    if ( $all || !(array_search('A',$state['only'])===False )) {
      $sls[] = "g.id in (select games_id\n   from games_authors ga, authors a\n" .
               "   where a.id = ga.authors_id and (a.name like :search or a.alias like :search))\n";
    }
    if ( $all || !(array_search('Y',$state['only'])===False )) {
      $sls[] = "g.year like :search\n";
    }
    if ( $all || !(array_search('Z',$state['only'])===False )) {
      $sls[] = "g.series like :search\n";
    }
    if ( $all || !(array_search('C',$state['only'])===False )) {
      $sls[] = "g.compilation like :search\n";
    }
    if ( $all || !(array_search('G',$state['only'])===False )) {
      $sls[] = "g.genre in (select id from genres where name like :search)\n";
    }
    if ( $all || !(array_search('S',$state['only'])===False )) {
      $sls[] = "g.id in (select gameid from game_genre m, genres g where g.id = m.genreid and g.name like :search)\n";
    }
  }

  if (count($sls)>0) {
    $wc[] = '(' . implode ('  OR ',$sls) . ')';
  }

//  if (array_key_exists ('pubid', $state)) {
//    $wc[] = "id in (select gameid from games_publishers gp where gp.pubid = :pubid)\n";
//  }

//  if (array_key_exists ('year', $state)) {
//    $wc[] = "year = :year\n";
//  }

  $doing_atoz_numbers=false;
  if (array_key_exists('atoz',$state)) {
    if ($state['atoz']=='#') {
      $doing_atoz_numbers=true;
      $atoz="^[0-9]";
      $atoz2=".*";
      $wc[]="title REGEXP :atoz\n";
    } else {
      $atoz=substr($state['atoz'],0,1)."%";
      $atoz2="%";
      $wc[]="title like :atoz\n";
    }
  }

  if (array_key_exists('rtype',$state)) {
    $wc[]="FIND_IN_SET(reltype,:array)\n";
  } else {
    $wc[]="reltype in (select id from reltype where selected = 'Y')\n";
  }

  if (array_key_exists('page',$state)) {
    $page=$state['page'];
  } else {
    $page=1;
  }

//  $wc[]='parent is null';
  if (isset($state['sort'])) {
    $srt=$state['sort'];
  } else {
    $srt='';
  }

  switch ($srt) {
    case "u":
      $ob = "order by g.imgupdated desc";
      break;
    case "a":
      $ob = "order by g.title";
      break;
    case "p":
      $ob = "order by dl desc, gp desc";
      break;
    case "b":
    default:
      $ob = "order by g.year desc, g.id desc";
  }
  $ym=date("Ym",time()-90*24*60*60);
  $offset = $limit * ($page -1);
  $sql ='select SQL_CALC_FOUND_ROWS g.*, sum(d.downloads) as dl, sum(d.gamepages) as gp from games g'."\n";
  $sql.=' left join game_downloads d on g.id = d.id and d.year > ' . $ym . ' where ' . implode(" AND ",$wc) . ' group by g.id '. $ob . ' LIMIT :limit OFFSET :offset';
  $sql2 = 'select distinct upper(substring(title,1,1)) AS c1 from games g WHERE ' . implode(' AND ',$wc) . " order by c1"; 

  $sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
  $sth2 = $db->prepare($sql2,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

  if (array_key_exists('search',$state)) {
    $search=prepare_search($state['search']);
    $sth->bindParam(':search', $search, PDO::PARAM_STR);
    $sth2->bindParam(':search', $search, PDO::PARAM_STR);
  }
  if (array_key_exists('atoz',$state)) {
    $sth->bindParam(':atoz',$atoz, PDO::PARAM_STR);
    $sth2->bindParam(':atoz',$atoz2, PDO::PARAM_STR);
  }
  if (array_key_exists('rtype',$state)) {
    $t=implode(',',$state['rtype']);
    $sth->bindParam(':array',$t);
    $sth2->bindParam(':array',$t);
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

  $scrsql = 'select filename, subdir from screenshots where gameid = :gameid order by main, id limit 1';
  $dscsql = 'select filename, subdir, customurl, probs from images where gameid = :gameid order by main, id limit 1';
  $pubsql = 'select id,name from publishers where id in (select pubid from games_publishers where gameid = :gameid)';
  $scrpdo = $db->prepare($scrsql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
  $dscpdo = $db->prepare($dscsql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
  $pubpdo = $db->prepare($pubsql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

  $chars=array();

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

  reltypes($state);

  atoz_line($atoz,$chars,'bottom');

  $pl=pager($limit,$rows,$page,$state);
  echo $pl;
  echo '    <div class="row" style="display:flex; flex-wrap: wrap;">'."\n";
  if ( $rows > 0 ) {
    foreach ( $res as $game ) {
      $scrpdo->bindParam(':gameid',$game["id"], PDO::PARAM_INT);
      $dscpdo->bindParam(':gameid',$game["id"], PDO::PARAM_INT);
      $pubpdo->bindParam(':gameid',$game["id"], PDO::PARAM_INT);
      if ($scrpdo->execute()) {
        $img=$scrpdo->fetch(PDO::FETCH_ASSOC);
        $shot = get_scrshot($img['filename'],$img['subdir']);
      } else {
        echo "Error:";
        $sim->debugDumpParams ();
      }
      if ($dscpdo->execute()) {
        $dnl=$dscpdo->fetch(PDO::FETCH_ASSOC);
      } else {
        echo "Error:";
        $sim->debugDumpParams ();
      }
      $pubs='';
      if ($pubpdo->execute()) {
        while($pub=$pubpdo->fetch(PDO::FETCH_ASSOC)) {
          $t=preg_split('/[,(]/',$pub['name']);
          $u=htmlspecialchars(trim($t[0]));
          $pubs=$pubs.'<a href="?search='.urlencode($pub['name']).'&on_P=on">'.$u.'</a>, ';
        }
      } else {
        echo "Error:";
        $sim->debugDumpParams ();
      }
      $pubs=trim($pubs,', ');

      gameitem($game["id"],htmlspecialchars($game["title_article"]),htmlspecialchars($game["title"]), $shot, $dnl ,$pubs,$game["year"],$pub["id"]);
    }
  } else {
    echo '    <div class="row" style="display:flex; flex-wrap: wrap;">'."\n<h2>No games found!</h2>";
    echo "    </div>\n";
  }
  echo "    </div>\n";
  echo $pl;
  atoz_line($atoz,$chars,'top');

  if ( defined('GD_DEBUG') && GD_DEBUG == True ) {
    echo "<pre>";
    echo "SQL:\n";
    echo $sql;
    echo "\n\nSQL2:\n";
    echo $sql2;
    echo "\n\nscrsql:\n";
    echo $scrsql;
    echo "\n\ndscsql:\n";
    echo $dscsql;
    echo "\n\npubsql:\n";
    echo $pubsql;
    echo "\n\nState:\n";
    print_r($state);
    echo "</pre>";
  }
  echo "   </div>\n";

}
?>
