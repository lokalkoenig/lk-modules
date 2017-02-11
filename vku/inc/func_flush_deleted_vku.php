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
   
  $page_manager = new \LK\VKU\PageManager();
        
  // get all deleted
  $dbq = db_query("SELECT vku_id FROM lk_vku WHERE vku_status='deleted' AND uid='". $account -> uid ."'");
  foreach($dbq as $data){
    $vku = \LK\VKU\VKUManager::getVKU($data -> vku_id, true);
    if($vku){
      $page_manager ->removeVKU($vku);
      drupal_set_message("Die Verkaufsunterlage " . $vku ->get('vku_title') . " wurde gelöscht.");
    }
  }
   
  drupal_goto("user/" . $account -> uid . "/vku");
}

