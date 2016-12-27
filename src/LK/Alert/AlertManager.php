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
    
    /**
     * Creates a new Alert
     * 
     * @param type $query_array
     * @return \LK\Alert\Alert
     */
    static public function create($query_array){
         $entity = entity_create('alert', array('type' => 'alert'));
         $entity -> field_search_query["und"][0]["value"] = serialize($query_array);
         $query = new Search();
         $query->addFromQuery($query_array);
         $num = $query ->getCount();
         
         $entity -> title = 'Suche: ' . SearchQueryParser::toLabel($query_array);
         $entity -> field_search_count["und"][0]["value"] = $num; 
         $entity->save();
         return self::load($entity -> id);
    }
    
    static public function searchTitle($title, $uid){
        $query = new \EntityFieldQuery();
          // remove all previous created Alters
          $query->entityCondition('entity_type', 'alert')
               ->entityCondition('bundle', 'alert')
               ->propertyCondition('uid', $uid)
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
    static public function load($id){
        
        try {
          $alert = new Alert($id);
          return $alert;
        } catch (\Exception $ex) {
            return false;
        }       
    }
}
