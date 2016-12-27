<?php

/* 
 * @added 2015-11-02
 */


/**
 * @route user/UID/vkusettings
 * @param type $account
 */
function vkuconn_settings($account){
    lk_set("Einstellungen zu Verkaufsunterlagen",NULL, 'list');
   
    
    if(isset($_GET["delete"])){
        $templates = vkuconnection_get_user_templates();
        foreach($templates as $template){
            if($template -> vku_id == $_GET["delete"]){
                $vku = new VKUCreator($template -> vku_id);
                if($vku -> is('template')):
                    $msg = $vku ->logEvent('template', "Vorlage wurde gelöscht");
                    $vku ->remove();
                    drupal_set_message($msg);
                    drupal_goto('user/' . $account -> uid . "/vkusettings");
                    endif;
            }
        }
    }
    
    if(isset($_GET["make_default"])):
       // reset default star
       _vkuconn_settings_make_default($account, $_GET["make_default"]);
    
       $templates = vkuconnection_get_user_templates();
       foreach($templates as $template){
            if($template -> vku_id == $_GET["make_default"]){
                $vku = new VKUCreator($template -> vku_id);
                if($vku -> is('template')):
                    
                    $status = $vku -> get("vku_template_default");
                    if($status == 1){
                      $msg = $vku ->logEvent('template', "Die VKU ist nicht mehr Ihre Standard-Verkaufsunterlage");  
                      $vku -> set("vku_template_default", 0); 
                    }
                    else {
                      db_query("UPDATE lk_vku SET vku_template_default='0' WHERE uid='". $account -> uid ."' AND vku_status='template' AND vku_template_default='1'"); 
                      $msg = $vku ->logEvent('template', "Die VKU ist nun Ihre Standard-Verkaufsunterlage");  
                      $vku -> set("vku_template_default", 1);    
                        
                    }
                
                    drupal_set_message($msg);
                    drupal_goto('user/' . $account -> uid . "/vkusettings");
                    endif;
                }    
         }
        
    
    endif;
   
    
    
    // This will be a new Task
    
    // - Overview personal Sample-VKU's
    
    $array = array();
    
    $templates = vkuconnection_get_user_templates();
    foreach($templates as $template){
        
        if(vku_is_update_user()){
            $template -> vku_template_default = 0;
            
            if(empty($template -> vku_title)){
                continue;
            }
        }
        
      $array[] = array(
         'title' => $template -> vku_template_title,
         'link_delete' => url('user/' . $account -> uid . "/vkusettings", array("query" => array("delete" => $template -> vku_id))),
         'link_star' => url('user/' . $account -> uid . "/vkusettings", array("query" => array("make_default" => $template -> vku_id))),
         'link_edit' => url('vku/' . $template -> vku_id),
         'default' => $template -> vku_template_default,
         'changed' => $template -> vku_changed
      );   
     }
     
     return theme('vkuconnection_settings', array('items' => $array));
  }
  
  
  function _vkuconn_settings_make_default($account, $id){
      
      
      
  }
