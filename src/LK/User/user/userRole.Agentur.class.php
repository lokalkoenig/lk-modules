<?php

namespace LK;

class Agentur extends User {
   
   var $verlag = 0;
   var $user_data = null;
   var $telefon = false;
   var $profile = null;
   var $role_name = 'agentur';
   
   function __construct($user, $reference){
       $return = parent::__construct($user, $reference);
          
       if(!$return) {
            return false;
       }
         
    return $this;
   }    
   
   function isAgentur(){
        return true;       
    }
    
    
}
