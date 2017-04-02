<?php

function _vku_show_current_ajax(){
global $user;

  $vkuid = vku_get_active_id();
  if(!$vkuid){
      drupal_json_output(array('error' => 1));
      drupal_exit(); 
  }
    
  $vku = new VKUCreator($vkuid);
  $nodes = array();
  $data = $vku -> getKampagnen();
  foreach($data as $node){
    $nodes[$node] = node_load($node);  
  }

  $vku_count = vku_get_active_count($user);

  drupal_json_output(array('error' => 0, 'content' => theme('vku_content', array('account' => $user, 'vku_count' => $vku_count, 'nodes' => $nodes, 'vku' => $vku))));
  drupal_exit();
}
