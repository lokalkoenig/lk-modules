<?php

namespace LK\Admin\Interfaces;

/**
 * Interface for User & Kampagnen Maintenance
 *
 * @author Maikito
 */
abstract class DataManager {

  use \LK\Log\LogTrait;
  var $manager = null;

  /**
   * Removes the Data when the Node is set Offline
   *
   * @return boolean
   */
  function removeOnDisableKampagne(){
    return false;
  }

  protected function count($table, array $where){

    $arr = [];
    foreach($where as $key => $item) {
        $arr[] = $key."='". $item. "'";
    }

    $dbq = db_query("SELECT count(*) as count FROM " . $table . " WHERE " . implode(' AND ', $arr));
    return $dbq->fetchObject()->count;
  }

  /**
   * Constructor
   *
   * @param \LK\Admin\Data\DataManager $manager
   */
  final function __construct(\LK\Admin\Data\DataManager $manager) {
    $this->manager = $manager;
  }

  /**
   * Get the Data-Manager
   *
   * @return \LK\Admin\Data\DataManager
   */
  final function getManager(){
    return $this->manager;
  }


  /**
   * Removes the User-Data
   * 
   * @param \LK\User $acccount
   */
  function removeUserData(\LK\User $acccount){
    return [];
  }

  /**
   * Get User-Data
   *
   * @param \LK\User $acccount
   * @return int
   */
  function getUserDataCount(\LK\User $acccount){
    return [];
  }

  /**
   * Get the count of a Node
   *
   * @param \LK\Kampagne\Kampagne $kampagne
   * @return array
   */
  function getKampagnenCount(\LK\Kampagne\Kampagne $kampagne){
    return [];
  }

  /**
   * Removes the Data
   *
   * @param \LK\Kampagne\Kampagne $kampagne
   * @return array
   */
  function removeKampagnenData(\LK\Kampagne\Kampagne $kampagne){
    return [];
  }
}
