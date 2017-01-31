<?php

function _lk_get_usernamewpos($uid){
  
  $account = \LK\get_user($uid);
  $return = $account -> $account ->getUsername();

  if($account ->isTeamleiter()){
    $return .= ' - Bereichsleiter';
  }
  
  $return .= ' - ' . $account->profile['main']->field_profile_adresse['und'][0]['organisation_name'];
  $return .= ' (' . $account->profile['main']->field_profile_adresse['und'][0]['locality'] . ')';

return $return;
}


function _lokalkoenig_user_verlag_searchusers($verlag){
  $search = arg(3);
  $results = array();
  
  if($search){
    if(lk_is_verlag($verlag)){
      $dbq = db_query("SELECT uid, name FROM users u WHERE u.status='1' AND u.name LIKE '%". $search ."%'");
      foreach($dbq as $all){
        if(lk_user_is_in_verlag($all -> uid, $verlag -> uid)){
           $results[$all -> name] = _lk_get_usernamewpos($all -> uid); 
        }
      }
    }
  }
  

   drupal_json_output($results);
   drupal_exit();
}

?>