<?php

namespace LK\Kampagne\Cron;

/**
 * Description of KampagnenPopularity
 * Updates Popularity of Node
 *
 * @author Maikito
 */
class KampagnenPopularity {

  /**
   * Runs the cron
   */
  public static function executeCron(){
    
    $updates = array();
    $dbq = db_query("SELECT nid FROM node WHERE type='kampagne' AND status='1' ORDER BY RAND() LIMIT 50");
    foreach($dbq as $all){
        $node = node_load($all -> nid);
        $beliebt =  self::getPoularity($node);
        $value = 0;

        if(isset($node -> field_kamp_beliebtheit['und'][0]['value'])){
          $value = $node -> field_kamp_beliebtheit['und'][0]['value'];
        }

        if($value != $beliebt){
            db_query("UPDATE field_data_field_kamp_beliebtheit SET field_kamp_beliebtheit_value='". $beliebt ."' WHERE entity_id='". $node -> nid ."'");
            $updates[] = $node -> nid;
        }
    }
  
    // Clear Cache of Modules
    if($updates){
      entity_get_controller('node')->resetCache($updates);
    }
  }
  
  /**
   * Gets the Popularity points of a node
   *
   * @param \stdClass $node
   * @return int
   */
  public static function getPoularity(\stdClass $node){
    //  1 Punkt in History
    $points = 0;
   
    $dbq = db_query("SELECT count(*) as count FROM lk_lastviewed WHERE nid='". $node -> nid ."'");
    $res = $dbq -> fetchObject();
    $points += $res -> count;
  
    // 3 Punkte in VKU
    $count = \LK\VKU\VKUManager::getNidInVKUCount($node -> nid);
    $points += $count * 3;
  
    // 2 Punkte in Merkliste
    $manager = new \LK\Merkliste\AdminMerkliste();
    $count = $manager->getGeneralKampagnenCount($node -> nid);
    $points += $count * 2; 
  
    // 10 Punkte Lizenz gekauft
    $dbq = db_query("SELECT count(*) as count FROM lk_vku_lizenzen WHERE nid='". $node -> nid ."'");
    $res = $dbq -> fetchObject();
    $points += $res -> count * 10; 
  
  return $points;
  }
}
