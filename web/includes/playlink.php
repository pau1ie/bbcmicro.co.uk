<?php
function get_scrshot($file,$subdir) {
  $di='gameimg/screenshots/default.jpg';
  if ($subdir === NULL or $subdir === '') {
    $imgfile = 'gameimg/screenshots/' . $file;
  } else {
    $imgfile = 'gameimg/screenshots/' . $subdir . '/' . $file;
  }
  if ($file === NULL || $file === '') {
    $imgfile=$di;
  }
  if (!file_exists($imgfile)) {
    $imgfile=$di;
  }
  return $imgfile;
}

function get_playlink($image,$jsbeeb,$wsroot) {
  $jsbdisc=$jsbeeb . '?autoboot&disc=';
  $jsbtape=$jsbeeb . '?autochain&tape=';
  $url = Null;
  if ($image['customurl'] === NULL or $image['customurl'] === '') {
    $ssd=get_discloc($image['filename'],$image['subdir']);
    if (file_exists($ssd)) {
      $file_parts = pathinfo($ssd);
      if (strtolower($file_parts['extension']) == 'uef') {
        $url = $jsbtape . $wsroot . '/' . $ssd;
      } else {
        $url = $jsbdisc . $wsroot . '/' . $ssd;
      }
    }
  } else {
    if ($image['customurl']=='NONE') {
      $url=NULL;
    } else {
      $url = str_replace('%jsbeeb%',$jsbdisc,$image['customurl']);
      $url = str_replace('%wsroot%',$wsroot,$url);
    }
  }
  return $url;
}

function get_discloc($file,$subdir) {
  $di = Null;
  if ($subdir === NULL or $subdir === '') {
    $imgfile = 'gameimg/discs/' . $file;
  } else {
    $imgfile = 'gameimg/discs/' . $subdir . '/' . $file;
  }
  if ($file === NULL || $file === '') {
    $imgfile=$di;
  }
  if (!file_exists($imgfile)) {
    $imgfile=$di;
  }
  return $imgfile;
}
?>
