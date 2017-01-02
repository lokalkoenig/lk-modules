<?php

namespace LK\Kampagne\Cron;

/**
 * Description of KampagnenPopularity
 *
 * @author Maikito
 */
class KampagnenPopularity {
  
  public static function executeCron(){
    
    $updates = array();
    $dbq = db_query("SELECT nid FROM node WHERE type='kampagne' ORDER BY RAND() LIMIT 50");
    foreach($dbq as $all){
        $node = node_load($all -> nid);
        $beliebt =  self::getPoularity($node);

        if($node -> field_kamp_beliebtheit['und'][0]['value'] != $beliebt){
            db_query("UPDATE field_data_field_kamp_beliebtheit SET field_kamp_beliebtheit_value='". $beliebt ."' WHERE entity_id='". $node -> nid ."'");
            $updates[] = $node -> nid;
        }
    }
  
    // Clear Cache of Modules
    if($updates){
      entity_get_controller('node')->resetCache($updates);
    }
  }
  
  public static function getPoularity(\stdClass $node){
    //  1 Punkt in History
    $points = 0;
   
    $dbq = db_query("SELECT count(*) as count FROM lk_lastviewed WHERE nid='". $node -> nid ."'");
    $res = $dbq -> fetchObject();
    $points += $res -> count;
  
    // 3 Punkte in VKU
    $count = get_nid_in_vku_count($node -> nid);
    $points += $count * 3;
  
    // 2 Punkte in Merkliste
    $dbq = db_query("SELECT count(*) as count FROM field_data_field_merkliste_node WHERE field_merkliste_node_nid='". $node -> nid ."'");
    $res = $dbq -> fetchObject();
    $points += $res -> count * 2; 
  
    // 10 Punkte Lizenz gekauft
    $dbq = db_query("SELECT count(*) as count FROM lk_vku_lizenzen WHERE nid='". $node -> nid ."'");
    $res = $dbq -> fetchObject();
    $points += $res -> count * 10; 
  
  return $points;
  }
}
