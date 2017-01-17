<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//require_once __DIR__ . "/func_vku2_callback.php";

function vku2_generate_form(VKUCreator $vku){
    
   drupal_add_js(drupal_get_path('module', 'vku') .'/js/vku2.js', 'file');
   drupal_add_css(drupal_get_path('module', 'vku') .'/css/vku2.css');
   drupal_add_css(drupal_get_path('module', 'vku') .'/css/vku2-generate.css');
    
   drupal_set_title("Verkaufsunterlage");
   lk_set_icon('tint');
   
   $manager = new \LK\VKU\PageManager();
   $uid = $vku ->getAuthor();
   $account = \LK\get_user($uid);
    
   $print = $manager ->getPossibilePages('print', $account);
   $online = $manager ->getPossibilePages('online', $account);
   $sonstiges = $manager ->getPossibilePages('sonstiges', $account);
   $pages = $manager->generatePageConfiguration($vku);
   
   $status = $vku ->getStatus();
   $generated = theme("vku2_items", array("items" => $pages, 'vku' => $vku));
   
   $title = $vku -> get("vku_title");
   if(!$title){
       $title = 'NEU';
   }
   
   lk_set_subtitle('<span class="vku-title">' . $title . '</span><span class="pull-right label label-primary label-vku-editor">VKU Editor 2.0</span>');
   $array = array('items' => $generated, 'print' => $print, 'online' => $online, 'sonstiges' => $sonstiges, 'vku' => $vku, 'kampagnen' => array());
   
   if($status == 'template'){
    drupal_set_title("Vorlage");
    $array["dokumente"] = theme("vku2_documents", $array);
    return theme('vku2_template', $array);    
   }
   
   $array["kampagnen"] = $manager ->getPossibilePages('kampagnen', $account); 
   
   $array["templates"] = vkuconnection_get_user_templates($uid);
   $array["ausgaben"] = vku2_get_ausgaben_hinweis($vku, $uid);
   
   $array["dokumente"] = theme("vku2_documents", $array);
   
   return theme('vku2', $array);    
}

function vku2_get_ausgaben_hinweis(VKUCreator $vku, $user_uid){
    
   // Add Ausgaben-Support für Ausgaben
     // Show current Ausgaben, before VKU-Sumit
     $account = \LK\get_user($user_uid);
     $ausgaben =  $account -> getCurrentAusgaben();
        
     $ausgaben_formatted = array();
     foreach($ausgaben as $ausgabe){
        $object = \LK\get_ausgabe($ausgabe);     
         if($object){
             $ausgaben_formatted[] = $object -> getTitleFormatted(); 
         }
     }
     
     
     $link = '';
     // Can adjust theese Settings
     if($account -> isTelefonmitarbeiter()){
         $link = '<a class="pull-right btn btn-sm btn-primary" href="'. url('user/' . $account -> uid . '/setplz', array("query" => array('destination' => 'vku/' . $vku -> getId() .'/finalize'))) .'"><span class="glyphicon glyphicon-cog"></span> Ausgaben anpassen</a>';
     }
    
    $days = 0; 
    $verlag = $account -> getVerlag();
    if($verlag){
        $verlag_user = \LK\get_user($verlag);
        $days = $verlag_user -> getVerlagSetting('sperrung_vku_pdf', 0);
    }
     
    if($ausgaben_formatted AND $days){
          return '<div class="row"><div class="col-xs-6">'
            . '<p><span class="glyphicon glyphicon-exclamation-sign"></span> Bitte beachten Sie, dass die Kampagnen für '. $days .' Tage für '
            . 'Ihre ausgewählten Ausgaben vorgemerkt werden:</p></div><div class="col-xs-6">'
            . $link
            . '<ul class="list-inline"><li>' . implode("</li><li>", $ausgaben_formatted) . '</li></ul></div></div>';
        
   }   
    
return false;    
}


////////////////////////////////////////////////////


function _vku2_generate_add_new_category_page($vku_id, $type, $page){
   $category_id = db_insert('lk_vku_data_categories')->fields(array('vku_id' => $vku_id, 'category' => $type, 'sort_delta' => $page["data_delta"]))->execute();
   
   _vku2_add_page_to_category($page, $category_id);
}

function _vku2_add_page_to_category($page, $category_id){
   db_query("UPDATE lk_vku_data SET data_category='". $category_id ."' WHERE id='". $page["id"]  ."'");    
}
 
/**
 * 
 * @param VKUCreator $vku
 * @return Array
 */
function vku2_generate_category_pages(VKUCreator $vku){
    
    $pagem = new \LK\VKU\PageManager();
    return $pagem ->generatePageConfiguration($vku);
}



/**
 * Gets back yes or no, if Node can be licenced
 * 
 * @param Int $nid
 * @param Int $uid
 * @return boolean
 */
function vku2_node_can_add($nid, $uid){
    
    $node = node_load($nid);
    $access = na_check_user_has_access($uid, $node -> nid);
    
    if(!$node -> status OR !$access["access"]){
        return false;
    }
    
return true;    
}


/**
 * Returns the Basic Configuration of the
 * new VKU2 Categories
 * 
 * @return Array
 */
function vku2_generate_vku_categories(){
   $pagem = new \LK\VKU\PageManager();
   $pagem ->getDefaultCategories();
}


/**
 * Copy a Vorlage to a existing VKU
 * 
 * 
 * @param VKUCreator $vku
 * @param Integer $vorlage_vku_id
 * @return VKUCreator
 */
function vku2_generate_takeover_vorlage(VKUCreator $vku, $vorlage_vku_id){
  return \LK\VKU\Vorlage::takeOver($vku, $vorlage_vku_id);  
}    