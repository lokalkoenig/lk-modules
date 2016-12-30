<?php
namespace LK;

/**
 * Gives back the Usermanager
 * 
 * @return \LK\UserManager
 */
function manager(){
global $user_manager;

    if(!$user_manager){
        $user_manager = new UserManager();  
    }    
    
return $user_manager;     
}

function current_verlag_uid(){
    
    $account = current();
    if(!$account){
        return false;
    }
    
    if($account ->isModerator()){
        return LK_TEST_VERLAG_UID;
    }
    
return $account ->getVerlag();    
}


function u($user_id){
   if(!$user = get_user($user_id)) {
        return '<span class="label label-default">Extern</span>';
   }
   else {
       return (string)$user;
   } 
}

function user_ausgaben_f($user_id){
   if($user = get_user($user_id)) {
       return $user ->getAusgabenFormatted(); 
   }
}

/**
 * 
 * @param stdClass $user
 * @return \LK\User
 */
function get_user($user){
    $manager = manager();
    
    if(is_object($user)){
        $uid = $user;
    }
    else {
        $uid = user_load($user);
    }
    
    return $manager ->getUser($uid);
}

/**
 * Gets the Object of the Current Object
 * 
 * @global type $user
 * @return \LK\User
 */
function current(){
global $user;
    return get_user($user);
}


/**
 * Gets Back a Ausgabe Object
 * 
 * @param int $ausgabe
 * @return \LK\Ausgabe
 */
function get_ausgabe($ausgabe){
  $manager = manager();
  
  $object = $manager ->getAusgabe($ausgabe);
  return $object;
}


function get_team($team_id){
  $manager = manager();
  
  $object = $manager ->getTeam($team_id);
  return $object;
}


function get_verlag_id($user){
  $manager = manager();
  
  $account = $manager ->getUser($user);
  $verlag_id = $account ->getVerlag();
  
  return (int)$verlag_id;
}

function stats(){
    
    $manager = manager();
    
return $manager -> getStats();    
}


require_once 'UserManager.Team.class.php';
require_once 'UserManager.Ausgabe.class.php';

// User
require_once 'user/userBase.class.php';
require_once 'user/userRole.Mitarbeiter.class.php';
require_once 'user/userRole.Verlag.class.php';
require_once 'user/userRole.Moderator.class.php';
require_once 'user/userRole.Agentur.class.php';
require_once 'user/userRole.VerlagController.class.php';

require_once 'lizenz/lizenz.php';

define('LK_USER_MITARBEITER', 2);
define('LK_USER_VERLAG', 1);

define("LK_USER_MODERATOR", 10);
define("LK_USER_AGENTUR", 11);

global $user_manager;




/**
 * User-Manager 
 * Keeps track of the User-Objects
 * 
 * 
 */
class UserManager {
    private $user = array();
    private $ausgaben = array();
    public $teams = array();
    
    function getStats(){
        
        $items = array();
        $items[] = 'User: ' . count($this -> user);
        $items[] = 'Ausgaben: ' . count($this -> ausgaben);
        $items[] = 'Teams: ' . count($this -> teams);
    
    return '<ul><li>'. implode('</li><li>', $items) .'</li></ul>';    
    }
    
    
    /**
     * Retrieve Back a User-Object
     * 
     * @param type $user
     * @return boolean
     */
    function getUser($user){
        
        if(is_int($user)){
            if($user == 0){
                return false;
            }
            
            $user_object = user_load($user);
       }
       else {
            $user_object = $user;
       }
       
       if(!$user_object OR $user_object -> uid == 0){
           return false;
       }
       
       if(isset($this -> user[$user_object -> uid])){
           return $this -> user[$user_object -> uid];
       }
      
        // Load profiles
       $user_object -> profile = profile2_load_by_user($user_object);
       
       if(lk_is_moderator($user_object)){
           $return = new Moderator($user_object, $this);
       }
       elseif(lk_is_verlags_controller($user_object)){
           $return = new VerlagController($user_object, $this);
       }
       elseif(lk_is_verlag($user_object)){
           $return = new Verlag($user_object, $this);
       }
       elseif(lk_is_mitarbeiter($user_object)){
           $return = new Mitarbeiter($user_object, $this);
       }
       elseif(lk_is_agentur($user_object)){
           $return = new Agentur($user_object, $this);
       }
       else {
         return false;  
       }
       
       if(!$return) {
         return false;  
       }
       
        $this -> user[$user_object -> uid] = $return;
        return $this -> user[$user_object -> uid];
    } 
    
    function getAusgabe($ausgaben_id){
     
        if(isset($this -> ausgaben[$ausgaben_id])){
            return $this -> ausgaben[$ausgaben_id];
        }
        $ausgabe = new Ausgabe($ausgaben_id);
        
        $this -> ausgaben[$ausgaben_id] = $ausgabe; 
        
        return $ausgabe; 
    }
    
    function getAdmins(){
        return $this ->_get_users_by_role(7);
   }
    
    function getAgenturen(){
       return $this ->_get_users_by_role(5);
    }
    
    function getVerlagsAccounts(){
      $arr = $this ->getVerlage();
      $arr += $this ->getVerlagsController();
        
    return $arr;  
    }
    
    function getVerlage(){
         return $this ->_get_users_by_role(5);
    }
    
    function getMitarbeiter(){
         return $this ->_get_users_by_role(6);
    }
    
    function getVerkaufsleiter(){
        
        $user = array();
        
        $dbq = db_query("SELECT u.uid FROM "
                . "field_data_field_team_verkaufsleiter v, users u WHERE "
                . "u.uid=v.field_team_verkaufsleiter_uid AND u.status='1' AND v.bundle='team' AND v.deleted='0'");
        foreach($dbq as $arr){
          $user[] = $arr -> uid;
        }
    
    return $user;    
    }
    
    function getVerlagsController(){
        return $this ->_get_users_by_role(8);
    }
    
    
    /**
     * Gets the Users by Role
     * 
     * @param Int $role_id
     * @return type
     */
    private function _get_users_by_role($role_id){
        
        $query = db_select('users', 'u')
                ->fields('u', array('uid'))
                ->condition('u.status', 1, '=')
                ->condition('ur.rid', $role_id, '=') // set to an array of roles and pass. In this case I knew ahead of time what rids I wanted to filter by.
                ->groupBy('u.uid');
          $query->innerJoin('users_roles', 'ur', 'u.uid = ur.uid');
          $results = $query->execute();
    
          $users = array();
          foreach($results as $result) {
             $users[] = $result -> uid;
          }
    
    return $users;      
    } 
    
    
    
    /**
     * Get back a Team
     * 
     * @param type $team_id
     * @return Team
     */
    function getTeam($team_id){
       if(!isset($this -> teams[$team_id])){
          
           try {
               $team = new Team($team_id);
            } 
            catch (LKException $e) {
                return false;
            }
           
           $this -> teams[$team_id] = new Team($team_id);
       }
       
       return $this -> teams[$team_id];
    }
    
}


class LKException extends \Exception { 
    
    
    
    
    
}