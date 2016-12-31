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
     * @param Array $options Options
     * @return String
     */
     function logCron($message, $options = array()){
       $log = new Debug($message);
       $log -> setCategory("cron");
       $this -> _parseLogOptions($log, $options);
       $log -> setContext("class", get_called_class());
       return $log -> save();
    }
  
    /**
     * Logs an Error-Message who comes from a Cron-Job
     * 
     * @param type $message
     * @param Array $options Options
     * @return String
     */
   
    function logError($message, $options = array()){
       $log = new Debug($message);
       $log -> setCategory("error");
       $this -> _parseLogOptions($log, $options);
       $log -> setContext("class", get_called_class());
       return $log -> save();
    }
    
    /**
     * Logs an Notice Message
     * 
     * @param type $message
     * @param Array $options Options 
     * @return type
     */
    function logNotice($message, $options = array()){
      $log = new Debug($message);
      $log -> setCategory('debug');
      $this -> _parseLogOptions($log, $options);
      $log -> setContext("class", get_called_class());
      return $log -> save();
    }
    
    
    /**
     * Logs a Verlag-Message
     * 
     * @param String $message
     * @param Array $options
     */
    function logVerlag($message, $options = array()){
      
       // Log the Event
       $log = new \LK\Log\Verlag($message);
       $this -> _parseLogOptions($log, $options);
       $log -> setContext("class", get_called_class());
    
    return $log -> save();
    }
    
    /**
     * Logs Kampagnen-Messages
     * 
     * @param type $message
     * @param Int $nid Node-ID
     * @param Array $options Options
     * @return String
     */
    function logKampagne($message, $nid, $options = array()){
      $log = new Debug($message);
      $log -> setCategory('kampagne');
      $log->setNid($nid);
      $this -> _parseLogOptions($log, $options);
      $log -> setContext("class", get_called_class());
      
      return $log -> save();
    }  
    
    /**
     * Parses the optional parameter Options and adds it
     * to the Log-Message
     * 
     * @param \LK\Log\LogInterface $log
     * @param type $options
     */
    private function _parseLogOptions(\LK\Log\LogInterface $log, $options){
      
       if(isset($options['nid'])){
          $log -> setNid($options['nid']);
       }
       
       if(isset($options['uid'])){
          $log ->setUser($options['uid']);
       }
       
       if(isset($options['lizenz'])){
          $log -> setLizenz($options['lizenz']);
       }
       
       if(isset($options['vku'])){
          $log -> setVku($options['vku']);
       }
    }
}
