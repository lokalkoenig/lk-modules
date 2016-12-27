<?php

function show_verlags_stats($verlag){
  drupal_set_title("Protokoll");
  $content = views_embed_view('lk_log', 'attachment_1', $verlag -> uid);

  return $content;

}


?>