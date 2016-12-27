<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Alert;

use LK\Alert\AlertManager;
use LK\Solr\Search;
use LK\Component;


/**
 * Description of AltertCron
 *
 * @author Maikito
 */
class AltertCron extends Component {
    //put your code here
    
    static function run(){
        
        $query = new \EntityFieldQuery();
        $query->entityCondition('entity_type', 'alert')->propertyOrderBy('changed', 'ASC')->range(0, 100);
        $result = $query->execute();
        
        foreach($result["alert"] as $alert):
            $alert = AlertManager::load($alert -> id);
            
            if($alert):
                self::testAlert($alert);
            endif;
     
        endforeach;
        
        
        self::logCron("Parse " . count($result["alert"]) . " Alerts");
    }
    
     function getContext(){
        return __NAMESPACE__ . '/' . __CLASS__;
    }
    
    static protected function testAlert(Alert $alert){
        
      $query = $alert ->getQuery();
      $timestamp = $alert ->getTimestamp();
      
      $search = new Search();
      $search -> addFromQuery($query);
      
      $search -> addTimestamp($timestamp);
      $nodes = $search -> getNodes();
      
      if($nodes):
          self::sendNotification($alert, $nodes);
            
          // Create another Query to measure the new Counts
          $search2 = new \LK\Solr\Search();
          $search2 ->addFromQuery($query);
          $count = $search2 ->getCount();
          $alert ->updateCount($count);
          
          self::logCron("Sende Notifikation ueber neue Kampagnen an " . $alert);
   
        endif;
    }
    
    static protected function sendNotification(Alert $alert, $nodes){
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
}
