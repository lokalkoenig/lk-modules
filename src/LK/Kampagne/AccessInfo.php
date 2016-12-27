<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Kampagne;

/**
 * Description of KampagnenAccess
 *
 * @author Maikito
 */
class AccessInfo {
    //put your code here
    
    static public function loadAccess($nid){
    global $user;    
    
        // Anonymous user
        if(!$user -> uid){
            return false;
        }

        $account = \LK\get_user($user -> uid);
    
        $ausgaben = $account -> getCurrentAusgaben();
        if(!$ausgaben){
            return true;
        }
    
        $dbq = db_query("SELECT count(*) as count FROM na_node_access_ausgaben_time WHERE nid='". $nid ."' AND aid IN (". implode(",", $ausgaben) .")");
        $all = $dbq -> fetchObject();
    
        if($all -> count > 0){
            return false;
        }
    
    return true;    
    }
    
    
    /**
     * Has Access to put the Node into VKU,
     * can be used once Node is load
     * 
     * @global type $user
     * @param $nid
     * @return boolean
     */
    static public function hasAccess($nid){
    global $user;
      
       if(lk_is_agentur()){
          return false;
        }
  
        $node = node_load($nid);
    
        if($node -> plzaccess == false){
         return true;
        }
  
        if($node -> plzaccess == true) {
           $result = self::getAccessCount($nid, $user);
           if($result){
               return true;
           }
        }
  
    return false;          
    }
    
    static public function getAccessCount($nid, $account){
        $where = array(1);
        
        if(lk_is_agentur()){
          return false;
        }
  
        $days = 100;   
        $verlag = \LK\get_verlag_id($account);
        if($verlag){
           $verlag_object = \LK\get_user($verlag);
           $days = $verlag_object -> getVerlagSetting("sperrung_vku_hinweis", 10);
           $where[] = "v.verlag_uid='". $verlag ."'";    
           $where[] = "v.uid != '". $account -> uid ."'";    
        }
          
        $time = time() - (60*60*24*$days);
        $where[] = "v.vku_changed >= '". $time ."'";
  
       $count = get_nid_in_vku_count($nid, array("active", "created", "downloaded", "ready"), $where);
   
    return $count;
    }
    
    static public function getAccessInfo($nid){
    global $user;
        
        $node = node_load($nid);
        $verlags_sperre = \get_verlag_plz_sperre($nid, true);
        $output = array();
   
        if($node -> plzaccess == false AND !$verlags_sperre){
            $test = (get_ausgaben_access_nid($nid, $user));
            if($test && $test["count"]){
                $test["account"] = $user;
                $test["class"] = 'well clearfix';
                $output[] = \theme("lk_vku_lizenz_usage", $test);
            }
        }
        elseif($node -> plzaccess == true OR $verlags_sperre) {
          // Checken ob andere User VKU's mit der Kampagne erstellt haben
          $result = vku_get_use_count($nid, $user);

          if($result){
              $output[] = theme("lk_vku_usage", array('class' => 'clearfix', "account" => $user, "entries" => vku_get_use_details($nid, $user)));
          }
        } 
    
        if(!$output){
           $output[] = 'Keine Informationen verfügbar';
        }
  
    return implode("", $output);
    }   
    
    
    
    static public function getUserDetails($nid, $account){
    
        if(lk_is_agentur($account)) {
             return false;
        }
        
        $where = array(); 
        $where[] = "n.data_entity_id='". $nid ."'";
        $where[] = "n.data_class='kampagne'";
        
        $verlag = \LK\get_verlag_id($account);
        
        // set the days we want to have past information
        $days = 100;
        
        if($verlag){
            $verlag_object = \LK\get_user($verlag);
            $days = $verlag_object -> getVerlagSetting("sperrung_vku_hinweis", 10);
            
            $where[] = "v.verlag_uid='". $verlag ."'";    
            $where[] = "v.uid != '". $account -> uid ."'";    
        }
                
        $time = time() - (60 * 60 * 24 * $days);
        $items = array();
     
        $dbq = db_query("SELECT v.vku_id, v.uid, v.vku_changed as date_added, v.vku_title 
        FROM lk_vku v 
          LEFT JOIN lk_vku_data n 
          ON (n.vku_id=v.vku_id) 
        WHERE
        v.vku_status IN ('active', 'created', 'downloaded', 'ready') 
          AND ". implode(" AND ", $where) ." AND  v.vku_changed >= '". $time ."'
          ORDER BY v.vku_changed DESC");
  
        foreach($dbq as $all){
          $all -> ausgaben = \vku_get_plz_bereiche($all -> vku_id);  
          $items[] = $all;
        }

    return $items;
    }
    
}

