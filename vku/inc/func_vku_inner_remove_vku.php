<?php

/**
 * Removes a VKU
 * 
 * @path /vku/$vku_id/id
 * @param int $vku_id
 */
function _vku_delete_data($vku_id){

  $vku = \LK\VKU\VKUManager::getVKU($vku_id, true);

  if(!$vku || !$vku -> isActiveStatus()){
    drupal_goto("vku");
  }

  $msg = $vku ->logEvent("Remove VKU", "Die Verkaufsunterlage (". $vku_id . ") wurde verworfen.");
  drupal_set_message($msg);
  $vku ->setStatus('deleted');
  
  drupal_goto($vku->userUrl());
}
