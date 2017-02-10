<?php

namespace LK\Alert;
use LK\Solr\Search;
use LK\Solr\SearchQueryParser;

use LK\Alert\Alert;

/**
 * Description of Alert
 *
 * @author Maikito
 */
class AlertManager {
    
    use \LK\UI\Table;
    use \LK\Log\LogTrait;
    var $LOG_CATEGORY = "Alert";
    
    /**
     * Creates a new Alert
     * 
     * @param type $query_array
     * @return \LK\Alert\Alert
     */
    public function create($query_array){
         
         $entity = entity_create('alert', array('type' => 'alert'));
         $entity -> field_search_query["und"][0]["value"] = serialize($query_array);
         $query = new Search();
         $query->addFromQuery($query_array);
         $num = $query ->getCount();
         
         $entity -> title = 'Suche: ' . SearchQueryParser::toLabel($query_array);
         $entity -> field_search_count["und"][0]["value"] = $num; 
         $entity->save();
         
         $this-> logNotice("Erstelle Alert: " . $entity -> title);
         
         return $this -> loadAlert($entity -> id);
    }
    
    
    public function searchTitle($title, \LK\User $account){
        $query = new \EntityFieldQuery();
          // remove all previous created Alters
          $query->entityCondition('entity_type', 'alert')
               ->entityCondition('bundle', 'alert')
               ->propertyCondition('uid', $account ->getUid())
               ->propertyCondition('title', 'Suche: ' . $title); 
          $result = $query->execute();
          
          if(!$result){
              return false;
          }
          
    return $result["alert"];      
    }

    
    /**
     * Loads an Alert
     * 
     * @param type $id
     * @return boolean|\LK\Alert\Alert
     */
    public function loadAlert($id){
        
        try {
          $alert = new Alert($id);
          return $alert;
        } catch (\Exception $ex) {
            return false;
        }       
    }
    
    
    /**
     * Loads Users alters
     * 
     * @param \LK\User $account
     * @return array
     */
    public function getUserAlerts(\LK\User $account){
      // Show existing Alters
      $query = new \EntityFieldQuery();
      $query->entityCondition('entity_type', 'alert')
        ->entityCondition('bundle', 'alert')
        ->propertyCondition('uid', $account ->getUid())->propertyOrderBy('changed', 'DESC');

      $result = $query->execute();

      $alters = [];

       if(isset($result["alert"])){
        foreach($result["alert"] as $alert_entity){
          $alters[] = $alert_entity -> id;
        }
       }

       return $alters;
    }


    /**
     * List all Alters for the User
     *
     * @param \LK\User $account
     * @return string
     */
    public function listAlerts(\LK\User $account){
      
      $alerts = $this->getUserAlerts($account);
      $this ->UI_Table_setHeader(array('Suche', 'Angelegt', 'Kampagnen', ''));
      $count = 0;

      foreach($alerts as $alert_id){
        $alert = $this ->loadAlert($alert_id);
        $this -> UI_Table_addRow(array($alert ->getTitle() . '<br /><small><a href="'. $alert -> getSearchLink() .'">Suchergebnisse öffnen</a></small>',
               date("d.m.Y", $alert ->getCreated()), 
               $alert ->getCount(),
              '<a class="btn btn-sm btn-danger optindelete" href='. $alert ->getRemoveLink() .' optintitle="Alert löschen" optin="Sind Sie sicher?">Alert löschen</a>'));
        $count++;        
      }
    
      return theme('alerts_overview', array("account" => $account, 'count' => $count, "table" => $this->UI_Table_render()));
    }
}
