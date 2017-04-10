<?php


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
    
  $links = [];
  $uid = $account -> getUid();
  $verlag = $account ->getVerlagObject();
  $verlag_uid = $verlag ->getUid();

  if($account -> isVerlag()){
     $links[] = array('title' => "Verlagsdaten editieren", "link" => "user/" . $uid . "/edit/verlag", 'icon' => "wrench");
  }
    
  if($verlag ->getVerlagSetting('vku_editor', 0)) {
    $links[] = array('title' => "VKU Vorlagen editieren", "link" => "user/" . $verlag_uid . "/vku_editor", 'icon' => "file");
  }
    
  $links[] = array('title' => "Mitarbeiter", "link" => "user/" . $verlag_uid . "/struktur", 'icon' => "user");
  $links[] = array('title' => "Ausgaben", "link" => "user/" . $verlag_uid . "/ausgaben", 'icon' => "globe");
  $links[] = array('title' => "Verlags-Statistiken", "link" => "user/" . $verlag_uid . "/verlagstats", 'icon' => "stats");

  if($verlag->isLKTestverlag()) {
    $links[] = array('title' => "Mitarbeiter-Lizenzen", "link" => "user/" . $verlag_uid . "/user_lizenzen", 'icon' => "cloud-download");
  }

  if($verlag -> showProtokoll()){
    $links[] = array('title' => "Mitarbeiter Protokoll", "link" => "user/" . $verlag_uid . "/verlagsprotokoll", 'icon' => "list");
  }
    
  $links[] = array('title' => "Abrechnung", "link" => "user/" . $verlag_uid . "/abrechnung", 'icon' => "euro");

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



/** Depricated */
function track_read_neuigkeit($uid, $id){
   $dbq = db_query("SELECT count(*) as count FROM lk_neuigkeiten_read WHERE uid='". $uid ."' AND neuigkeit_id='". $id ."'");
   $result = $dbq -> fetchObject();
    
    if($result -> count == 0){
        db_query("INSERT IGNORE INTO lk_neuigkeiten_read 
            SET uid='". $uid ."', neuigkeit_id='". $id  ."', neuigkeit_read='". time() ."'");
    }  
}
