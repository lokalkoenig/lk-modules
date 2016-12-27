<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Kampagne;

/**
 * Description of Kampagne
 *
 * @author Maikito
 */
class Kampagne {
    //put your code here
    
    var $version = 1;
    var $node = null;
    
    
    function __construct(\stdClass $node) {
         $this -> node = &$node;
         
         if(!isset($this -> node -> loadedmedias)){
             dpm($this -> node -> nid); 
             $this -> initMedias();
         }
    }
    
    
    function getNode(){
        return $this -> node;
    }
    
    private function initMedias(){
        
        $this -> node -> loadedmedias = true;
        $this -> node -> lkstatus = $this -> node -> field_kamp_status["und"][0]["value"];   
        $this -> node -> plzaccess = \LK\Kampagne\AccessInfo::loadAccess($this -> node -> nid); 
        
        $medien = [];
        
        $result = db_query('SELECT field_medium_node_nid as nid, entity_id, entity_type '
            . 'FROM {field_data_field_medium_node} '
            . "WHERE entity_type='medium' AND field_medium_node_nid = :nid", array(':nid' => $this -> node -> nid));
        foreach ($result as $record) {
            $medien[] = entity_load_single($record -> entity_type, $record -> entity_id);
        }
        
        $medien_print = array();
        $medien_online = array();
        
        foreach($medien as $media){
            $test = \_lk_get_medientyp_print_or_online($media->field_medium_typ['und'][0]['tid']);
            if($test == 'print'){
                $medien_print[] = $media; 
            }
            else {
                $medien_online[] = $media; 
            }
        }
            
        foreach ($medien_online as $medium){
            $medien_print[] = $medium;
        }
        
        $this -> node -> medien = $medien_print;
    }
    
    
    
    
    /**
     * Removes a Kampagne and its relations
     */
    function remove(){
        if(isset($this -> node -> medien)){
            // Medien
            foreach($this -> node -> medien as $entity){
              entity_delete('medium', $entity -> id);    
            }   
        }
  
        // remove PLZ-Sperren        
        $manager = new \LK\Kampagne\SperrenManager();  
        $result = db_query('SELECT field_medium_node_nid as nid, entity_id, entity_type FROM {field_data_field_medium_node} WHERE field_medium_node_nid =:nid', array(':nids' => $this -> node -> nid));
        foreach ($result as $record) {
           if($record -> entity_type == "plz"){
             $manager ->removeSperre($record -> entity_id);
           }
        }
    }   
}
