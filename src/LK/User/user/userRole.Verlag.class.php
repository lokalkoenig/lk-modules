<?php
namespace LK;

/**
 * Tage wie lange eine Warnmeldung angezeigt werden soll
 */
define("VERLAG_SHOW_WARNING_USAGE_VKU", 10);


class Verlag extends User {
   var $user_role = LK_USER_VERLAG;
   var $ausgaben = array();
   var $role_name = 'verlag';
   var $settings = [];
   var $teams = array();
   
   function __construct($user, $reference){
      $return = parent::__construct($user, $reference);

      $settings = new \LK\User\Settings\Manager($this);
      $this->settings = $settings->getVars();
      
      return $return;
   }
   
   function isTelefonmitarbeiter(){
       return true;
   }
   
   function getPlzSimplyfied(){
       return \plz_simplyfy($this ->  profile["verlag"] -> field_plz_sperre["und"]);
   }
   
   function getPlzFormatted(){
       
       $plain = $this -> profile["verlag"] -> field_plz_sperre["und"];
       
       $plz = array();
       foreach($plain as $i){
         $tax = taxonomy_term_load($i["tid"]);
         $plz[] = $tax -> name;
       }
       
       return implode(", ", $plz);
   }
   
   
   function getPeopleCount($active = 1){
      
      $dbq = db_query('SELECT count(*) as count FROM '
              . 'field_data_field_mitarbeiter_verlag f, users u, profile p '
              . "WHERE p.uid=u.uid AND f.entity_id=p.pid AND f.deleted='0' AND"
              . " u.status='". $active."' AND "
              . " f.field_mitarbeiter_verlag_uid='". $this -> getUid() ."'");
      $all = $dbq -> fetchObject();
      
      return $all -> count;
   }
   
   /**
    * 
    * @param type $id
    * @param type $default_value
    * @param string $value_name
    * @return type
    */
   function getVerlagSetting($id, $default_value = NULL, $value_name = 'value'){

     if(isset($this->settings[$id])){
       return $this->settings[$id];
     }

     $setting_name = 'field_' . $id;
     $values = (array)$this->profile['verlag'];
       
     if(isset($values[$setting_name]['und'][0][$value_name])){
      return $values[$setting_name]['und'][0][$value_name];
     } 
     else {
      return $default_value;
     }       
   }
   
   /**
    * Gets Verlag-Settings (Multiple Values)
    * 
    * @param type $id
    * @param type $default_value
    */
   function getVerlagSettingMultiple($id, $default_value = []){
    $setting_name = 'field_' . $id;
    $values = (array)$this->profile['verlag'];
    
    if(isset($values[$setting_name]['und'])){
      return $values[$setting_name]['und'];  
    } 
    else {
      return $default_value;
    }       
   }
   
   /**
    * Protokoll
    * 
    * @return Int
    */
   function showProtokoll(){
       return $this ->getVerlagSetting('anzeige_des_ma_protokolls', 1);
   }
   
