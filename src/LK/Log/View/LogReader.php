<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Log\View;

/**
 * Description of LogReader
 *
 * @author Maikito
 */
abstract class LogReader {
    //put your code here
    
    var $data = [];
    
    abstract function __construct($data);
    abstract function __toString();
    
    
    function getContext(){
        if(!lk_is_moderator()){
            return null;
        }
        
        // Show VKU-Info and other Variables
        
       $data = unserialize($this -> data -> context);
       return "<p><code class='small'>" .  json_encode($data) . "</code></p>";
    }
    
    function getNode(){
        
        if(!$this -> data -> node_nid){
            return null;
        }
        
        $node = node_load($this -> data -> node_nid);
        return '<p>' . l($node -> title, "node/" . $node -> nid) . " <small class='label label-info'>" . $node->field_sid['und'][0]['value'] . '</small></p>';    
    }
}
