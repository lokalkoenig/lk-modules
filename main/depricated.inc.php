<?php
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




function vku_get_use_count($nid, $account){
    return \LK\Kampagne\AccessInfo::getAccessCount($nid, $account);    
}

function vku_get_use_details($nid, $account, $exclude = false){  
    return \LK\Kampagne\AccessInfo::getUserDetails($nid, $account);   
}




function vku_get_use_count_days($account){

return 10;   
}



/**
 * @deprecated
 */
function na_check_user_has_access($uid, $nid){
  return \LK\Kampagne\AccessInfo::userHasAccessToKampagne($uid, $nid);
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

/** Lizenz-Zeit */
function lk_get_lizenz_time($account){
    
    $obj = \LK\get_user($account);
    $verlag = $obj ->getVerlagObject();
    if(!$verlag) {
        return 0;
    }
      
    $days = 365;
    $test = $verlag -> getVerlagSetting('sperrung_vku');
    if($test){
       return $test; 
    }
          
return $days; 
}



/** Depricated */
function _lk_username($account){
  
    $obj = \Lk\get_user($account);
    if($obj){
        return (string)$account;
    }
    
return '';
}

function format_ausgaben_kurz($account){ }

function _lk_can_access_protokoll_verlag($account){ 
  return false;
}


function format_team_title($team){ }


/** Depricated */

function print_plz2($account){ }
function print_plz($account){ return ''; }

function lokalkoenig_merkliste_test_access($node){
 
}


function _lk_check_private_terms($tids){
global $user;
  
  foreach($tids as $tid){
    $dbq = db_query("SELECT count(*) as count FROM  lk_merklisten_terms WHERE uid='". $user -> uid ."' AND tid='". $tid ."'");
    $res = $dbq -> fetchObject();
    
    if($res -> count == 0){
        $taxo = taxonomy_term_load($tid);  
        $nid = db_insert('lk_merklisten_terms') // Table name no longer needs {}
        ->fields(array(
          'uid' => $user -> uid,
          'tid' => $tid,
          'term_name' => $taxo -> name
        ))
        ->execute();
      
    } 
  }
}


/*******************************************/


function getLizenz($lizenz_id){
  $dbq = db_query("SELECT * FROM lk_vku_lizenzen WHERE id='". $lizenz_id ."'");
  return $lizenz = $dbq -> fetchObject();
}

function lk_get_verlag_from_team($team){
   $team = lk_get_team($team);
   return $team->field_verlag['und'][0]['uid'];  
}


function lk_get_team($team) {

   $entity = entity_load('team', array($team));
   $team_entity =  $entity[$team];
   
return $team_entity;   
}

function format_team($team){
  
   $entity = entity_load('team', array($team));
   $team_entity =  $entity[$team];
   
   if($team_entity){
      return '<span>' . $team_entity -> title . '</span>';
   }
}

function getVerlagFromTeam($team){
   $entity = entity_load('team', array($team));
   $team_entity =  $entity[$team];
   
    if($verlag = $team_entity->field_verlag['und'][0]['uid']){
      return $verlag;
    }
}



/** Get the Ausgaben-Title-Kurz 
 *  @ausgabe int
 **/

function lk_get_ausgaben_title_kurz($ausgabe){

  if($b = lk_load_ausgabe($ausgabe)){
      return $b ->field_kurzbezeichnung['und'][0]['value'];
  }
}

/** Format Ausgaben-Title-Kurz 
 *  @ausgabe int
 **/

function format_ausgabe_kurz($id){  
    if($b = lk_load_ausgabe($id)){
      return '<small class="label label-primary" title="'. $b -> field_ortsbezeichnung['und'][0]['value'] .'">' .$b ->field_kurzbezeichnung['und'][0]['value'] . '</small> ';
    }
}


function lk_load_ausgabe($ausgabe){
   $entity = entity_load('ausgabe', array($ausgabe));
   return $entity[$ausgabe];
}

/** Depricated */
function lk_get_verlag_from_user($account){
    
    $user_account = \LK\get_user($account);
    if(!$user_account){
        return false;
    }
    
    return $user_account ->getVerlag();
}


/** User-Array */
$lk_user = array();


function _lk_user($account, $fresh = false){
global $lk_user; 
 
  if(!is_object($account)){
    $account = user_load($account);
    if(!$account) return user_load(0);
  }  

  if(isset($account -> lk)) return $account;
  if(isset($lk_user[$account -> uid]) AND !$fresh) return $lk_user[$account -> uid];

  if($account -> uid == 0) return $account;
  
  $account -> profile = profile2_load_by_user($account);
  $account -> telefon = false;
  $account -> lk = true;
  $account -> verlag = true;
  
  // Lade Bereich
  if(lk_is_mitarbeiter($account)){
    
    if(isset($account->profile['mitarbeiter']->field_mitarbeiter_verlag['und'][0]['uid'])){
       $account -> verlag = $account->profile['mitarbeiter']->field_mitarbeiter_verlag['und'][0]['uid'];
    }
    else {
      $account -> verlag = 0;
    }
      
    
     //$ausgabe = lk_ausgabe_from_user($account);
     //$account -> ausgabe = lk_load_ausgabe($ausgabe); 
     
     // Telefonmitarbeiter
     if(isset($account -> ausgabe->field_telefonmitarbeiter['und'][0]['value'])){
        //$account -> telefon = $account -> ausgabe->field_telefonmitarbeiter['und'][0]['value'];
     }
  }  
  
  $lk_user[$account -> uid] = $account;
   
return $account;  
}



/** Get the Ausgaben-Title 
 *  @ausgabe int
 **/
function lk_get_ausgaben_title($ausgabe){

  if($b = lk_load_ausgabe($ausgabe)){
      return $b ->field_ortsbezeichnung['und'][0]['value'];
  }
}

function get_verlag_from_ausgabe($ausgabe){
  if($b = lk_load_ausgabe($ausgabe)){
      return $b -> field_verlag['und'][0]['uid'];
  }
}


function _format_user($user_id){
    return \LK\u($user_id);
}

