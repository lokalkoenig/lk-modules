<?php

/**
 * 
 * 
 * @param type $account
 */

function vku_func_flush_deleted($account){
    
  $current = LK\current();

  if(!$current ->isModerator() AND $current -> getUid() != $account -> uid){
    drupal_goto("user/" . $account -> uid . "/vku");
  }
   
  //self::logNotice("User löscht alle Verkaufsunterlagen aus dem Papierkorb");
        
  // get all deleted
  $dbq = db_query("SELECT vku_id FROM lk_vku WHERE vku_status='deleted' AND uid='". $account -> uid ."'");
  foreach($dbq as $data){
    $vku = new \VKUCreator($data -> vku_id);
    if($vku -> is('deleted')){
      drupal_set_message("Die Verkaufsunterlage " . $vku ->get('vku_title') . " wurde gelöscht."); 
      $vku ->remove();  
    }
  }
   
  drupal_goto("user/" . $account -> uid . "/vku");
}

