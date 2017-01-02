<?php

namespace LK\Kampagne\Cron;

/**
 * Description of RemoveSperren
 *
 * @author Maikito
 */
class RemoveSperren {
  //put your code here
  
  static function executeCron(){
    $manager = new \LK\Kampagne\SperrenManager();
    $manager ->checkOutDatedSperren();
  }  
}
