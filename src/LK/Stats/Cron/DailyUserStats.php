<?php

namespace LK\Stats\Cron;

/**
 * Description of DailyUserStats
 *
 * @author Maikito
 */
class DailyUserStats {
  
  use \LK\Stats\Action;
  
   public static function executeCron(){
    // log daily active users
    $stats = new DailyUserStats();
    $stats->logUsersPerDay();
    $stats->logPublicKampagnen();
    $stats->logKeywordSearches();
    $stats->logUsersTotal();
  }
  
  /**
   * Logs how many Users are registered as active in the LK
   */
  function logKeywordSearches(){
    
    $time = time() - (60 * 60 * 24);
    $dbq = db_query("SELECT count(*) as count FROM lk_search_history WHERE created >='". $time ."'");
    $all = $dbq -> fetchObject();
    
    $this->setAction('searches', $all -> count);
  }
  
  /**
   * Logs how many Users are registered as active in the LK
   */
  function logUsersTotal(){
    
    $dbq = db_query("SELECT count(*) as count FROM users WHERE status='1'");
    $all = $dbq -> fetchObject();
    
    $this->setAction('users', $all -> count);
  }
  
  /**
   * Logs how many Users logged in today
   */
  function logUsersPerDay(){
    
    $time = time() - (60 * 60 * 24);
    $dbq = db_query("SELECT count(*) as count FROM users WHERE access>='". $time ."' AND status='1'");
    $all = $dbq -> fetchObject();
    
    $this->setAction('users-active', $all -> count);
  }
  
  /**
   * Logs how many Kampagnen are available
   */
  function logPublicKampagnen(){
    $dbq = db_query("SELECT count(*) as count FROM node WHERE type='kampagne' AND status='1'");
    $all = $dbq -> fetchObject();
    
    $this->setAction('kampagnen', $all -> count);
  }
}
