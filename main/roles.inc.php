<?php

// Roles visibility functions


function lk_user_is_in_verlag($uid, $verlag_id){
   $account = \LK\get_user($uid); 
    
   if($account):
    if($account ->getVerlag() == $verlag_id){
        return true; 
    } 
   endif;
  
return false;
}

function lk_is_in_testverlag($account){
   
    $accessed = \LK\get_user($account);
    if(!$accessed){
        return false;
    }
    
    return $accessed ->isTestAccount();
 }

function lk_vku_access(){
global $user;
 
  if(!$user -> uid) { 
      return false;   
  }
  
  if(lk_is_agentur()){
    return false;
  }
  
  return true;
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

