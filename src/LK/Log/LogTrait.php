<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Log;

/**
 * Description of LogTrait
 *
 * @author Maikito
 */
trait LogTrait {
   
    /**
     * Logs as Message who comes from a Cron-Job
     * 
     * @param type $message
     * @return String
     */
    static function logCron($message){
       $log = new Debug($message);
       $log -> setCategory("cron");
       $log -> setContext("class", get_called_class());
       return $log -> save();
    }
  
    /**
     * Logs an Error-Message who comes from a Cron-Job
     * 
     * @param type $message
     * @return String
     */
   
    static function logError($message){
       $log = new Debug($message);
       $log -> setCategory("error");
       $log -> setContext("class", get_called_class());
       return $log -> save();
    }
    
    /**
     * Logs an Notice Message
     * 
     * @param type $message
     * @return type
     */
    static function logNotice($message){
      $log = new Debug($message);
      $log -> setCategory('debug');
      $log -> setContext("class", get_called_class());
      return $log -> save();
    }
}
