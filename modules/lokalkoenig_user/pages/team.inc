<?php

function lokalkoenig_render_team_tabs($team){
    $leiter = $team -> getLeiter();
    $leiter_account = \LK\get_user($leiter);
    $links = lokalkoenig_user_dashboard_links($leiter_account);
    
    $active = arg(2);
    if(!$active){
        $active = 'home';
    }
    
    // If Links smaller then this
    if(count($links) < 2){
        return '';
    }
    
    $html = '<ul class="nav nav-tabs" role="tablist">';  
    
    foreach ($links as $link){ // <!--class="active"-->
       if($link["id"] == $active){
            $html .= '<li role="presentation" class="active"><a href="'. url($link["link"]) .'" role="tab"><span class="glyphicon glyphicon-'. $link["icon"] .'"></span> '. $link["title"] .'</a></li>'; 
       }
       else {
            $html .= '<li role="presentation"><a href="'. url($link["link"]) .'" role="tab"><span class="glyphicon glyphicon-'. $link["icon"] .'"></span> '. $link["title"] .'</a></li>'; 
       }
        
    }
    
    $html .= '</ul>';
    
return $html;    
}


function lokalkoenig_user_team_overview($team_raw_id){
global $user;
  
  $manager = \LK\manager();
  $team = $manager -> getTeam((int)$team_raw_id);
  $current = \LK\current();
  $page = arg(2);
  lk_set_icon("comment");
  
  if(!$team){
    drupal_goto('<front>');
    drupal_exit();  
  }
  $leiter = $team -> getLeiter();
  $verlag = $team -> getVerlag();
  $verlag_obj = \LK\get_user($verlag);
  
  $tabs = lokalkoenig_render_team_tabs($team);
      
  if($page == 'protokoll'){
      if($current -> isModerator()){
          // Grant;
      }
      elseif(!$verlag_obj -> showProtokoll()){
        $page = 'dashboard';  
      }
  }
  
  $team_id = $team -> getId();
  
  if(lk_is_verlag() AND $user -> uid == $verlag){
      // grant
  }
  elseif(lk_is_moderator()){
      // granted
  }
  elseif(lk_is_mitarbeiter()){
    // Checken ob Drin oder nicht
    $members = $team -> getUser();

    if(in_array($user -> uid, $members) AND $leiter == $user -> uid){
        // grant
    }
    else {
        if(in_array($user -> uid, $members)){
           if(!in_array($page, array('', 'dashboard',  'members'))){
               $page = 'dashboard'; 
           }
        }
        else {
           drupal_goto('user');
           drupal_exit();
        }
    }
  }  

  
  drupal_set_title($team -> getTitle());
  $v = $team -> getVerlag();
  $verlag = _lk_user($v);
  
  lk_set_subtitle($verlag -> name);
  $vkl =  $team ->getLeiter();
  
  switch($page){
    case 'edit':
      if(!lk_is_mitarbeiter()){                                   
          drupal_goto("user/" . $v . "/ausgaben", array("query" => array("action" => "editteam", "team" => $team_id)));
          drupal_exit();
      }
    
      break;
    
    case 'protokoll':
      return $tabs . views_embed_view("log", 'team', $team_id);
      break;
      
    case 'stats';
      return $tabs . _lk_statsUser(array("team" => $team_id));
      break;
      
    case 'abrechnung':
      require("verlag_abrechnung.inc");
      return $tabs . lokalkoenig_team_stats_abrechnung_page($team_id);
      break;  
      
    default:
      return $tabs. team_show_members_and_admin($team);
      break;  
  }
  
  
return 'No Access';
}  
/**
 * 
 * @param LK\Team $team
 * @return type
 */

function team_show_members_and_admin($team){

    $leiter_id = $team -> getLeiter();
    $vkl = \LK\get_user($leiter_id);
    $current = \LK\current();
    $user2 = $team ->getUser();

    $accounts = array();
    $accounts[$leiter_id] = $vkl;
    $deactivated = array();
    
    $info = theme('mitarbeiterinfo_vkl', array('account' => $vkl));

  
  foreach($user2 as $u){
     $account = \LK\get_user($u);
     
     if($leiter_id == $account ->getUid()){
         continue;
     }
     
     if($account ->getStatus()){
        $accounts[$u] = $account;
     }
     else {
        $deactivated[$u] = $account;
     }
  }
  
  $form = '';
  
  if($current -> hasRight('edit_team')){
    $ma_array = array();
    $leiter_id = $vkl -> uid;
    
        foreach($accounts as $a):
            $uid = $a -> getUid();
            $name = $a ->getUsername();
      
            if($a ->isTeamleiter()){
                $ma_array[$uid] = $name . ' (Verkaufsleiter)';
            }
            else {
                $ma_array[$uid] = $name;
            }
            
        endforeach;
  
    $form = drupal_get_form("form_change_team_leiter", $team ->getId(), $ma_array, $leiter_id);
  }
  
  $array = array(
    'current' => $current,  
    'accounts' => $accounts,
    'leiter' => $vkl,
    'deactivated' => $deactivated,  
    'team' => $team,
    'form' => $form,
    'header' => $info 
  );
  
 return theme('team_mitarbeiter', $array); 
}

  
function form_change_team_leiter_submit($form, $form_state){
 $args = ($form_state['build_info']['args']);
  
  $team = lk_get_team($args[0]);
  
  if($form_state["values"]["select"] != $args[2]){
     if(isset($args[1][$form_state["values"]["select"]])){
        $team ->field_team_verkaufsleiter['und'][0]["uid"] = $form_state["values"]["select"];
        entity_save('team', $team);   
        drupal_set_message("Der Verkaufsleiter wurde geändert.");
     } 
  }
}

  
function form_change_team_leiter($form, $form_state, $team_id, $ma, $leiter){

  
  $args = ($form_state['build_info']['args']);

   $form["select"] = array(
    '#type' => 'select',
    '#title' => "Mitarbeiter",
    '#options' => $ma,
    '#default_value' => $leiter
   );
  
  $form['submit'] = array('#type' => 'submit', '#value' => ('Verkaufsleiter ändern'));

  return $form;
}  



?>