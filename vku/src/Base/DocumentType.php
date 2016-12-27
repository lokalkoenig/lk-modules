<?php

namespace Base;

class DocumentType {
   
   var $title = null;
   var $machine_name;
   var $data = array(); 
   var $document = null;
   var $slots = 1;
   var $entity_type = 'vku_document';
   
   function __construct(\DynamicDocument\Manager $reference, $data = array()) {
       $this -> document = $reference;
       
       if(isset($data["title"])):
           $this -> title = $data["title"]; 
       endif;    
       
       if(isset($data["content"])):
            $x = 1;
            foreach ($data as $content){
                $this ->setSlot($slot_id, $content["type"], $content["title"], $content["data"]);
                $x++;
            }
       endif;
   }
   
   function initialize(){
      for ($x = 1; $x <= $this -> slots; $x++){
            $this ->setSlot($x);
      }  
   }
   
   function getTitle(){
       return $this -> title;
   }
   
   function getID(){
       return $this -> machine_name;
   }
   
   function serialize(){
       return array(
           'id' => $this -> machine_name,
           'title' => $this -> getTitle(),
           'content' => $this -> data
       );
   }
   
   
   /**
    * This defines the Main-HTML-Layout of the
    * Format
    */
   function getLayoutHTML(){ }
   
   function saveColumn($id, $data){
       $this -> data[$id] = $data;
   }
   
   
   /**
    * Gets back all the Data of the 
    * 
    * @return Array
    */
   function getData(){
       return $this -> data;
   }
   
   
   /** 
    * Gets back the Content-Type of the Slot
    * 
    * @param Integer $slot_id
    * @return mixed
    */
   function getSlotType($slot_id){
       if(!isset($this -> data[$slot_id])){
           return 'undefined';
       }
       
       $this -> data[$slot_id]["type"];
   }
   
   function setSlot($slot_id, $data_type = 'undefined', $title = null, $data_array = array()){
      $this -> data[$slot_id] = array(
            'type' => $data_type, 
            'title' => $title, 
             'data' => $data_array
        ); 
   }
   
   
   function save(){
       $this -> document -> saveDocument($this -> serialize());
   }
}
