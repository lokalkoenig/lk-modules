<?php

function vku_get_top_menu(){
global $user;
  
  drupal_add_js(drupal_get_path('module', 'vku') .'/js/vku2-handling.js', 'file');
  drupal_add_css(drupal_get_path('module', 'vku') .'/css/vku2.css');
 
 $array = array(); 
 $dbq = db_query("SELECT vku_id FROM lk_vku WHERE vku_status='active' AND uid='".$user -> uid  ."' ORDER BY vku_changed DESC");
 foreach($dbq as $all){
   $array[]  = new VKUCreator($all -> vku_id);
 }
 
 return theme("vku_menu", array('vkus' => $array));   
}
