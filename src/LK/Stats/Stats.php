<?php

namespace LK;

class Stats {
    
   
    /** Verlag - related */
    public static function logVerlagActiveUsers($user_id, $value){
        
        // log also activated users
        $verlag = \LK\get_user($user_id);
        if($verlag AND $verlag -> isVerlag()){
            self::log('verlag', $user_id, 'activated_users', $verlag -> getPeopleCount() + 1); 
        }
        
        self::log('verlag', $user_id, 'active_users', $value); 
    }
    
    public static function logVerlagAccessedKampagnen($user_id, $value){
        self::log('verlag', $user_id, 'accessed_kampagnen', $value); 
    }
   
    
    public static function logVerlagSearches($user_id, $value){
        self::log('verlag', $user_id, 'searches', $value); 
    }
   
    public static function logVerlagMerklisten($user_id, $value){
         self::log('verlag', $user_id, 'merklisten', $value); 
    }
    
    /** Team-related **/
    public static function logTeamSearches($team_id, $value){
        self::log('team', $team_id, 'searches', $value); 
    }
    
    public static function logTeamMerklisten($team_id, $value){
        self::log('team', $team_id, 'merklisten', $value); 
    }
    
    public static function logTeamActiveUsers($team_id, $value){
        
        $team = \LK\get_team($team_id);
        
        if($team){
            self::log('team', $team_id, 'activated_users', $team ->getUserActive_count()); 
        }
        
        self::log('team', $team_id, 'active_users', $value); 
    }
    
    public static function logTeamAccessedKampagnen($user_id, $value){
        self::log('team', $user_id, 'accessed_kampagnen', $value); 
    }
   
    
    /** User-related **/
    public static function logUserSearches($user_id, $value){
        self::log('user', $user_id, 'searches', $value); 
    }
    
    public static function logUserMerklisten($user_id, $value){
        self::log('user', $user_id, 'merklisten', $value); 
    }
    
    public static function logUserAccessedKampagnen($user_id, $value){
        self::log('user', $user_id, 'accessed_kampagnen', $value); 
    }
    
    private static function __logVku(\VKUCreator $vku, $key){
       $user = \LK\get_user($vku ->getAuthor());
       self::log("user", $user ->getUid(), $key);
      
       if($team = $user ->getTeam()){
            self::log("team", $team, $key);
       }
        
       if($verlag = $user ->getVerlag()){
            self::log("verlag", $verlag, $key);
       } 
    }


    static function countVKU(\VKUCreator $vku){
        self::__logVku($vku, "created_vku");
    }
    
    static function countGeneratedVKU(\VKUCreator $vku){
        self::__logVku($vku, "generated_vku");
    }
    
    static function countPurchasedVKU(\VKUCreator $vku){
        self::__logVku($vku, "purchased_vku");
    }
   
    private static function __get_id($bundle, $user_id, $month_select = null){
        
        $month = date("Y-m");
        
        if($month_select){
            $month = $month_select;
        }
        
        $where = array("stats_user_type='" .$bundle ."'");
        $where[] = "stats_bundle_id='". $user_id ."'";
        $where[] = "stats_date='". $month ."'";
        
        $dbq = db_query("SELECT id FROM lk_verlag_stats "
                . "WHERE " . implode(" AND ", $where));
        $test = $dbq -> fetchObject();
        
        if(!$test){
            // create record
            
            $verlag_uid = 0;
            $team_id = 0;
            
            if($bundle == 'user'){
                 $user = get_user($user_id);
                 if($user):
                    $team_id = (int)$user ->getTeam();
                    $verlag_uid = (int)$user ->getVerlag();    
                 endif;
            }
            
            if($bundle == 'team'){
                $team = get_team($user_id);
                
                if($team):
                    $verlag_uid = (int)$team ->getVerlag();
                endif;   
            }       
             
            
                     
            $id = db_insert('lk_verlag_stats') // Table name no longer needs {}
                ->fields(array(
                            "user_stats_verlag_uid" => $verlag_uid,
                            "user_stats_team_id" => $team_id,
                            "stats_user_type" => $bundle, 
                            "stats_bundle_id" => $user_id, 
                            "stats_date" => $month))
                ->execute();
            
        }
        else {
            $id = $test -> id;
        } 
        
        
    return $id;    
    }
    
    
    public static function getLastEntry($bundle, $user_id, $month = null){
        
        $id = self::__get_id($bundle, $user_id, $month);
        
        if($id){
            $dbq = db_query("SELECT * FROM lk_verlag_stats WHERE id='". $id ."'");
            return $dbq -> fetchObject();
        }
        
    return false;    
    }
    
    static function getLogMonthes($bundle, $user_id){
        $where = array();
        $where = array("stats_user_type='" .$bundle ."'");
        $where[] = "stats_bundle_id='". $user_id ."'";
        
        $monthes = array();
        $dbq = db_query("SELECT DISTINCT stats_date FROM lk_verlag_stats WHERE " . implode(" AND ", $where) . " ORDER BY stats_date DESC");
        foreach($dbq as $all){
            $monthes[] = $all -> stats_date;
        }
        
    return $monthes;     
    }
    
    public static function logOverall($key, $value){
        self::log("lk", 0, $key, $value);
    }
   
    
    
    private static function log($bundle, $user_id, $key, $value = null){
        $id = self::__get_id($bundle, $user_id);
        
        // count one up
        if($value === null){
           db_query("UPDATE lk_verlag_stats SET " . $key . "=" . $key . "+1 WHERE id='". $id ."'");
        }
        else {
           db_query("UPDATE lk_verlag_stats SET " . $key . "='". $value ."' WHERE id='". $id ."'");
        }
    }
}

