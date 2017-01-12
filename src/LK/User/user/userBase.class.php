<?php
namespace LK;


class User {
    
    var $uid = 0;
    var $name = null;
    var $mail = null;
    
    var $manager = null;
    var $telefon = false;
    var $team = 0;
    var $verlag = 0;
    var $user_data = null;
    var $user_role = 0;
    var $profile = null;
    var $testverlag = 0;
    var $role_name = 'user';
    var $title;
    
    
    private $current_ausgaben = array();
    
    function __construct($user, $reference) {
        $this -> manager = $reference;
        $this -> uid = $user -> uid;
        $this -> name = $user -> name;
        $this -> mail = $user -> mail;
        return $this -> fetchUser($user);
    }
    
    function getRole(){
        return $this -> role_name;
    }
    
    function getUsername(){
        return $this ->name;
    }
    
    function getCreated(){
        return $this -> user_data -> created;
    }
    
    function getLastAccess(){
        return $this -> user_data -> access;
    }
    
    
    function getStatus(){
        return $this -> user_data -> status;
    }
    
    function getVerlag(){
        return false;
    }
    
    function isVerlag(){
        return false;
    }
    
    function isMitarbeiter(){
        return false;
    }
    
    function isModerator(){
        return false;
    }
    
    function isAgentur(){
        return false;       
    }
    
    function getTeam(){
        return $this -> team;
    }
    
    function getTeamObject(){
        if($this -> team){
            return get_team($this  -> team);
        }
        
    return false;    
    }
    
    /**
     * Gets the Verlag-Object
     * 
     * @return \LK\Verlag|boolean
     */
    function getVerlagObject(){
        
        if($this ->isVerlag()){
            return $this;
        }
        
        if($verlag = $this -> getVerlag()){
            return get_user($verlag);
        }
        
    return false;    
    }
    
    function isVerlagController(){
        return false;   
    }
    
    public function getUid(){
        return $this -> uid;
    }
    
    function isTelefonmitarbeiter() {
        return $this -> telefon;
    }
    
    function isTeamleiter(){
        return false;
    }
    
    function isTestAccount(){
        return false;
    }
    
    function getTitle(){
        return $this -> user_data -> name . " / " . ucfirst($this -> role_name);
    }
    
    function __toString(){
       
        
        
        if($this -> title){
            return $this -> title;
        }
        
        $this -> title .= $this ->getUsername();
        
        $current = current();
        
        if($current -> hasRight('profile access') AND $this -> getVerlag()){
           $this -> title = l($this -> title, 'user/' . $this ->getUid()); 
        }
      
        $status = $this ->getStatus();
        if(!$status){
            $this -> title = '<strike title="Deaktivierter Nutzer">' . $this -> title . '</strike>';
        }
        
        $this -> title .= ' <sup>';
        
        if($this ->isModerator()){
           $this -> title .= ' <span class="label label-warning"><span class="glyphicon glyphicon-plus"></span> Lokalkönig Support</span>'; 
        }
        elseif(!$this ->isMitarbeiter()){
            $this -> title .= ' <span class="label label-primary">'. ucfirst($this ->getRole()) . '</span>'; 
        }
        
        else {
          if($this ->isTelefonmitarbeiter()){
            $this -> title .= '<span class="glyphicon glyphicon-phone" title="Telefonmitarbeiter"></span>';
          }
     
          if($this ->isTeamleiter()){
                $this -> title .= ' <span class="glyphicon glyphicon-tower" title="Verkaufsleiter"></span>';
          }
          
          if($current -> hasRight('profile access')):
            // GET ausgaben
            $ausgaben = $this ->getCurrentAusgaben();
            if($ausgaben){

              if(count($ausgaben) > 3){
                $this -> title .= ' <span class="label label-primary" title="'. count($ausgaben) .' Ausgaben">'. count($ausgaben) .'</span>';  
              }  
              else {
                  $this -> title .= $this -> getAusgabenFormatted();  
               }
            } 
          
          endif;
        }
        
        $this -> title .= '</sup>';
        
        return $this -> title;
    }
    
    function getInfoUrl(){
        return 'user/' . $this -> getUid() . "/info";
    }
    
    
    function getAusgabenFormatted(){
        $ausgaben = $this ->getCurrentAusgaben();
        
        $return = ' <span class="user-ausgaben">';  
        foreach($ausgaben as $item){
           $ausgabe = get_ausgabe($item);
           $return .= $ausgabe ->getTitleFormatted();
        }
        $return .= '</span>';   
        
    return $return;    
    }
    
    function getCurrentAusgaben(){
        
        if($this -> current_ausgaben){
            return $this -> current_ausgaben;
        }
        
        if($this ->isMitarbeiter() OR $this -> isVerlag() OR $this ->isVerlagController()){
            $ausgaben = array(); 
            
            if(isset($this->profile['mitarbeiter']->field_ausgabe['und'])){
                   foreach($this->profile['mitarbeiter']->field_ausgabe['und'] as $ausgabe){
                        $ausgaben[] = $ausgabe["target_id"];
                    }
                }   
        
            $this -> current_ausgaben = $ausgaben;        
        }
        
        return $this -> current_ausgaben; 
    }
    
    function setAusgaben($new_ausgaben){
        // TODO: check if Ausgaben matches Verlag
        
        if($this -> isVerlag() OR $this ->isMitarbeiter() OR $this ->isVerlagController()){
            $current = $this ->getCurrentAusgaben();
            
            // Only check if there is a Difference
            if($current != $new_ausgaben){
                $newvalue = array();    
                
                foreach($new_ausgaben as $ausgabe){
                    $newvalue[]["target_id"] = $ausgabe;  
                }    
                
                $this -> profile["mitarbeiter"] -> field_ausgabe["und"] = $newvalue;   
                profile2_save($this -> profile["mitarbeiter"]);
            }
        }
    }
    
   /**
    * Save User-Information in Class
    * 
    * @param Object $user
    * @return boolean
    * 
    */
   function fetchUser($user){
       if(is_object($user)){
             $this -> uid = $user -> uid;
             $this -> user_data = $user;
             $this -> profile = profile2_load_by_user($user);
             return $this;
       } 
       else {
          return false;  
       }
   } 
   
   
   function getPicture(){
       if(isset($this -> profile['main']->field_profile_bild['und'][0]['uri'])){
         $uri = $this -> profile['main']->field_profile_bild['und'][0]['uri'];   
         return theme('image_style', array('style_name' => "avatar", "path" => $uri));
       } 
   }
   
   
   function getUrl(){
       return 'user/' . $this -> getUid();
   }
   
   
   function getRoleLong(){
       return $this -> role_name;
   }
   
   /**
    * Rights, to be defined, return is false
    * 
    * @param String $key
    * @return boolean
    */
   function hasRight($key){
       return false;
   }
   
   function dashboardUrl(){
       return $this ->getUrl() . "/dashboard";
   }   
}

