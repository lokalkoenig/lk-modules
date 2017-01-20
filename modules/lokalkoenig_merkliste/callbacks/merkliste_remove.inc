<?php

/**
 * Removes a Merkliste
 * 
 * @path /merkliste/%/remove
 * @param int $tid
 */
function lokalkoenig_merkliste_ajax_callback_delete($tid){
  
  $manager = new \LK\Merkliste\UserMerkliste();
  $merkliste = $manager->loadMerkliste($tid);
    
  if(!$merkliste){
    drupal_goto($manager ->getUrl());
  }
  
  $manager ->removeMerkliste($merkliste ->getId());
  drupal_set_message("Die Merkliste <b>". $merkliste ->getName() ."</b> wurde gelöscht.");
  drupal_goto($manager ->getUrl());
}

