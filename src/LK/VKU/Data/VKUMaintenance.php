<?php
namespace LK\VKU\Data;

/**
 * Description of VKUMaintenance
 *
 * @author Maikito
 */
class VKUMaintenance extends \LK\VKU\PageManager {
  
  /**
   * Sets the VKU
   *
   * @param \VKUCreator $vku
   */
  function setVKU(\VKUCreator $vku){
    $this->vku = $vku;
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
    $this->setVKU($vku);
 
    parent::removeVKU();
    $this->logCron("Cron-Delete VKU-ID " . $vku_id . "/". $status .", Zuletzt geändert am: " . format_date($vku_changed, 'short'));
  }
}
