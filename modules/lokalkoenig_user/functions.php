<?php



/** User-Array */
$lk_user = array();


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

function lokalkoenig_user_dashboard_links($account){
  
  $links = array();
  
  $current = \LK\current();
  $user = \LK\get_user($account);
  if(!$user){
      return $links;
  }
  $verlag = $user ->getVerlagObject();
  $team = $user ->getTeamObject();
  if(!$team){
      return $links;
  }
 
  $team_id = $team -> getId();
  $team_title = $team ->getTitle();
 
  
  if($current ->isMitarbeiter() OR $current -> hasRight('profile access')){
       $links = array();
       $links[] = array('id' => 'home', 'title' => "Übersicht", "link" => "team/" . $team_id, 'icon' => "home");
       
       if($current ->isTeamleiter() OR $current ->hasRight('profile access')){
            $links[] = array('id' => 'stats', 'title' => "Statistiken", "link" => "team/" . $team_id . "/stats", 'icon' => "stats");
       }
       
       if($verlag -> showProtokoll()){
           if($current ->isTeamleiter() OR $current ->hasRight('profile access')){
                $links[] = array('id' => 'protokoll', 'title' => "Protokoll", "link" => "team/" . $team_id . "/protokoll", 'icon' => "list");
           } 
       }
          
       if($current ->isTeamleiter() OR $current -> hasRight('profile access')){
           $links[] = array('id' => 'abrechnung', 'title' => "Abrechnung", "link" => "team/" . $team_id . "/abrechnung", 'icon' => "euro");
       }   
       
       if($current -> hasRight('edit team')){
           $links[] = array('id' => 'edit', 'title' => "Editieren", "link" => "team/" . $team_id . "/edit", 'icon' => "pencil");
       }
 }
          
        
  
return $links;  
}


function lokalkoenig_user_profile_links_verlag(\LK\User $account){
    
    $links = array();
    $current = \LK\current();
    $uid = $account -> getUid();
    
    $verlag = $account ->getVerlag();
    $verlag_obj = \LK\get_user($verlag);
    
    if($account -> isVerlag()){
       $links[] = array('title' => "Verlagsdaten editieren", "link" => "user/" . $uid . "/edit/verlag", 'icon' => "wrench");
    }
    
    $links[] = array('title' => "Mitarbeiter", "link" => "user/" . $verlag . "/struktur", 'icon' => "user");
    $links[] = array('title' => "Ausgaben", "link" => "user/" . $verlag . "/ausgaben", 'icon' => "globe");
    $links[] = array('title' => "Verlags-Statistiken", "link" => "user/" . $verlag . "/verlagstats", 'icon' => "stats");
    
    if($verlag_obj -> showProtokoll()){
        $links[] = array('title' => "Mitarbeiter Protokoll", "link" => "user/" . $verlag . "/verlagsprotokoll", 'icon' => "list");
    }
    
    if($current ->isModerator()){
        $links[] = array('title' => "NEU: VKU-Extras", "link" => "user/" . $verlag . "/vkuextras", 'icon' => "tint");
    }
    
    $links[] = array('title' => "Abrechnung", "link" => "user/" . $verlag . "/abrechnung", 'icon' => "euro");

return $links;    
}













function plz_simplyfy($items){
  
  $plz = array();
  
  if(!$items) { return ''; }
  
  foreach($items as $t){
     $term = taxonomy_term_load($t["tid"]);
     $plz[] = $term -> name;
  }
  
  sort($plz);
 
  
  $simple = array();
  foreach($plz as $item){
    $simpletext = $item[0] . $item[1];
    $simple[$simpletext] = $simpletext . " <small>xxx</small>"; 
  }  
  
return implode("," , $simple);
}





