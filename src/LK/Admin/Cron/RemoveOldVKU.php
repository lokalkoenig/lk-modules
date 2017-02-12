<?php

namespace LK\Admin\Cron;
use VKUCreator;


/**
 * Description of RemoveOldVKU
 *
 * @author Maikito
 */
class RemoveOldVKU {
  
  /**
   * Removes old VKU from the Database
   */
  public static function executeCron(){

    $manager = new \LK\VKU\Data\VKUMaintenance();
    
    $time = time() - (60*60*24*31); // vor einem Monat
    $vkus = [];
    $dbq = db_query("SELECT vku_id, vku_changed "
            . "FROM lk_vku "
            . "WHERE vku_status='deleted' AND ((vku_created < '". $time ."' AND vku_changed < '". $time ."') OR vku_changed IS NULL) ORDER BY vku_id DESC");
    foreach($dbq as $all){
      $vkus[] = $all->vku_id;
    }

    $time2 = time() - (60*60*24);
    $dbq2 = db_query("SELECT vku_id, vku_changed "
            . "FROM lk_vku "
            . "WHERE vku_status='new' AND vku_changed < '". $time2 ."' ORDER BY vku_id DESC");
    foreach($dbq2 as $all){
      $vkus[] = $all->vku_id;
    }

    foreach($vkus as $vku_id){
      $vku = new VKUCreator($vku_id);
      $manager->removeVKU($vku);
    }
  }  
}
