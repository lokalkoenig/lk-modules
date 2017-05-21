<?php

/**
 * Callback for creating a VKU
 * 
 * @path /merkliste/%/convert
 * @param int $tid
 */
function lokalkoenig_merkliste_ajax_callback_vku($tid){
  
  $manager = new \LK\Merkliste\UserMerkliste();
  $merkliste = $manager->loadMerkliste($tid);
    
  if(!$merkliste){
    drupal_goto($manager ->getUrl());
  }
  
  $title = 'Ihr Angebot';
  $post_title = trim($_POST["title"]);
  if($post_title){
    $title = $post_title; 
  }
  
  $kampagnen = $merkliste ->getKampagnen();
  $can_nids = array();
  
  foreach ($kampagnen as $nid){
    $node = node_load($nid);
    $ka = new \LK\Kampagne\Kampagne($node);
    if(!$ka->canPurchase()){
      drupal_set_message('Die Kampagne "'. $node -> title .'" ist im Moment für Sie nicht verfügbar und wurde deswegen der Verkaufsunterlage nicht hinzugefügt.', 'error');  
    }
    
    $can_nids[] = $nid;
  }  
  
  if(!$can_nids){
     drupal_goto($manager ->getUrl());
  }
  $account = \LK\current();
  $vku = \LK\VKU\VKUManager::createEmptyVKU($account, ["vku_title" => $title, 'vku_company' => $merkliste ->getName(), 'vku_generic' => 0]);
  foreach($can_nids as $nid){
    $vku -> addKampagne($nid);  
  }
   
  drupal_set_message('Eine neue Verkaufsunterlage wurde erstellt.');

  if(vku_is_update_user()){
    drupal_goto($vku -> vku_url() . '/title');
  }

  drupal_goto($vku -> vku_url());
}
