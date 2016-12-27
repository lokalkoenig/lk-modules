<?php

function _vku_user_stats_generate(){
    // Log searches
    // Log merklisten
    
    $time_from = strtotime(date("Y-m-01") . "-01 00:00:00");
    $time_until = strtotime(date("Y-m-t 23:59:59", ($time_from)));
    
    $dbq = db_query("SELECT uid FROM users WHERE access >= '". $time_from ."'");
    foreach($dbq as $all):
        
         $query = new EntityFieldQuery();
         $query->entityCondition('entity_type', 'merkliste');
         $query -> entityCondition('bundle', 'merkliste');

         $query -> propertyCondition('created', $time_from, ">=");
         $query -> propertyCondition('created', $time_until, "<=");
         
         $query -> propertyCondition('uid', $all -> uid);
         $query -> count(); 
         $result = $query->execute();
        
         \LK\Stats::logUserMerklisten($all -> uid, (int)$result);
        
         $dbq2 = db_query("SELECT count(*) as count FROM lk_search_history WHERE uid='". $all -> uid ."' AND created	>= '". $time_from ."' AND created	<= '". $time_until ."'");
         $all2 = $dbq2 -> fetchObject();
         \LK\Stats::logUserSearches($all -> uid, (int)$all2 -> count);
         
         
         // Kampagnen
         $dbq3 = db_query("SELECT count(*) as count FROM lk_lastviewed WHERE uid='". $all -> uid ."' AND lastviewed_time >= '". $time_from ."' AND lastviewed_time <= '". $time_until ."'");
         $all3 = $dbq3 -> fetchObject();
         \LK\Stats::logUserAccessedKampagnen($all -> uid, (int)$all3 -> count);
         
    endforeach;   
    
    _vku_team_stats_generate();
    _vku_verlag_stats_generate();
    _vku_general_stats_generate();
}

function _vku_team_stats_generate(){
    $month = date("Y-m");
    
    $dbq = db_query("SELECT DISTINCT user_stats_team_id FROM lk_verlag_stats WHERE user_stats_team_id != 0 AND stats_date='". $month ."'");
    foreach($dbq as $all){
        $team_id = $all -> user_stats_team_id;
        
        $dbq1 = db_query("SELECT sum(merklisten) as count FROM lk_verlag_stats WHERE user_stats_team_id='". $team_id ."' AND stats_date='". $month ."'");
        $result1 = $dbq1 -> fetchObject();
        \LK\Stats::logTeamMerklisten($team_id, (int)$result1 -> count);
            
        $dbq2 = db_query("SELECT sum(searches) as count FROM lk_verlag_stats WHERE user_stats_team_id='". $team_id ."' AND stats_date='". $month ."'");
        $result2 = $dbq2 -> fetchObject();
        \LK\Stats::logTeamSearches($team_id, (int)$result2 -> count);
            
        $dbq3 = db_query("SELECT count(*) as count FROM lk_verlag_stats WHERE user_stats_team_id='". $team_id ."' AND stats_date='". $month ."'");
        $result3 = $dbq3 -> fetchObject();
            
        \LK\Stats::logTeamActiveUsers($team_id, (int)$result3 -> count);
        
        //accessed_kampagnen
        $dbq4 = db_query("SELECT sum(accessed_kampagnen) as count FROM lk_verlag_stats WHERE user_stats_team_id='". $team_id ."' AND stats_date='". $month ."'");
        $result4 = $dbq4 -> fetchObject();
        \LK\Stats::logTeamAccessedKampagnen($team_id, (int)$result4 -> count);
        
    }
}


function _vku_verlag_stats_generate(){
    
    
      $query = 'SELECT DISTINCT(ur.uid) 
        FROM {users_roles} AS ur
        WHERE ur.rid IN (:rids)';
      $result = db_query($query, array(':rids' => array(5)));
      
       $month = date("Y-m");
       $uids = $result->fetchCol();
      
       foreach($uids as $uid):
            $account = user_load($uid);
            
            if(!$account -> status):
                continue;
            endif;   
           
            $dbq1 = db_query("SELECT sum(merklisten) as count FROM lk_verlag_stats WHERE user_stats_verlag_uid='". $uid ."' AND stats_date='". $month ."'");
            $result1 = $dbq1 -> fetchObject();
            \LK\Stats::logVerlagMerklisten($uid, (int)$result1 -> count);
            
            $dbq2 = db_query("SELECT sum(searches) as count FROM lk_verlag_stats WHERE user_stats_verlag_uid='". $uid ."' AND stats_date='". $month ."'");
            $result2 = $dbq2 -> fetchObject();
            \LK\Stats::logVerlagSearches($uid, (int)$result2 -> count);
            
            $dbq3 = db_query("SELECT count(*) as count FROM lk_verlag_stats WHERE user_stats_verlag_uid='". $uid ."' AND stats_date='". $month ."'");
            $result3 = $dbq3 -> fetchObject();
            \LK\Stats::logVerlagActiveUsers($uid, (int)$result3 -> count);
            
            //accessed_kampagnen
            $dbq4 = db_query("SELECT sum(accessed_kampagnen) as count FROM lk_verlag_stats WHERE stats_user_type='user' AND user_stats_verlag_uid='". $uid ."' AND stats_date='". $month ."'");
            $result4 = $dbq4 -> fetchObject();
            
            \LK\Stats::logVerlagAccessedKampagnen($uid, (int)$result4 -> count);
    
        endforeach;
}
    
    
function _vku_general_stats_generate(){
    $month = date("Y-m");
    $measurements = array('active_users', "created_vku", "generated_vku", "purchased_vku", "merklisten", "searches", "accessed_kampagnen", "activated_users");
    
    foreach($measurements as $item):
        $dbq = db_query("SELECT sum(". $item .") as count FROM lk_verlag_stats WHERE stats_bundle_id !='". LK_TEST_VERLAG_UID ."' AND stats_user_type='verlag' AND stats_date='". $month ."'");
        $result = $dbq -> fetchObject();
        \LK\Stats::logOverall($item, (int)$result -> count);
    endforeach;    
    
    
    
}
