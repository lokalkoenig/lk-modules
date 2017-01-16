<?php
/**
 * @file
 * This file defines the side-wide Cron-Jobs
 * defined in Static functions in the src/ folder structure
 * 
 * The callback is there, because cron-API does 
 * not support Classes yet, only functions
 */



/**
 * Adds Cronjobs
 * 
 * @param type $op
 * @param type $job
 * @return array
 */
function lokalkoenig_admin_cronapi($op, $job = NULL) {

  $items['lk_remove_outdated_sperren'] = array(
    'description' => 'LK: PLZ-Sperren, remove old',
    'rule' => '0 2 * * *', // Every Day at 2 AM
    'callback' => 'lokalkoenig_admin_cronapi_cb',
    'arguments' => array('\\LK\\Kampagne\\Cron\\RemoveSperren'),
  );
  
  $items['lk_run_alert_manager'] = array(
    'description' => 'LK: Alert Manager',
    'rule' => '0 8 * * *', // Every Day at 8 AM
    'callback' => 'lokalkoenig_admin_cronapi_cb',
    'arguments' => array('\\LK\\Alert\\Cron\\AlertCron'),
  );
  
   $items['lk_vku_delete_old_vkus'] = array(
    'description' => 'LK: Unbenötigte VKUs löschen (VKU-Status=deleted)',
    'rule' => '0 2 * * *', // Every Day at 2 AM
    'callback' => 'lokalkoenig_admin_cronapi_cb',
    'arguments' => array('\\LK\\Admin\\Cron\\RemoveOldVKU'),
  );
  
  $items['lk_vku_licences_done'] = array(
    'description' => 'LK: Mark VKUs as DONE',
    'rule' => '0 2 * * *', // Every Day at 2 AM
    'callback' => 'lokalkoenig_admin_cronapi_cb',
    'arguments' => array('\\LK\\Admin\\Cron\\MarkLicencesAsFinished'),
  ); 
  
  $items['lk_garbage_collector'] = array(
    'description' => 'LK: Delete unused files (tbc)',
    'rule' => '0 2 * * *', // Every Day at 2 AM
    'callback' => 'lokalkoenig_admin_cronapi_cb',
    'arguments' => array('\\LK\\Admin\\Cron\\RemoveOrphanedFiles'),
  ); 
  
  $items['lk_kampagnen_popularity'] = array(
    'description' => 'LK: Update Kampagnen Populartity',
    'rule' => '0 2 * * *', // Every 5 minutes
    'callback' => 'lokalkoenig_admin_cronapi_cb',  
    'arguments' => array('\\LK\\Kampagne\\Cron\\KampagnenPopularity'),
  ); 
  
  $items['lk_user_stats'] = array(
    'description' => 'LK: User-Stats',
    'rule' => '50 * * * *', // Every 50 minutes
    'callback' => 'lokalkoenig_admin_cronapi_cb',  
    'arguments' => array('\\LK\\Stats\\Cron\\UserStats'),
  ); 
  
  $items['lk_user_stats_daily'] = array(
    'description' => 'LK: User-Stats',
    'rule' => '30 23 * * *', // Every 50 minutes
    'callback' => 'lokalkoenig_admin_cronapi_cb',  
    'arguments' => array('\\LK\\Stats\\Cron\\DailyUserStats'),
  ); 
  
  return $items;
}

/**
 * Callback for the Cron-API
 * Static functions are not going to be recodnized
 * 
 * @param string $cb Class-Name with function who to call
 */
function lokalkoenig_admin_cronapi_cb($cb){
  
  try {
    $cb::executeCron();
    //eval($cb);
  } catch (Exception $ex) {
    $error = new \LK\Log\Debug("Cron on " . $cb . " failed with message: " . $ex ->getMessage());
    $error ->setCategory('error');
    $error -> save();
    return ;
  }
}
