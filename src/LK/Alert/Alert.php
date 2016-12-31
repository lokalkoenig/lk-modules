<?php
namespace LK\Alert;

use LK\Solr\SearchQueryParser;

class Alert  {
    
    use \LK\Log\LogTrait;
  
    var $entity = null;
    
    function __construct($id) {
        $this -> entity = entity_load_single('alert', $id);
        
        if(!$this -> entity):
            throw new \Exception("Can not load the alert");
        endif;
    }
    
    function getContext(){
        return __NAMESPACE__ . '/' . __CLASS__;
    }
    
    function getAuthor(){
        return $this -> entity -> uid;
    }
    
    function getId(){
        return $this -> entity -> id;
    }
    
    function getQuery(){
       return unserialize($this -> entity -> field_search_query["und"][0]["value"]);
    }
    
    function getCreated(){
        return $this -> entity -> created;
    }
    
    function getTimestamp(){
        return $this -> entity -> changed;
    }
    
    function getCount(){
       return $this -> entity -> field_search_count["und"][0]["value"];
    }
    
    function getTitle(){
        return $this -> entity -> title;
    }
    
    function getSearchLink(){
        return SearchQueryParser::buildLink($this ->getQuery());
    }
    
    function getRemoveLink(){
        return url("user/" . $this ->getAuthor() . "/alerts", array("query" => array('action' => "remove", 'id' => $this ->getId())));
    }
    
    function __toString() {
        return $this ->getTitle() . " / [Nodes: " . $this ->getCount() . " / Uid: ". $this ->getAuthor() ."]";
    }
    
    
    /**
     * Only for DEVELOPMENT issues
     * 
     * @param Integer $timestamp
     */
    function updateTimestamp($timestamp){
        $this -> entity -> changed = $timestamp;
        db_query("UPDATE eck_alert SET changed='". $timestamp ."' WHERE id='". $this ->getId() ."'");
    }
    
    function updateCount($new_count){
        $this -> entity -> field_search_count["und"][0]["value"] = $new_count;
        $this -> entity -> changed = time();
        $this -> entity -> save();
    }
    
    function remove(){
        $this->logNotice('Lösche Alert ' . $this);
        
        entity_delete('alert', $this -> entity -> id);  
    }
}
