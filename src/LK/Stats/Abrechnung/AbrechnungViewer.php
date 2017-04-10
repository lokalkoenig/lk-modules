<?php

namespace LK\Stats\Abrechnung;

/**
 * Description of LK
 *
 * @author Maikito
 */
class AbrechnungViewer {

  private $options = [];

  private function setOptions($options) {
    $this->options = $options;
  }

  private function getSelectedMonth() {
    
  }

  public function renderLKOverall() {

    $options = array();
    $dbq = db_query("SELECT
        YEAR(FROM_UNIXTIME(lizenz_date)) as year,
        COUNT(*) as count
        FROM lk_vku_lizenzen
        WHERE lizenz_verlag_uid != '0'
        GROUP BY YEAR(FROM_UNIXTIME(lizenz_date))
        ORDER BY year DESC");
     foreach($dbq as $all){
        $options[] = $all;

        $dbq2 = db_query("SELECT
          MONTH(FROM_UNIXTIME(lizenz_date)) as month,
          COUNT(*) as count
          FROM lk_vku_lizenzen
          WHERE lizenz_verlag_uid != '0' AND YEAR(FROM_UNIXTIME(lizenz_date)) = '". $all -> year ."'
          GROUP BY MONTH(FROM_UNIXTIME(lizenz_date))
          ORDER BY month DESC");
        foreach($dbq2 as $all2){
           $all2 -> year = $all -> year;
           $options[] = $all2;
        }
     }
    
     $this->setOptions($options);
     
     



  }


}
