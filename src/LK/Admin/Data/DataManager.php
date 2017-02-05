<?php

namespace LK\Admin\Data;

/**
 * Description of DataManager
 *
 * @author Maikito
 */
class DataManager {

  var $modules = [];
  var $kampagne_can_be_removed = FALSE;

  function __construct() {

    foreach (module_implements('lk_data_management') as $module) {
      $function = $module . '_lk_data_management';
      $addition = $function();

      if($addition){
        $this->modules += $addition;
      }
    }
  }
  
  /**
   * Blocks a Kampagne to get removed
   */
  function blockKampagne(){
    $this->kampagne_can_be_removed = FALSE;
  }

  /**
   * Loads a Module
   *
   * @param string $classname
   * @return \LK\Admin\Interfaces\DataManager
   */
  private function loadModule($classname){
    $obj = new $classname($this);
    return $obj;
  }

  /**
   * Aggregates the Kampagnen-Stats
   *
   * @param \LK\Kampagne\Kampagne $kampagne
   * @return array
   */
  function getKampagneStats(\LK\Kampagne\Kampagne $kampagne){

    $stats = [];
    foreach($this -> modules as $module){
      $obj = $this->loadModule($module);
      $stats += $obj ->getKampagnenCount($kampagne);
    }

    return $stats;
  }
}
