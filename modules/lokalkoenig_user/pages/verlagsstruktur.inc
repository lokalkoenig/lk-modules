<?php

/**
 * Ausgaben Admin
 */   
function lokalkoenig_user_verlag_ausgaben($verlag){

   
  lk_set_icon('globe');
  $verlag_obj = \LK\get_user($verlag);
  $bereiche = $verlag_obj ->getAusgaben();
  $teams = $verlag_obj -> getTeams();
  $current = \LK\current();
  
  $action = '';
  if(isset($_GET["action"])){
      $action = $_GET["action"];
  }
  
  $team = 0;
  if(isset($_GET["team"])){
      $team = (int)$_GET["team"];
  }
  
  $ausgabe = 0;
  if(isset($_GET["ausgabe"])){
      $ausgabe = (int)$_GET["ausgabe"];
  }
  
  // Show Members of an Ausgabe
  if(!$action AND isset($bereiche[$ausgabe])){
    $obj = \LK\get_ausgabe($ausgabe); 
    drupal_set_title("Ausgabe: " . $obj ->getTitle());
    return views_embed_view("teammember", 'attachment_2', $obj ->getId());
  }
  
  // Add Ausgabe
  if($action == 'new' AND $current -> hasRight('add ausgabe')){
     $form = eck__entity__add('ausgabe', 'ausgabe');
     $form["#verlag"] = $verlag;
     return $form;
   }
   
    if($action == 'editausgabe' AND $ausgabe){
        if(isset( $bereiche[$ausgabe] )){
            drupal_set_title("Bereich editieren");
            $form = eck__entity__edit('ausgabe', 'ausgabe', $ausgabe);
           return $form;
        } 
    }
   
    // Edit the Team
   if($action == 'editteam' AND $current -> hasRight('edit team')){
        if(isset($teams[$team] )){
            drupal_set_title("Team editieren");
            $id = $teams[$team] -> getId();
            
            $form = eck__entity__edit('team', 'team', $id);
            return $form;
        } 
    }
  
  drupal_set_title("Ausgaben");
  return theme("lk_ausgaben_admin", array("teams" => $teams, "verlag" => $verlag_obj, "bereiche" => $bereiche));
}

function lokalkoenig_user_verlag_form_eck__entity__form_add_ausgabe_ausgabe_alter(&$form, &$form_state, $form_id){
   $form["field_verkaufsleiter"]["#access"] = false;
   $form["field_verlag"]["#access"] = false; 
   $form["field_telefonmitarbeiter"]["#access"] = false; 
   
   array_push($form['#submit'], 'lk_add_new_bereich_submit');
   //array_push($form['#validate'], 'lk_add_new_bereich_validate');  
} 


function lokalkoenig_user_verlag_form_eck__entity__form_edit_ausgabe_ausgabe_alter(&$form, &$form_state, $form_id){
   
   
   $form["field_verkaufsleiter"]["#access"] = false;
   $form["field_verlag"]["#access"] = false; 
   $form["field_telefonmitarbeiter"]["#access"] = false; 
   
   
   array_push($form['#submit'], 'lk_edit_bereich_submit');
   //array_push($form['#validate'], 'lk_add_new_bereich_validate');  
} 

function lokalkoenig_user_verlag_form_eck__entity__form_edit_team_team_alter(&$form, &$form_state, $form_id){
   
   if(arg(0) == 'admin') return ;
   
   //$form["field_verkaufsleiter"]["#access"] = false;
   $form["field_verlag"]["#access"] = false; 
   $form["field_team_verkaufsleiter"]["#access"] = false; 
    $form["title"]["#weight"] = -10;
  
   array_push($form['#submit'], 'lk_edit_team_submit');
  // array_push($form['#validate'], 'lk_add_new_bereich_validate');  
} 



