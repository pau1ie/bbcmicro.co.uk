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
#  print_r($image);
  $url = Null;
  if ($image['customurl'] === NULL or $image['customurl'] === '') {
    if ($image['filename'] === NULL ) {
      $ssd = null;
    } else {
      $ssd = 'gameimg/discs/' . $image["filename"];
      if (file_exists($ssd)) {
        $url = $jsbeeb . $wsroot . '/' . $ssd;
      }
    }
  } else {
    if ($image['customurl']=='NONE') {
      $url=NULL;
    } else {
      $url = str_replace('%jsbeeb%',$jsbeeb,$image['customurl']);
      $url = str_replace('%wsroot%',$wsroot,$url);
    }
  }
#  echo "URL:".$url."\n";
  return $url;
}
?>