function userIsTeamLeiter($account){
  
  $account = _lk_user($account); 

  if(!lk_is_mitarbeiter($account)) return false;
  
  $dbq = db_query("SELECT entity_id FROM field_data_field_team_verkaufsleiter 
      WHERE bundle='team' AND field_team_verkaufsleiter_uid='". $account -> uid ."' LIMIT 1");
      
   $result = $dbq -> fetchObject(); 
   //dpm($result);
   if(!$result) return false;   
  
   return $result -> entity_id; 
}








function lizenz_log_augaben($lizenz_id){
  $dbq = db_query("SELECT lizenz_uid FROM lk_vku_lizenzen WHERE id='". $lizenz_id ."'");
  $lizenz = $dbq -> fetchObject();
  
  $account = \LK\get_user($lizenz -> lizenz_uid);
  if(!$account){
      return ;
  }
  
  $ausgaben = $account ->getCurrentAusgaben();
  
  foreach($ausgaben as $ausgabe){
    db_query("INSERT INTO lk_vku_lizenzen_ausgabe SET lizenz_id='". $lizenz_id ."', ausgabe_id='". $ausgabe ."'"); 
  }
}





function getAusgabenFromUser($account, $current = false){

  $account = _lk_user($account);
  $ausgaben = array();

  
  if(lk_is_telefonmitarbeiter($account) AND $current == false){
    
    if(isset($account->profile['mitarbeiter']->field_telefonmitarbeiter_ausgabe['und'])){
      foreach($account->profile['mitarbeiter']->field_telefonmitarbeiter_ausgabe['und'] as $ausgabe){
        $ausgaben[] = $ausgabe["target_id"];
      }
    }
  }
  else {
    if(isset($account->profile['mitarbeiter']->field_ausgabe['und'])){
      foreach($account->profile['mitarbeiter']->field_ausgabe['und'] as $ausgabe){
        $ausgaben[] = $ausgabe["target_id"];
      }
    }
  }
  
return $ausgaben; 
}









function lk_user_is_in_verlag($uid, $verlag_id){
   $account = \LK\get_user($uid); 
    
   if($account):
    if($account ->getVerlag() == $verlag_id){
        return true; 
    } 
   endif;
  
return false;
}





function _lk_user($account, $fresh = false){
global $lk_user; 
 
  if(!is_object($account)){
    $account = user_load($account);
    if(!$account) return user_load(0);
  }  

  if(isset($account -> lk)) return $account;
  if(isset($lk_user[$account -> uid]) AND !$fresh) return $lk_user[$account -> uid];


  lk("LOAD USER (". $account -> uid .")");

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



/** Alias for lk_is_telefonmitarbeiter */
function lokalkoenig_user_is_telefonmitarbeiter($account){
  return lk_is_telefonmitarbeiter($account);
}


function lk_is_telefonmitarbeiter($account){
    
    $obj = \LK\get_user($account);
    if(!$obj){
        return false;
    }
    
return $obj ->isTelefonmitarbeiter();    
}


function _lk_check_role($account = NULL, $number){
global $user;
  
  if($account == NULL) $account = $user;
  
  if(isset($account -> roles[$number])){
    return true;
  }
  else {
    return false;
  }
}



// Ist Administrator
function lk_is_admin($account = NULL){
  return _lk_check_role($account, 3);
}

// Ist Agentur
function lk_is_agentur($account = NULL){
  return _lk_check_role($account, 4);
}

function lk_is_verlagsuser($account = NULL){
    
    if(lk_is_verlag($account) ) {
        return true;
    }
    
    if(lk_is_moderator($account)) {
        return true;
    }
    
    if(lk_is_verlags_controller($account)) {
        return true;
    }
 
    return false;
}


// Ist Agentur
function lk_is_moderator($account = NULL){
  if(lk_is_admin($account)){
    return true;
  }
  
  return _lk_check_role($account, 7);
}

function lk_is_verlags_controller($account = NULL){
  return _lk_check_role($account, 8);
}


function lk_is_verlag($account = NULL){
  return _lk_check_role($account, 5);
}

function lk_is_mitarbeiter($account = NULL){
  return _lk_check_role($account, 6);
}


function lklink($title, $url, $glyph = NULL, $class = 'list-group-item'){
  if($glyph){
     $title = '<span class="glyphicon glyphicon-'. $glyph .'"></span> ' . $title;
  }

return l($title, $url, array('html' => true, 'attributes' => array("class" => array($class))));
}

function lokalkoenig_user_check_user_access_is_agentur($account){
global $user;
  
  if(!lk_is_agentur($account)){
    return false;
  }
 
  if($user -> uid == 0) return false;
  
  if(lk_is_moderator()) return true;
  
  if($user -> uid == $account -> uid){
      return true; 
  }
 
  return false;
}


function lokalkoenig_user_check_user_access($account){
global $user;
 
  if($user -> uid == 0) return false;
  
  if(lk_is_moderator()) return true;
  
  if($user -> uid == $account -> uid){
      return true; 
  }
  
  
  return false;
}



function lk($msg){
    return ;
    
   file_put_contents('log2.txt', time() . " - " . $msg . " - ".  current_path() ."\n", FILE_APPEND);
}


function lk_list_verlag_team($vid){
    
   // Später sort nach Titel
   $return = array();
    
   $items = array();
   $dbq = db_query("SELECT v.entity_id FROM field_data_field_verlag v, eck_team t 
    WHERE 
      v.bundle='team' AND v.field_verlag_uid='". $vid ."' 
      AND t.id=v.entity_id
    ORDER BY t.title ASC");
   foreach($dbq as $all){
      $entity = entity_load('team', array($all -> entity_id));
      $items[$all -> entity_id] = $entity[$all -> entity_id];      
   }
   
   return $items;
}


function lk_list_verlag_bereiche($vid){
  
    
   $items = array();
   $dbq = db_query("SELECT entity_id FROM field_data_field_verlag WHERE bundle='ausgabe' AND field_verlag_uid='". $vid ."'");
   foreach($dbq as $all){
          $entity = entity_load('ausgabe', array($all -> entity_id));
          $items[$all -> entity_id] = $entity[$all -> entity_id];      
   }
   
   return $items;
}

/** 
 *  List the MA Associatied with a user
 *  But not the Leiter itself
 *  @old function rewritten
 *  @date 2015-03-10    
 **/
function lk_list_verlag_ma_from_leiter($account_id){
   $return = array();
   
   $team = getTeamFromUser(_lk_user($account_id));
   $dbq = db_query("SELECT p.uid FROM 
                      profile p, 
                      field_data_field_team t,
                      users u 
    WHERE 
   p.type='mitarbeiter' AND
   p.pid=t.entity_id AND p.uid IS NOT NULL AND
   t.field_team_target_id='". $team ."' AND u.uid=p.uid AND u.status='1' 
   ORDER BY u.name ASC");
    foreach($dbq as $all){
      $return[] = $all -> uid;
    }
    
   return $return;       
}


/** 
 *  Gets a Team-Leiter from a User
 *  Rewritten 2015-03-10 
 **/
function lk_user_get_leiter($uid){
  $account = _lk_user($uid);
  
  if(!lk_is_mitarbeiter($uid)) return false;
  
  if(userIsTeamLeiter($account)){
    return $account;
  }
  else {
    // Get-Team-From-user
    if($team = getTeamFromUser($account)){
        $user = getVerkaufsleiterFromTeam($team);
        if(isset($user[0])){
          return _lk_user($user[0]);
        }
    }
  }
  
return false; 
}




/** Depricated */
function lk_list_verlag_ma($vid){
    $return = array();
    
    $dbq = db_query("SELECT p.uid 
    FROM field_data_field_mitarbeiter_verlag v, profile p, users u WHERE
     v.entity_id = p.pid AND p.uid=u.uid AND u.status='1' AND
    v.field_mitarbeiter_verlag_uid = '". $vid ."' AND p.uid IS NOT NULL");
    foreach($dbq as $all){
      $return[] = $all -> uid;
    }
    
    
    
   return $return; 
}


function track_read_neuigkeit($uid, $id){
   $dbq = db_query("SELECT count(*) as count FROM lk_neuigkeiten_read WHERE uid='". $uid ."' AND neuigkeit_id='". $id ."'");
   $result = $dbq -> fetchObject();
    
    if($result -> count == 0){
        db_query("INSERT IGNORE INTO lk_neuigkeiten_read 
            SET uid='". $uid ."', neuigkeit_id='". $id  ."', neuigkeit_read='". time() ."'");
    }  
}

/******************************************* /


/** Deprecated */
function lk_ausgabe_from_user($account){

  $account = _lk_user($account);

  if(isset($account -> profile['mitarbeiter']->field_ausgabe['und'][0]['target_id'])){
      return $account -> profile['mitarbeiter']->field_ausgabe['und'][0]['target_id']; 
  }

return false;
}


function getTeamFromUser($account){
  // User kann nur ein Team haben
  $account = _lk_user($account);
  
  if(isset($account->profile['mitarbeiter']->field_team['und'][0]['target_id'])){
    return $account->profile['mitarbeiter']->field_team['und'][0]['target_id'];
  }
  else {
    return 0;
  }
}


// DONEEEEE
function lk_is_in_testverlag($account){
   
    $accessed = \LK\get_user($account);
    if(!$accessed){
        return false;
    }
    
    return $accessed ->isTestAccount();
 }

function lk_get_user_from_team($team_id){
   
   $dbq = db_query("SELECT p.uid FROM 
                      profile p, 
                      field_data_field_team t,
                      users u 
    WHERE 
   p.type='mitarbeiter' AND
   p.pid=t.entity_id AND p.uid IS NOT NULL AND
   t.field_team_target_id='". $team_id ."' AND u.uid=p.uid AND u.status='1' 
   ORDER BY u.name ASC");
    foreach($dbq as $all){
      $return[] = $all -> uid;
    }
  
return $return;
}

function lk_get_verlag_from_team($team){
   $team = lk_get_team($team);
   return $team->field_verlag['und'][0]['uid'];  
}


function getVerkaufsleiterFromTeam($team_id){
    
    $vkl = array();
    $team = \LK\get_team($team_id);
    
    if($team){
        $vkl[] = $team ->getLeiter();
    }
   
return $vkl;   
}

function format_team_linked($team){
    
   if(!$team){
       return ;
   } 
    
   $entity = entity_load('team', array($team));
   $team_entity =  $entity[$team];
   
   if($team_entity){
      return '<span>' . l($team_entity -> title, "team/" . $team_entity -> id) . '</span>';
   }
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


function gettheVerkaufsleiterFromTeam($team){
    $user = getVerkaufsleiterFromTeam($team);
    
    if($user){
      return $user[0];
    }
}


function team_is_telephone_team($team_id){
   $entity = entity_load('team', array($team_id));
   $team_entity =  $entity[$team_id];

   if($team_entity->field_telefonteam['und'][0]['value']){
      return true;
   }
   else return false;
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



function _lk_get_user_pos_format($accuid){
  return _lk_get_user_pos_format_html($accuid);
}

function _lk_get_user_pos_format_html($accuid){  
    
    
}


function _lk_get_user_pos($accuid){
   return array();
}

?>