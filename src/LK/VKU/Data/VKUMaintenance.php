<?php
namespace LK\VKU\Data;

use \LK\VKU\PageManager;
use VKUCreator;

/**
 * Description of VKUMaintenance
 *
 * @author Maikito
 */
class VKUMaintenance extends PageManager {
  
  /**
   * Constructor
   * 
   * @param \VKUCreator $vku
   */
  function __construct(VKUCreator $vku) {
    parent::__construct($vku);
  }

  /**
   * Overwrite with Log-Option
   */
  function removeVKU() {

    $vku= $this->getVKU();

    $status = $vku->getStatus();
    $vku_id = $vku->getId();
    $vku_changed = $vku->get('vku_changed');
    parent::removeVKU();

    $this->logCron("Cron-Delete VKU-ID " . $vku_id . "/". $status .", Zuletzt geändert am: " . format_date($vku_changed, 'short'));
  }
}
