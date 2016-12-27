<?php

namespace LK;

class VerlagController extends User {
    
   var $ausgaben = array();
   var $role_name = 'VUM';
   var $verlag = 0;
   
   function __construct($user, $reference){
      parent::__construct($user, $reference);
      
      $this -> verlag = (int)$this -> profile['mitarbeiter']->field_mitarbeiter_verlag['und'][0]['uid'];
      return $this;
   }
   
   function getRoleLong(){
      return 'Verkaufsübergreifender Mitarbeiter'; 
   }
   
   function isTelefonmitarbeiter(){
       return true;
   }
   
   function isVerlagController() {
       return true;
   }
   
   function getVerlag(){
       return $this -> verlag;
   } 
   
   function getVerlagObject(){
      $verlag_uid = $this -> getVerlag();
      
      return $verlag = $this -> manager -> getUser($verlag_uid);
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
    
    function hasRight($key) {
        
        if($key == 'profile access'){
            return true;
        }
        
        parent::hasRight($key);
    }
    
}


?>