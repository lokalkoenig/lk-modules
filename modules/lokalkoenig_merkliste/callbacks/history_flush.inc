<?php
/**
 * @file Flush history
 * @path /history/flush
 */

use LK\Merkliste\History\UserManager as HistoryManager;

/**
 * Flush the User-History
 */
function lokalkoenig_merkliste_flush_history(){
  HistoryManager::getInstance(\LK\current())->flush();
  drupal_set_message("Ihr Verlauf wurde gelöscht.");
  drupal_goto('history');   
}
