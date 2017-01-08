<?php

/*
CREATE TABLE `lk_vku_documents` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `document_vorlage` tinyint(4) NOT NULL DEFAULT '0',
  `document_created` int(11) NOT NULL DEFAULT '0',
  `document_changed` int(11) NOT NULL DEFAULT '0',
  `document_title` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `document_layout` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `document_preset` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `document_footnote` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `document_content` text COLLATE utf8_bin,
  `document_usage` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 */

namespace LK\VKU\Editor;

/**
 * Data-Model for Table 'lk_vku_documents'
 *
 * @author Maikito
 */
class Document {
  
  /**
   * Data Array
   * @var array 
   */
  var $data = [
      'document_vorlage' => 0,
      'document_usage' => 0
  ];
  
  /**
   * ID of the Document
   * @var int 
   */
  var $id = null;
  
  function setData($key, $val){
    $this -> data[$key] = $val;
    
    return $this;
  }
  
  function __clone() {
    
  }
  
  function save(){
    
    
  }
  
  function getId(){
    return $this -> id;
  }
  
  function __construct($data){
      
      if(isset($data['id'])){
        $this -> id = $data['id'];
        unset($data['id']);
        
        $this-> data = $data;
      }
      
      return $this;
  }
  
  function setUser($uid){
    $this -> setData('uid', $uid);
    
    return $this;
  }
  
  function getData(){
    return $this -> data;
  } 
  
  function remove(){
      if($this -> id){
        
          
      }
  }
}
