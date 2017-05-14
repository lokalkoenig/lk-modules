<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use LK\Log\Debug as DebugLog;
use LK\Log\Kampagne as KampagnenLog;
use LK\Log\Verlag as VerlagLog;


/**
 * Logs a Debug Message to the LK-Watchdog
 * 
 * @param type $message
 */
function lk_log_debug($message){
    $log = new DebugLog($message);
    $log ->save();
}

/**
 * Logs a Cronjob Message to the LK-Watchdog
 * 
 * @param type $message
 */
function lk_log_cron($message){
    $log = new DebugLog($message);
    $log -> setCategory('cron');
    $log ->save();
}

/**
 * Logs Node related entries
 * 
 * @param type $nid
 * @param type $message
 * @param type $context
 */

function lk_log_kampagne($nid, $message, $context = []){
    $log = new KampagnenLog($nid, $message);
   
    while(list($key, $val) = each($context)){
       $log ->setContext($key, $val); 
    }
    
    $log ->save();
}

function lk_log_verlag($message){
   
    $log = new VerlagLog($message);
    $log ->save();
}


/**
 * DEPRICATED
 * 
 * @global type $user
 * @param type $type
 * @param type $message
 * @param type $uid
 * @return type
 */
function lk_note($type, $message, $uid = NULL){
  global $user;
    
  if(!$uid){
    $uid = $user -> uid;
  }
   
  $log = new DebugLog($message);
  $log->setSubcategory($type);
  $log->set("uid", $uid);
  $log ->save();
   
  return $message;
}

/**
 * Logs an Action
 * @deprecated 
 * 
 * @param type $action
 * @param type $id
 */
function log_action($action, $id = 0){
  new \LK\Stats\ActionManager($action, $id);
}
