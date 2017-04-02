<?php

function vku_changeorder($vku_id){
  // only AJAX
  $vku = new VKUCreator($vku_id);
  if($vku -> is()){
    if(isset($_POST["page"])){
      $x = 0;
      foreach($_POST["page"] as $item){
        $vku -> saveItemOrder($item, $x);
        $x += 10;
      }
    }

    $vku -> update();
    $message = $vku ->logEvent("VKU Sort Pages", "Die Reihenfolge der Kampagnen wurde geändert.");

    drupal_json_output(array('error' => 0, 'message' => $message));
    drupal_exit();
  }

  // Standard pushback
  drupal_json_output(array('error' => 1, 'message' => 'Fehler unbekannt, keine VKU ('. $vku_id .')'));
  drupal_exit();  
}