function lokalkoenig_user_verlag_struktur($verlag){

  module_load_include('inc', 'eck', 'eck.entity');
  // List Verlagsstruktur
  
  $current = \LK\current();
  
  $verlags_object = \LK\get_user($verlag);
  if(!$verlags_object){
      drupal_goto("user");
  }
  
  $action = '';
  if(isset($_GET["action"])){
     $action = $_GET["action"]; 
  }
  
  drupal_set_title("Verlagsstruktur");
  $items = $verlags_object -> getAusgaben();
  
  // Neu Verlagsübergreifenden MA anlegen
  if($action == 'addvuema' AND $current -> hasRight('add account')){
    drupal_set_title("Verkaufsübergreifender Mitarbeiter hinzufügen");
    require_once(__DIR__ . "/addnewaccount.inc");
    return drupal_get_form("lk_user_add_vuegr_ma", $verlag -> uid);
  } 
  
  if($action){
    $edit_account = NULL;
      
    if(isset($_GET["uid"])){
          $account = user_load($_GET["uid"]);
          $account_object = \LK\get_user($_GET["uid"]);
          
          if(!$account_object){
              drupal_goto("user/" . $verlag -> uid . "/struktur");
          }
          
          $vid = $account_object ->getVerlag();
          
          if($vid != $verlag -> uid){
              drupal_goto("user/" . $verlag -> uid . "/struktur");
          }
     }
      
      if(($action == 'edit' OR $action == 'editaccount') AND $account AND $current -> hasRight('edit account')){
        drupal_set_title("Account von " . $account -> name  . " editieren");
        require_once(__DIR__ ."/unteraccounts_edit.inc");          
        return drupal_get_form('lk_user_edit_ma', $verlag, $account); 
    }
        elseif($action == 'disable' OR $action == 'enable' AND $account AND $current -> hasRight('edit account')){
       require_once(__DIR__ ."/unteraccounts_edit.inc");          
       return drupal_get_form('lk_user_disable_ma', $verlag, $account); 
    }
        elseif($action == 'status' AND $account AND $current -> hasRight('edit account')){
        drupal_set_title("Account von " . $account -> name  . " deaktivieren");
              
        require_once(__DIR__ ."unteraccounts_edit.inc");          
        return drupal_get_form('lk_user_disable_ma', $verlag, $account);
    }
    elseif($action == 'addaccount' AND $current -> hasRight('edit account')){
      require_once(__DIR__ . "addnewaccount.inc");
      return lokalkoenig_user_verlag_addnew($verlag -> uid);
    }
  }
    
    // get All Users
    $users_clear = $verlags_object -> getAllUsers();
    $users = array();
    $deactivated = array();
    $vums = array();

    foreach($users_clear as $uid){
       $acc = \LK\get_user($uid); 
       $name = $acc ->getUsername();
       
       if(!$acc ->getStatus()){
           $deactivated[$name] = $acc;
       }
       elseif($acc ->isVerlagController()){
          $vums[$name] = $acc; 
       }
       else {
           $users[$name] = $acc; 
       }
    }  
    
    // Sort the users after User-Name
    ksort($users);    
    ksort($vums);    
    ksort($deactivated);
    
    $teams = $verlags_object -> getTeams();
    
  return theme('verlagsstruktur', 
          array('teams' => $teams,
                'users' => $users, 
                'verlag' => $verlags_object, 
                'items' => $items,
                'deactive_users' =>  $deactivated,
                'vums' => $vums,
                'vid' => $verlags_object -> getUid(),
                'current' => $current
        ));
}


function lk_edit_bereich_submit(&$form, &$form_state){
  drupal_get_messages();

  drupal_set_message("Der neue Bereich wurde editiert.");
  drupal_goto("user/" . arg(1) . "/ausgaben");
}



function lk_edit_ausgabe_submit(){
  drupal_get_messages();

  drupal_set_message("Das Team wurde editiert.");
  drupal_goto("user/" . arg(1) . "/ausgaben");

}


function lk_add_new_bereich_submit(&$form, &$form_state){

  drupal_get_messages();
  
  // Entity nochmal anfassen und speichern
  $entity = $form_state['build_info']['args'][0];
  $entity -> field_verlag['und'][0]["uid"] = arg(1);

  $entity->save();
  
  drupal_set_message("Der neue Bereich wurde angelegt.");
  drupal_goto("user/" . arg(1) . "/ausgaben");
}


function lk_edit_team_submit(&$form, &$form_state){
  $entity = $form_state['build_info']['args'][0];
  
  drupal_get_messages();
  drupal_set_message("Das Team <strong>". $entity -> title ."</strong> wurde gespeichert.");
  
  
  drupal_goto("team/" . $entity -> id);
}

?>