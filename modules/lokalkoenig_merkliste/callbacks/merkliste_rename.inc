<?php

/**
 * Callback for renaming a ML
 * 
 * @path /merkliste/%/rename
 * @param int $tid
 */
function lokalkoenig_merkliste_ajax_callback_rename($tid){
  
  $newname = trim($_POST["name"]);
  $manager = new \LK\Merkliste\UserMerkliste();
  $merkliste = $manager->loadMerkliste($tid);
    
  if(!$merkliste){
    drupal_set_message("aaaa"); 
    drupal_goto($manager ->getUrl());
  }
  
  if(!$newname){
    drupal_set_message("Bitte vergeben Sie einen eindeutigen Merklisten-Titel."); 
    drupal_goto($merkliste ->getUrl());
  }      
  
  $new_merkliste =  $manager->renameMerkliste($merkliste, $newname);
  
  if(!$new_merkliste){
    drupal_set_message('Es ist ein Fehler passiert.');
    drupal_goto($manager ->getUrl());
  }
  
  drupal_set_message("Die Merkliste wurde in <b>". $new_merkliste ->getName() ."</b> umbenannt.");
  drupal_goto($new_merkliste ->getUrl());
}
