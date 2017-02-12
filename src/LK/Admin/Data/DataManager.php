<?php

namespace LK\Admin\Data;

/**
 * Description of DataManager
 *
 * @author Maikito
 */
class DataManager {

  use \LK\Log\LogTrait;
  var $LOG_CATEGORY = 'User Verwaltung';

  var $modules = [];
  protected $kampagne_can_be_removed = TRUE;
  protected $user_can_change = TRUE;

  function __construct() {
   
    $this -> modules[] = '\\LK\\Merkliste\\History\\HistoryDataManager';
    $this -> modules[] = '\\LK\\Merkliste\\Manager\\MerklistenDataManager';
    $this -> modules[] = '\\LK\\Solr\\Data\\SearchData';
    $this -> modules[] = '\\LK\\VKU\\Data\\DataManager';
    $this -> modules[] = '\\LK\\Admin\\Data\\UserData';

    foreach (module_implements('lk_data_management') as $module) {
      $function = $module . '_lk_data_management';
      $addition = $function();

      if($addition){
        $this->modules += $addition;
      }
    }
  }

  public function canChangeUserState(){
    return $this->user_can_change;
  }

  function disableUserCanChange(){
    $this->user_can_change = FALSE;
  }

  /**
   * Removes a User
   * @param \LK\User $account
   */
  private function removeUser(\LK\User $account){



  }



  /**
   * Disable the User
   *
   * @param \LK\User $account
   */
  private function userDisable(\LK\User $account){
    
    $this->getUserStats($account);
    
    if(!$this->user_can_change){
      throw new \Exception('User Status can not be changed.');
    }

    $save = user_load($account ->getUid());
    $save -> status = 0;
    user_save($save);
  }

  /**
   * Kampagne will be disabled
   */
  function disableKampagne(){
    return [];
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

  function getUserStats(\LK\User $account){
    $stats = [];
    $stats['Account-Type'] = $account ->getRole();
    $stats['Letzter Zugriff'] = format_date($account ->getLastAccess());
    $stats['Angelegt'] = format_date($account ->user_data -> created);
    $stats['------'] = '';

    foreach($this -> modules as $module){
      $obj = $this->loadModule($module);
      $stats += $obj ->getUserDataCount($account);
    }
    return $stats;
  }
}
