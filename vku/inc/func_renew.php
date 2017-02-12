<?php

/**
 *
 *
 * @param /stdClass $account
 * @param int $vku_id
 */
function _vku_renew_vku($account, $vku_id){

  $vku = \LK\VKU\VKUManager::getVKU($vku_id, TRUE);
  $current = \LK\current();

  if(!$vku){
    drupal_set_message("Die Verkaufsunterlage ist nicht mehr valide.");
    drupal_goto("user/" . $current ->getUid(). '/vku');
    drupal_exit();
  }

  // Remove PLZ-Sperren from
  $vku ->removePLZSperren();
  
  $vkustatus = $vku -> getStatus();
  $vkuauthor = $vku -> getAuthor();

  if(vku_is_update_user()){
    $page_manager = new \LK\VKU\Vorlage\Vorlage();

    // Create a new VKU
    $settings = [
      'uid' => $current->getUid(),
      'vku_title' => $vku -> get('vku_title', false),
      'vku_company' => $vku -> get('vku_company', false),
      'vku_untertitel' => $vku -> get('vku_untertitel', false),
    ];

    $new_vku = \LK\VKU\VKUManager::createEmptyVKU($current, $settings);
    $page_manager ->cloneVKUPages($vku, $new_vku);
  }
  else {
    // set Status to active
    // @VKU 1.0
    $vku_new_id = $vku -> cloneVku();
    $new_vku = new VKUCreator($vku_new_id);
    $new_vku -> isCreated();
    $new_vku ->setStatus('active');
  }

  // Wenn Eigentümer dann Original als DELETED einstampfen
  if($vkuauthor === $current ->getUid()){
    if(in_array($vkustatus, array("ready", "downloaded"))){
      $vku -> setStatus('deleted');
    }
  }

  if($vkustatus === 'template'){
    $msg = $new_vku -> logVerlagEvent("Eine neue Verkaufsunterlage wurde von der Vorlage erstellt.");
  }
  else {
    $msg = $new_vku -> logEvent("renew", "Die Verkaufsunterlage wurde erneuert.");
  }

  $redirect = $new_vku -> url();
  drupal_set_message($msg);

  if(vku_is_update_user()){
    drupal_goto($redirect . "/title");
    drupal_exit();
  }

  drupal_goto($redirect);
  drupal_exit();
}
