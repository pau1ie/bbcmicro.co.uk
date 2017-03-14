<?php
function get_playlink($image,$jsbeeb,$wsroot) {
#  print_r($image);
  $url = Null;
  if ($image['customurl'] === NULL ) {
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
