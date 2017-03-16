<?php

namespace LK\Admin\Cron;

use LK\VKU\Data\VKUMaintenance;
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
      $manager = new VKUMaintenance($vku);
      $manager->removeVKU($vku);
    }

    // Removing orphaned VKU Data entries
    $dbq3 = db_query('SELECT d.vku_id, d.id FROM lk_vku_data d '
            . 'LEFT JOIN lk_vku v ON d.vku_id = v.vku_id '
            . 'WHERE v.vku_id IS NULL ORDER BY id DESC');
    while($all = $dbq3 -> fetchObject()){
      db_query('DELETE FROM lk_vku_data WHERE id=:id', [':id' => $all -> id]);
    }
  }
}
