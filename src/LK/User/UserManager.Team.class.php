<?php

/**
 * Team-Manager-Wrapper 
 * @version 1.0
 * @since 2015-11-21 
 */

namespace LK;


class Team {
   
   var $id = null;
   var $data = null;
   var $verlag = null;
   var $leiter = false;
   var $members = array();
   var $telephone = null;
   
   function __construct($team_id) {
       $team_data = entity_load_single("team", $team_id);
       
       if($team_data){
           $this -> id = $team_data -> id;
           $this -> data = $team_data;
           $this -> verlag = $team_data->field_verlag['und'][0]['uid'];
           $this -> leiter = $team_data->field_team_verkaufsleiter['und'][0]["uid"];
           $this -> telephone = $team_data->field_telefonteam['und'][0]['value'];
           
           return $this;
       }
       else {
            throw new LKException('Unable to find Team ' . $team_id);
       }
       
       
       return false;
   }
   
   function getAusgaben(){
       
       $array = array();
       
       if(isset($this->data->field_ausgaben['und'])){
          foreach($this->data->field_ausgaben['und'] as $item){
             $array[] = $item["target_id"];  
          } 
       }
       
   return $array;    
   }
   
   
   function  getTitle(){
       return $this -> data -> title;       
   }
   
   
   
   function getUrl(){
       return 'team/' . $this -> getId();
   }
   
   function getLeiter(){
       return $this -> leiter;
   }
   
   function  getId(){
       return $this -> id;
   }
   
   function getVerlag(){
       return $this -> verlag;    
   }
   
   function isTelephone(){
       return $this -> telephone;
   }
   
   function getUser_count(){
       $members = $this -> getUser();
       return count($members);    
   }
   
   function getUserActive_count(){
       return count($this ->getUserActive());
   }
   
   function getUserActive(){
       
       $members = array();
       $team_id = $this -> getId();
       $dbq = db_query("SELECT p.uid FROM profile p, 
                           field_data_field_team t,
                           users u WHERE p.type='mitarbeiter' AND
             p.pid=t.entity_id AND p.uid IS NOT NULL AND
             t.field_team_target_id='". $team_id ."' AND u.uid=p.uid
             AND u.status='1' ORDER BY u.name ASC");
              foreach($dbq as $all){
                 $members[] = $all -> uid;
              }
              
   return $members;    
   }
   
   function getUser(){
       
       $team_id = $this -> getId();
       
       if(!$this -> members){
           $leiter = $this ->getLeiter(); 
           $this -> members[] = $leiter;
          
           $dbq = db_query("SELECT p.uid FROM profile p, 
                           field_data_field_team t,
                           users u WHERE p.type='mitarbeiter' AND
             p.pid=t.entity_id AND p.uid IS NOT NULL AND
             t.field_team_target_id='". $team_id ."' AND u.uid=p.uid
             ORDER BY u.name ASC");
              foreach($dbq as $all){
                 if($leiter == $all -> uid){
                     continue;
                 } 
                  
                 $this -> members[] = $all -> uid;
              }
         }
         
   return $this -> members;      
   }    
}