   /**
    * Gets back an Array of the Active Accounts
    * 
    * @return Array
    * 
    */
   function getActiveUsers(){
      $verlag_id = $this -> getUid();
      $accounts = array();
      
       $dbq = db_query("SELECT p.uid FROM field_data_field_mitarbeiter_verlag v, profile p, users u WHERE
                v.entity_id = p.pid AND p.uid=u.uid AND u.status='1' AND v.field_mitarbeiter_verlag_uid = '". $verlag_id ."' AND p.uid IS NOT NULL");
        foreach($dbq as $all){
            $accounts[] = $all -> uid;
        }
   
   return $accounts;    
   }
   
   
   function getAllVerkaufsleiter(){
       $teams = $this ->getTeams();
       $user = array();
       
       foreach($teams as $team){
           $leiter = $team -> getLeiter();
           $user[] = $leiter; 
       }
   
   return $user;    
   }
   
   /**
    * Gets back an Array of the Active Accounts
    * 
    * @return Array
    * 
    */
   function getAllUsers(){
      $verlag_id = $this -> getUid();
      $accounts = array();
      
       $dbq = db_query("SELECT p.uid FROM field_data_field_mitarbeiter_verlag v, profile p, users u WHERE
                v.entity_id = p.pid AND p.uid=u.uid AND v.field_mitarbeiter_verlag_uid = '". $verlag_id ."' AND p.uid IS NOT NULL");
        foreach($dbq as $all){
            $accounts[] = $all -> uid;
        }
   
   return $accounts;    
   }
   
   
   function getMitarbeiter(){
     $verlag_id = $this -> getUid();
     $accounts = array();
      
     $dbq = db_query("SELECT p.uid FROM field_data_field_mitarbeiter_verlag v, profile p, users u, users_roles r WHERE
                v.entity_id = p.pid AND p.uid=u.uid AND v.field_mitarbeiter_verlag_uid = '". $verlag_id ."' "
             . "AND u.uid=r.uid AND r.rid='6' "
             . "AND p.uid IS NOT NULL");
        foreach($dbq as $all){
            $accounts[] = $all -> uid;
        }
   
   return $accounts;    
       
   }
   
   function getVerlagscontroller(){
     $verlag_id = $this -> getUid();
     $accounts = array();
      
     $dbq = db_query("SELECT p.uid FROM field_data_field_mitarbeiter_verlag v, profile p, users u, users_roles r WHERE
                v.entity_id = p.pid AND p.uid=u.uid AND v.field_mitarbeiter_verlag_uid = '". $verlag_id ."' "
             . "AND u.uid=r.uid AND r.rid='8' "
             . "AND p.uid IS NOT NULL");
        foreach($dbq as $all){
            $accounts[] = $all -> uid;
        }
   
   return $accounts;   
       
   }
   
   function getVerlagColor($color_name, $default_value = NULL){
     return $this ->getVerlagSetting($color_name, $default_value, 'jquery_colorpicker'); 
   }
   
   /**
    * 
    * @return boolean
    */
   function isVerlag() {
       return true;
   }
   
    /**
     * 
     * @return Array Ausgaben
     */
    function getAusgaben(){
        
        if(!$this -> ausgaben) {
            $verlag_id = $this -> getUid();
        
            $dbq = db_query("SELECT entity_id FROM field_data_field_verlag "
                . " WHERE bundle='ausgabe' AND "
                . " field_verlag_uid='". $verlag_id ."'");
            foreach($dbq as $all){
                $ausgabe = $this -> manager -> getAusgabe($all -> entity_id);
                if($ausgabe){
                   $this -> ausgaben[$all -> entity_id] = $ausgabe; 
                }
            }
        }
    
        
   return $this -> ausgaben;     
   }
   
  
   
   function getTeams(){
        if(!$this -> teams) {
            $verlag_id = $this -> getUid();
        
            $dbq = db_query("SELECT entity_id FROM field_data_field_verlag "
                . " WHERE bundle='team' AND "
                . " field_verlag_uid='". $verlag_id ."'");
            foreach($dbq as $all){
                
               
                $team = $this -> manager -> getTeam($all -> entity_id);
               
                if($team){
                   $this -> teams[$all -> entity_id] = $team; 
                }
            }
        }
        
   return $this -> teams;
       
   }
   
   
   function getVerlag(){
       return $this -> getUid();
   }
   
   function isTestAccount(){
       if(isset($this -> profile["verlag"]->field_testverlag['und'][0]['value'])){
         return $this -> profile["verlag"]->field_testverlag['und'][0]['value'];  
       }
       
       return false;
   }   
   
   function getPicture() {
      if($this->profile['verlag']->field_verlag_logo['und'][0]['uri']): 
        $logo = $this->profile['verlag']->field_verlag_logo['und'][0]['uri'];
        return theme('image_style', array('style_name' => "verlags-logos-klein", "path" => $logo));
      endif;
   }
   
   function hasRight($key) {
       
       $array = array("add ausgabe", "edit ausgabe", "edit plz");
       
       if(in_array($key, $array)){
           return false;
       }
       
       return true;
   }
}