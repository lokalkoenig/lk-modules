<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Alert\Cron;

use LK\Alert\Alert;
use LK\Alert\AlertManager;
use LK\Solr\Search;

/**
 * Description of AltertCron
 *
 * @author Maikito
 */
class AlertCron extends AlertManager {
    //put your code here
    
    public function run(){
        
        $query = new \EntityFieldQuery();
        $query->entityCondition('entity_type', 'alert')->propertyOrderBy('changed', 'ASC')->range(0, 100);
        $result = $query->execute();
        
        $x = 0;
        foreach($result["alert"] as $alert):
            $alert = $this ->loadAlert($alert -> id);
            
            if($alert):
                $this->testAlert($alert);
            endif;
        
        $x++;    
        endforeach;
        
        $this->logCron("Parse " . $x . " Alerts");
    }
    
    protected function testAlert(Alert $alert){
        
      $query = $alert ->getQuery();
      $timestamp = $alert ->getTimestamp();
      
      $search = new Search();
      $search -> addFromQuery($query);
      
      $search -> addTimestamp($timestamp);
      $nodes = $search -> getNodes();
      
      if($nodes):
          $this->sendNotification($alert, $nodes);
            
          // Create another Query to measure the new Counts
          $search2 = new \LK\Solr\Search();
          $search2 ->addFromQuery($query);
          $count = $search2 ->getCount();
          $alert ->updateCount($count);
          
          $this ->logCron("Sende Notifikation ueber neue Kampagnen an " . $alert);
        endif;
    }
    
    protected function sendNotification(Alert $alert, $nodes){
        $uid = $alert ->getAuthor();
        $account = user_load($uid);
        
        // We are not going to send on deactivated user
        if(!$account -> status){
            return ;
        }
        
        $subject = 'Neue Kampagnen für ' . $alert ->getTitle();
        if(strlen($subject) > 100){
           $subject = substr($subject, 0, 90) . '...';
        }
        
        $message = array('Hallo '. $account -> name . ",");
        $message[] = 'wir haben neue Kampagnen zu Ihrem erstellten Kampagnen-Alert im Lokalkönig verfügbar.';
        $message[] = 'Sie können die Benachrichtigung jederzeit <em><a href="'. url("user/". $uid . "/alerts", array("absolute" => true)) .'">abbestellen</a></em> und auch neue Kampagnen-Benachrichtigungen anlegen.';
        $message[] = 'Ihr Team von Lokalkönig.de';
        
        privatemsg_new_thread(array($account), $subject, implode("\n\n", $message), array("nodes" => $nodes, 'author' => user_load(11)));  
    }    
    
    /**
     * Access to run the cron
     */
    public static function executeCron(){
      $manager = new AlertCron();
      $manager ->run();
    }   
}
