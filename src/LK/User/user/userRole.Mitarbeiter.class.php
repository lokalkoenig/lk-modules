<?php

namespace LK;

class Mitarbeiter extends User {
   
   var $verlag = 0;
   var $user_data = null;
   var $user_role = LK_USER_MITARBEITER;
   var $profile = null;
   var $role_name = 'mitarbeiter';
   
   function isTelefonmitarbeiter() {
       
       // Grant on Teamleiter
       if($this ->isTeamleiter()){
           return true;
       }
       
       // Check if Telephone-Team
       $team = $this ->getTeamObject();
       if($team AND $team -> isTelephone()){
           return true;
       }
       
       return false;
   }
   
   
   function getRole() {
       if($this ->isTeamleiter()){
           return 'Teamleiter';
       }
       else {
           return 'Mitarbeiter';
       }
   }
   
   function __construct($user, $reference){
       $return = parent::__construct($user, $reference);
       
        // Init the User
       $this -> verlag = (int)$this -> profile['mitarbeiter']->field_mitarbeiter_verlag['und'][0]['uid'];
       $this -> team = (int)$this -> profile['mitarbeiter']->field_team['und'][0]['target_id'];
    
       if(!$return) {
            return false;
       }
       
      
       return $this;
    }
    
    function isMitarbeiter() {
        return true;
    }
    
    function getUid(){
        return $this -> user_data -> uid;
    }
    
    function isTestAccount(){
        $verlag = $this ->getVerlagObject();
        
        if(!$verlag){
            return false;
        }
        else {
            $verlag -> isTestAccount();
        }
    }
    
   function getVerlag(){
       return $this -> verlag;
   } 
   
   
   function getVerlagObject(){
      $verlag_uid = $this -> getVerlag();
      return $verlag = $this -> manager -> getUser($verlag_uid);
   }

   function getTeamObject(){
       return $this -> manager -> getTeam($this -> team);
   }   
   
   function isTeamleiter() {
       // Check if Backreference is there in Team-Data
       
       $team = $this -> getTeamObject();
       if(!$team){
           return false;
       }
       
       
       $test_uid = $team -> getLeiter();
       if($test_uid == $this -> getUid()){
           return true;
       }
       else {
          return false; 
       }
   } 
   
  
   
}