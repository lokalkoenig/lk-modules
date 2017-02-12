<?php
namespace LK\VKU\Data;

/**
 * Description of VKUMaintenance
 *
 * @author Maikito
 */
class VKUMaintenance extends \LK\VKU\PageManager {
  
  /**
   * Runs the Task as User 11
   * 
   * @global type $user
   */
  function __construct() {
    global $user;
    $user = user_load(11);
    
    parent::__construct();
  }

  /**
   * Overwrite with Log-Option
   *
   * @param \VKUCreator $vku
   */
  function removeVKU(\VKUCreator $vku) {

    $status = $vku->getStatus();
    $vku_id = $vku->getId();
    $vku_changed = $vku->get('vku_changed');

    parent::removeVKU($vku);
    $this->logCron("Cron-Delete VKU-ID " . $vku_id . "/". $status .", Zuletzt geändert am: " . format_date($vku_changed, 'short'));
  }
}
