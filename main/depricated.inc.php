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

function vku_is_update_user_ppt(){
    return vku_is_update_user();
}    

function vku_is_update_user(){
    
  $account = \LK\current();
  if(!$account || $account ->isAgentur()){
    return false;
  }

  if($account ->isModerator()){
    return true;
  }

  $verlag = $account ->getVerlagObject();
  if(!$verlag){
    return false;
  }

  $vku2 = $verlag ->getVerlagSetting('vku_2', 0);
  if($vku2){
    return true;
  }
    
  return false;    
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

/** Depricated */

function print_plz2($account){ }
function print_plz($account){ return ''; }


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



/**
  * @deprecated
  */
 function vku_get_active_id(){
 global $user;

    return \LK\VKU\VKUManager::getActiveVku($user -> uid);
 }

 function get_nid_in_vku_count($nid, $vku_status = array(), $where = array()){

  $where_first = array();

  //$where = array();
  $where_first[] = "n.data_entity_id='". $nid  ."'";
  $where_first[] = "n.data_class='kampagne'";

  if($vku_status){
    $where_first[] = "v.vku_status IN ('". implode("','", $vku_status)  ."')";
  }

  if($where) {
    foreach($where as $item){
      $where_first[] = $item;
    }
  }


   $dbq = db_query("SELECT
        count(*) as count
        FROM lk_vku_data n, lk_vku v
        WHERE
            n.vku_id=v.vku_id

            AND " . implode(" AND ", $where_first));
  $all = $dbq -> fetchObject();

  return $all -> count;
}


  function vku_active_user_notfinal_count($uid){
    //created ready downloaded
    $dbq = db_query("SELECT count(*) as count FROM lk_vku WHERE uid='". $uid ."' AND vku_status IN ('active', 'ready', 'created', 'downloaded')");
    $result = $dbq -> fetchObject();

  return $result -> count;
  }


/**
 * Gets a Standard PDF based from a VKU
 *
 * @param Object $vku
 * @return type
 */
function vku_generate_get_pdf_object($vku){


  $author = $vku -> getAuthor();

  $account = \LK\get_user($author);
  $verlag_uid = $account -> getVerlag();

  if($account ->isModerator()){
      $verlag_uid = LK_TEST_VERLAG_UID;
  }

  $pdf = generate_pdf_object_verlag((int)$verlag_uid);

return $pdf;
}

/**
 * Gets a PDF Object
 *
 * @global type $user
 * @param type $verlag
 * @return \PDF
 */
function generate_pdf_object_verlag($verlag = 0){
global $user;

    // If Local
    if($verlag){
        $verlag_id = $verlag;
    }
    else {
       // Get the Verlag ID from current User
       $account = _lk_user($user);
       $verlag_return = lk_get_verlag_from_user($account);
       $verlag_id = (int)$verlag_return;
    }

    $pdf = \LK\PDF\PDF_Loader::load();

    // Instanciation of inherited class
    $pdf -> setVKUDefaults();
    $pdf -> setVerlag($verlag_id);
    $pdf -> SetMargins(0);
    $pdf -> SetTopMargin(30);
    $pdf -> AliasNbPages();
    $pdf -> SetTextColor(69, 67, 71);

 return $pdf;
}


// Items
function vku_generate_pdf_default($vku, $page, $pdf){

    $module_dir = 'sites/all/modules/lokalkoenig/vku/pages/';

    switch($page["data_class"]){
          case 'title':
            require($module_dir.'/a-cover.php');
            break;

          case 'contact':
          case 'kontakt':
            require($module_dir.'/z-contact.php');
            break;

          case 'kplanung':
            require($module_dir.'/r-kampagnenplanung.php');
            break;

          case 'onlinewerbung':
            require($module_dir.'/q-onlinewerbung.php');
            break;

          case 'wochen':
              require($module_dir.'/p-wochenblaettern.php');
          break;

          case 'tageszeitung':
              require($module_dir.'/o-tageszeitungen.php');
          break;
      }
}

// Node
function vku_generate_pdf_node($vku, $page, $pdf){

     $nid = $page["data_entity_id"];
     $node = node_load($nid);
     $module_dir = 'sites/all/modules/lokalkoenig/vku/pages/';

     require($module_dir.'/b-medias.php');
}


function _vku_load_vku_settings_node(&$node, $page){

    if(!isset($page["data_serialized"]) OR !$page["data_serialized"]){
        $data = array();
    }
    else {
       $data = unserialize($page["data_serialized"]);
    }


    $node -> vku_hide = false;

    if($data AND isset($data["desc"]) AND $data["desc"] == 1){
        $node -> vku_hide = true;
    }

    // Parse Medien
    while(list($key, $media) = each($node-> medien)):
        $node -> medien[$key] -> vku_hide = false;
        $node -> medien[$key] -> vku_hide_varianten = false;

        // Allgemeine Beschreibung
        if($data AND isset($data["media_" . $media -> id]) AND $data["media_" . $media -> id] == 1){
            $node -> medien[$key] -> vku_hide_varianten = true;
        }

        if($data AND isset($data["media_" . $media -> id]) AND $data["media_" . $media -> id . "_overview"] == 1){
            $node -> medien[$key] -> vku_hide = true;
        }
    endwhile;
}

