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
        //$links[] = array('title' => "NEU: VKU-Extras", "link" => "user/" . $verlag . "/vkuextras", 'icon' => "tint");
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



/** Depricated */
function track_read_neuigkeit($uid, $id){
   $dbq = db_query("SELECT count(*) as count FROM lk_neuigkeiten_read WHERE uid='". $uid ."' AND neuigkeit_id='". $id ."'");
   $result = $dbq -> fetchObject();
    
    if($result -> count == 0){
        db_query("INSERT IGNORE INTO lk_neuigkeiten_read 
            SET uid='". $uid ."', neuigkeit_id='". $id  ."', neuigkeit_read='". time() ."'");
    }  
}
