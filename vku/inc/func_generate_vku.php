<?php


function _vku_generate_final_vku($vku){
  $manager = new \LK\VKU\PageManager();
  return $manager ->finalizeVKU($vku);
}

function _vku_ontheflygenerate($account, $vku_id){
global $user;
  
   $vku = new VKUCreator($vku_id);
   if(!$vku -> is('created')){
      die('Die VKU ist veraltet');
   }

   $vkustatus = $vku -> getStatus();
   $vkuauthor = $vku -> getAuthor();
  
  if($account -> uid != $vkuauthor AND !lk_is_moderator()){
    exit;
  }
  
  $result = _vku_generate_final_vku($vku);
  $vku ->setStatus('ready');

  $return = array();
  $return["error"] = 0;
  
  if(!$result){
    $return["error"] = 1;
    $return["msg"] = 'Bei der Generierung gab es einen Fehler.';
    $return["link"] = url('user/'. $vkuauthor .'/vku');
    $vku ->logEvent('warning', 'Problem mit der PDF Generierung');
     
    if(isset($_GET["ajax"])){
      drupal_json_output($return);
      drupal_exit();
    }
    
    else {
      drupal_set_message($return["msg"]);
      drupal_goto($return["link"]);
      drupal_exit();
    }  
  }
  
  $vku = new VKUCreator($vku_id);
  $vkuauthor = $vku -> getAuthor();  
  $filesize = $vku -> getValue("vku_ready_filesize");
  
  $return["downloadlink"] = url('user/' . $vkuauthor . "/vku/" . $vku_id . "/download");
  $return["filesize"] = format_size($filesize);
  
  $vku ->logEvent('pdf', 'PDF generiert ('. $return["filesize"] .')');
  
  if(isset($_GET["ajax"])){
    drupal_json_output($return);
    drupal_exit();
  }  
  else {
    drupal_set_message("Der Download wurde erfolgreich erstellt.");
    drupal_goto($vku -> getUrl());
    drupal_exit();
  }
}
