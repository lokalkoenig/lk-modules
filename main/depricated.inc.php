<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/** 
 *   DEPRICATED
 *   Erstellt eine Regel für die PLZ des Users   
 *
 **/
function na_create_node_rule($nid, $uid, $until_date){
  
  $account = \LK\get_user($uid);
  $ausgaben = $account -> getCurrentAusgaben();
  
  $manager = new \LK\Kampagne\SperrenManager();
  $result = $manager ->createSperre($nid, $uid, $ausgaben, $until_date);  
  
  if($result){
      return $result ->getId();
  }  
  
  return false;    
}


/**
 * 
 * @deprecated since version number
 * @param int $entity_id
 * @return boolean
 */
function lokalkoenig_nodeaccess_delete_rule($entity_id){
    
    $manager = new \LK\Kampagne\SperrenManager();

    return $manager ->removeSperre($entity_id);
}






 // checken ob eine Lizenz vorliegt
function vku_user_has_lizenz_node($nid, $account){
    $dbq = db_query("SELECT l.* FROM lk_vku vku, lk_vku_lizenzen l 
          WHERE 
            l.nid='". $nid ."' AND 
            l.vku_id=vku.vku_id AND 
            vku.vku_status='purchased' AND 
            vku.uid='". $account -> uid  ."'");
            
  return $dbq -> fetchObject();
}


/** Ausgabe changed PLZ */
function node_access_ausgabe_changed_plz($aid){
    $ausgabe = \LK\get_ausgabe($aid);
    $manager = new \LK\Kampagne\SperrenManager();
    $manager ->updateAusgabe($ausgabe);
}



function _lokalkoenig_node_access_info_count($node){
    \LK\Kampagne\AccessInfo::hasAccess($node);
}


function vku_get_use_count($nid, $account){
    return \LK\Kampagne\AccessInfo::getAccessCount($nid, $account);    
}

function vku_get_use_details($nid, $account, $exclude = false){  
    return \LK\Kampagne\AccessInfo::getUserDetails($nid, $account);   
}




function vku_get_use_count_days($account){

return 10;   
}



function na_check_user_has_access($uid, $nid){
global $user;
  
 $account = \LK\get_user($uid);
 // If no Account
 if(!$account) {
    return false;
 }
 
 // No Node
 $node = node_load($nid);
 if(!$node){
     return false;
 }
  
 // Status of the Node is Offline
 if($node -> lkstatus != 'published' OR $node -> status != 1) {
      return array('access' => false, "reason" => "Kampagene nicht mehr Online.");
 }
 
 // No Ausgaben
 $ausgaben = $account -> getCurrentAusgaben();
 if(!$ausgaben){
   return array('access' => true);  
 }
 
 $dbq = db_query("select until as date_until from na_node_access_ausgaben_time WHERE nid='". $nid ."' AND aid IN (". implode(",", $ausgaben) .") ORDER BY until DESC LIMIT 1"); 
 $result = $dbq -> fetchObject();
 
 if(!$result) return array('access' => true);
 else {
     return array('access' => false, 
                  'time' => $result -> date_until,
                  "reason" => "Die Kampagne ist ab dem ". date("d.m.Y", $result -> date_until) ." wieder verfügbar.");
  }
}


/** Update Database on Entity Update */
function lokalkoenig_nodeaccess_entity_update($entity, $type){
    
   // Ausgabe - PLZ Changed 
   if($type == 'ausgabe'){
     if($entity -> type == 'ausgabe'){
         node_access_ausgabe_changed_plz($entity -> id);
     }
   }
}

/** INSERT Database on Entity Update  - AUsgabe*/
function lokalkoenig_nodeaccess_entity_insert($entity, $type){
  if($type == 'ausgabe'){
    if($entity -> type == 'ausgabe'){
      node_access_ausgabe_changed_plz($entity -> id);
    }
  }
}






function get_ausgaben_access_nid($nid,  $account){
  
  $ma = \LK\get_user($account);
  if(!$ma){
      return array();
  }
  
  $verlag = $ma -> getVerlag();
  if(!$verlag){
      return array();
  }
  
  $inverlag = array();
  $dbq = db_query("SELECT ausgaben_id FROM na_node_access_ausgaben WHERE verlag_uid='". $verlag ."' AND nid='". $nid ."'");
  foreach($dbq as $all){
      $ausgabe = \LK\get_ausgabe($all -> ausgaben_id);
      if($ausgabe){
          $inverlag[] = $ausgabe -> getShortTitle(); 
      }
  }
  
  $outverlag = array();
  $dbq = db_query("SELECT * FROM na_node_access_ausgaben WHERE verlag_uid != '". $verlag ."' AND nid='". $nid ."'");
  foreach($dbq as $all){
       $outverlag[] = $all -> plz_gebiet_aggregated;
  }  
   
return array('count' => (count($inverlag) + count($outverlag)), 'in' => $inverlag, "out" => $outverlag);
}



////////////////// DEPRICATED


function lk_process_gif_ani($file){
  $gif = new \LK\Kampagne\GIFExtractor();
  return $gif -> toArray($file);
}



/**
 * status: new|progress|submit|deny
 */   
function _lk_set_kampagnen_status($nid, $status){
  
   $node = node_load($nid);

   if($status == 'published'){
     $new_status = 1;
     module_invoke_all('change_kampagnen_status_published', $node);
   } 
   else {
     $new_status = 0;
     module_invoke_all('change_kampagnen_status_unpublished', $node);
   }
  
   $node -> status = $new_status;
   $node -> field_kamp_status["und"][0]["value"] = $status;   
   node_save($node);
}


function _lk_get_kampa_sid_generate($node){
    if(!isset($node->field_kamp_preisnivau['und'][0]['tid'])){
        return '';
    }
    $term = taxonomy_term_load($node->field_kamp_preisnivau['und'][0]['tid']);
    return $term->field_paket_kurz['und'][0]['value'] . '-' . $node -> nid;
}


function _lk_get_kampa_sid($node){
  if(!isset($node->field_sid['und'][0]['value'])){
     return _lk_get_kampa_sid_generate($node);
  }
   
  return $node->field_sid['und'][0]['value'];
}
