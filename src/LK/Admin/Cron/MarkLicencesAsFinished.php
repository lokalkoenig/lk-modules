<?php
namespace LK\Admin\Cron;

/**
 * Description of MarkLicencesAsFinished
 *
 * @author Maikito
 */
class MarkLicencesAsFinished {
  
  /**
   * Marks the VKU as purchased_done
   */
  public static function executeCron(){
    $date = time()  - 60*60*24*variable_get('lk_vku_max_download_time', 30);
  
    $dbq = db_query("SELECT vku_id FROM lk_vku WHERE vku_status='purchased' AND vku_purchased_date <='". $date  ."'");
    foreach($dbq as $item){
      $dbq2 = db_query("SELECT count(*) as count FROM lk_vku_lizenzen WHERE vku_id='". $item -> vku_id  ."' AND lizenz_until <='". time() ."'");
      $res = $dbq2 -> fetchObject();

      if($res -> count == 0){
        db_query("UPDATE lk_vku SET vku_status='purchased_done' WHERE vku_id='". $item -> vku_id ."'");
      }
    }
  }  
}
