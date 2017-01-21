<?php

function _vku_renew_vku($account, $vku_id){
global $user;
  
  $vku = new VKUCreator($vku_id);
  if(!$vku -> is()){
       drupal_set_message("Die Verkaufsunterlage ist nicht mehr valide.");
       drupal_goto("user/" . $user -> uid. '/vku');
       drupal_exit();
   }

   $vkustatus = $vku -> getStatus();
   $vkuauthor = $vku -> getAuthor();
   
   if(vku_is_update_user()){
       
       $settings = array(
           'uid' => $user -> uid,
           'vku_title' => $vku -> get('vku_title', false),
           'vku_company' => $vku -> get('vku_company', false),
           'vku_untertitel' => $vku -> get('vku_untertitel', false)
       );
       
       $vku_new = new VKUCreator('new', $settings);
       require_once __DIR__ ."/func_vku2_generate.php";
       
       $vku_new_generated = \LK\VKU\Vorlage::takeOver($vku_new, $vku -> getId());
       $vku_new_generated -> setStatus('new');
       
       $vku_new_id = $vku_new_generated -> getId(); 
   }
   else {
      $vku_new_id = $vku -> cloneVku();
      
      $vku_temp = new VKUCreator($vku_new_id);
      $vku_temp -> isCreated();
   }    
   
   // Wenn Eigentümer dann Original als DELETED einstampfen 
   if($vkuauthor == $user -> uid){
       if(in_array($vkustatus, array("ready", "downloaded"))){
          $vku -> setStatus('deleted');
       }

       if(in_array($vkustatus, array("deleted"))){
           $vku -> remove();
       }     
   } 
   
   
  $new_vku = new VKUCreator($vku_new_id);
  $new_vku -> update();
  
  if($vkustatus == 'template'){
    $msg = $new_vku ->logEvent("template", "Eine neue Verkaufsunterlage wurde erstellt. Sie können nun Kampagnen hinzufügen.");
    $redirect = $new_vku -> url();
  }
  else {
    $msg = $new_vku -> logEvent("renew", "Die Verkaufsunterlage wurde erneuert.");
    $redirect = $new_vku -> url(); 
    
    if(vku_is_update_user()){
         drupal_set_message($msg);
         drupal_goto($redirect . "/title");    
         drupal_exit();
    }    
    
  }
  
  drupal_set_message($msg);
  drupal_goto($redirect);    
  drupal_exit();
}

?>