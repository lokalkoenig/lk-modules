<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Log\View;
use VKUCreator;
/**
 * Description of LogReader
 *
 * @author Maikito
 */
abstract class LogReader {
    //put your code here
    
    var $data = [];
    
    abstract function __construct($data);
    abstract function render();
    
    
    function getContext(){
       
      $context = [];
      //dpm($this -> data);
      
      if($this -> data -> vku_id){
        $vku = new VKUCreator($this -> data -> vku_id);   
        
        if(!$vku ->is()){
          $context[] = '<p class="small"><strike><span class="glyphicon glyphicon-lock"></span> Verkaufsunterlage #'. $this -> data -> vku_id .' wurde bereits gelöscht.</strike></p>';
        }
        else {
          $context[] = '<p class="small"><span class="glyphicon glyphicon-lock"></span> <label class="label label-default">'. ucfirst($vku ->getStatus()) .'</label> '. $vku ->getTitle() .'</p>';
        }
      }
      
      if($this -> data -> lizenz_id){
        
        $lizenz = new \LK\Lizenz($this -> data -> lizenz_id);
        if($lizenz -> is()){
          $context[] = '<p class="small"><span class="glyphicon glyphicon-shopping-cart"></span> <label class="label label-default">#'. $lizenz ->getId() .'</label> '. $lizenz ->getShortSummary() .'</p>';
        }
        else {
            $context[] = '<p class="small">Die Lizenz wurde administrativ entfernt.</p>';
        }
      }
  
      
      if(lk_is_moderator()):
          $data = unserialize($this -> data -> context);
          if($data){
            $context[] = "<hr /><p><span class='glyphicon glyphicon-plus'></span> <small>" .  print_r($data, true) . "</small></p>";
          }
       endif;
       
    return implode('', $context);   
    }
    
    function getNode(){
        
        //dpm($this -> data);
      
        if(!$this -> data -> node_nid){
            return null;
        }
        
        $node = node_load($this -> data -> node_nid);
        return '<p>' . l($node -> title, "node/" . $node -> nid) . " &nbsp;&nbsp;<small class='label label-info'>" . $node->field_sid['und'][0]['value'] . '</small></p>';    
    }
}
