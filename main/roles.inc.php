<?php
/**
 * @file
 * Role visibility functions
 * 
 * 
 * 
 */

/**
 * Gives back the Information in
 * if the user is in this Verlag
 * 
 * @param Int $uid
 * @param Int $verlag_id
 * @return boolean
 */
function lk_user_is_in_verlag($uid, $verlag_id){
   $account = \LK\get_user($uid); 
    
   if($account):
    if($account ->getVerlag() == $verlag_id){
        return true; 
    } 
   endif;
  
return false;
}


/**
 * Checks weather the current user is a Verlag
 * and if the current user can access this
 * 
 * @param type $account
 * @return boolean
 */
function lk_verlag_access($account){
  
  $current = \LK\current();
  $verlag = \LK\get_user($account);
  
  if(!$current){
    return false;
  }
  
  if(!$verlag || !$verlag ->isVerlag()){
    return false;
  }
  
  if($current ->isModerator()){
    return true;
  }
  
  if($current ->getVerlag() === $verlag ->getUid()){
    return true;
  }
  
  if($current ->isVerlag() || $current ->isVerlagController()){
    return true;
  }
  
return false;  
}

/**
 * Gives back the Information 
 * if the user is in a Testverlag
 * 
 * @param array $account
 * @return boolean
 */
function lk_is_in_testverlag($account){
   
    $accessed = \LK\get_user($account);
    if(!$accessed){
        return false;
    }
    
 return $accessed ->isTestAccount();
 }

 /**
  * VKU-Access
  * User is not Agentur and Logged
  * 
  * @global stdClass $user
  * @return boolean
  */
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

/**
 * User is Telephone-MA
 * 
 * @param stdClass $account
 * @return boolean
 */
function lk_is_telefonmitarbeiter($account){
    
    $obj = \LK\get_user($account);
    if(!$obj){
        return false;
    }
    
return $obj ->isTelefonmitarbeiter();    
}

/**
 * Internal-Function to check the role 
 * of the user
 * 
 * @global stdClass $user
 * @param stdClass $account
 * @param int $number Role-Number
 * @return boolean
 */
function _lk_check_role($account = NULL, $number){
global $user;
  
  if($account == NULL) {
    $account = $user;
  }
  
  if(isset($account -> roles[$number])){
    return true;
  }
  
 return false;  
}



/**
 * User is Admin
 * 
 * @param stdClass $account
 * @return boolean
 */
function lk_is_admin($account = NULL){
  return _lk_check_role($account, 3);
}

/**
 * User is Agentur
 * 
 * @param stdClass $account
 * @return boolean
 */
function lk_is_agentur($account = NULL){
  return _lk_check_role($account, 4);
}

/**
 * User is Moderator
 * 
 * @param stdClass $account
 * @return boolean
 */
function lk_is_moderator($account = NULL){
  
  if(lk_is_admin($account)){
    return true;
  }
  
return _lk_check_role($account, 7);
}

/**
 * User is Verlags-Controller
 * 
 * @param stdClass $account
 * @return boolean
 */
function lk_is_verlags_controller($account = NULL){
  return _lk_check_role($account, 8);
}

/**
 * User is Verlags
 * 
 * @param stdClass $account
 * @return boolean
 */
function lk_is_verlag($account = NULL){
  return _lk_check_role($account, 5);
}

/**
 * User is Mitarbeiter
 * 
 * @param stdClass $account
 * @return boolean
 */
function lk_is_mitarbeiter($account = NULL){
  return _lk_check_role($account, 6);
}


/**
 * Menu-Access-Callback
 * for Agentur-Pages
 * 
 * @param stdClass $account
 * @return boolean
 */
function lokalkoenig_user_check_user_access_is_agentur($account){
  
  if(!lk_is_agentur($account)){
    return false;
  }
 
  $current = \LK\current();
  if(!$current){
    return false;
  }
  
  if($current ->isModerator()){
    return true;
  }
  
  if($current ->getUid() == $account -> uid){
    return true;
  }
  
return false;
}

function lklink($title, $url, $glyph = NULL, $class = 'list-group-item'){
  if($glyph){
     $title = '<span class="glyphicon glyphicon-'. $glyph .'"></span> ' . $title;
  }

return l($title, $url, array('html' => true, 'attributes' => array("class" => array($class))));
}

