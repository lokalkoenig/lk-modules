<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Log;

/**
 * Description of Convert
 *
 * @author Maikito
 */
class Convert {
    //put your code here
    
    public static function convertNodeLog(){
        
        $dbq = db_query('SELECT * FROM lokalkoenig_log ORDER BY id ASC');
        foreach($dbq as $all){
            
            if($all -> nid){
                $log = new Kampagne($all -> nid, $all -> title);
                $log ->set("request_time", $all -> createdate);
                $log ->set("uid", $all -> uid);
                $log ->context = [];
                $log ->save();
            }            
        }
    }
    
     public static function generalLog(){
         
        $dbq = db_query('SELECT * FROM  lk_common_log ORDER BY id ASC');
        foreach($dbq as $all){
            
            if($all -> 	log_category == "internal"){
                continue;
            }
            
            $log = new Debug($all -> log_category .': '. $all -> log_message);
            $log -> set("request_time", $all -> log_date);
            $log -> set("uid", $all -> uid);
            $log ->context = [];
            $log -> save();
        }
     }    
     
      public static function verlagLog(){
    
        $dbq = db_query('SELECT * FROM  lk_verlag_log ORDER BY log_id ASC');
        foreach($dbq as $all){
            
            $log = new Debug($all -> log_message);
            $log -> set("category", 'verlag');
            
            $log -> set("request_time", $all -> log_date);
            $log -> set("uid", $all -> log_uid);
            $log -> set("verlag_uid", $all -> log_verlag_uid);
            $log -> set("vku_id", $all -> vku_id);
            $log -> set("node_nid", $all -> nid);
            $log -> set("team_id", $all -> log_team);
            
            $log ->context = [];
            $log -> save();
        }
     }    
     
     
}
