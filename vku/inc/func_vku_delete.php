<?php

/**
 * Removes a VKU
 *
 * @path /user/$user/vku/$id/delete
 * @global \stdClass $user
 * @param int $id
 */
function _vku_current_delete($id){
  global $user;

  $vku = \LK\VKU\VKUManager::getVKU($id, true);
  if(!$vku){
    drupal_goto('user/' . $user -> uid . '/vku');
    drupal_exit();
  }

  // Check Status
  if(!$vku->isDeleteAble()) {
    drupal_set_message("Die Verkaufsunterlage kann nicht gelöscht werden.");
    drupal_goto($vku ->url());
  }

  $status = $vku -> getStatus();
  $author = $vku -> getAuthor();
  $pagemanager = new \LK\VKU\PageManager($vku);

  if($status === 'deleted'){
    $msg = $vku ->logVerlagEvent("Die Verkaufsunterlage wurde gelöscht.");
    $pagemanager->removeVKU();
    drupal_set_message($msg);
  }
  else {
    if($status === 'template'){
      $vku ->logEvent("Remove VKU", "Vorlage wurde gelöscht.");
      $pagemanager->removeVKU();
      drupal_goto('user/' . $author . '/vkusettings');
    }
          
    $vku -> setStatus('deleted');
    $vku ->logVerlagEvent("Verkaufsunterlage verworfen");
          
    drupal_set_message("Ihre Verkaufsunterlage wurde in den Papierkorb verschoben.");   
  }
      
  drupal_goto('user/' . $author . '/vku');
}
