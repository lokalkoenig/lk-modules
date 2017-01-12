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
  
  const TABLE = 'lk_vku_documents';


  /**
   * Data Array
   * @var array 
   */
  protected $data = [
      'document_vorlage' => 0,
      'document_usage' => 0
  ];
  
  /**
   * ID of the Document
   * @var int 
   */
  var $id = null;
  
  function __construct($data = array()){
      
  if(isset($data['id'])){
    $this -> id = $data['id'];
    unset($data['id']);
        
    $this-> data = $data;
   }
      
   return $this;
  }
  
  /**
   * Gets the preset
   * 
   * @return string
   */
  function getPreset(){
   return $this -> data['document_preset']; 
  }
  
  function  getCategory(){
    return $this -> data['document_category']; 
  }
  
  /**
   * Sets the Vorlage
   * 
   * @param type $vorlage
   * @return $this
   */
  function setVorlage($vorlage){
    return $this->setData('document_vorlage', $vorlage);
  }
  
  
  /**
   * Sets the Category
   * 
   * @param type $category
   * @return $this
   */
  function setCategory($category){
    return $this->setData('document_category', $category);
  }
  
  
  /**
   * Sets Data
   * 
   * @param Key $key
   * @param Val $val
   * @return $this
   */
  function setData($key, $val){
    $this -> data[$key] = $val;
    
    return $this;
  }
  
  /**
   * Get the Title
   * 
   * @return string
   */
  function getTitle(){
    return $this->data['document_title'];
  }
  
  /**
   * Sets the Content
   * 
   * @param array $data
   */
  function setContent($data){
    $this ->setData('document_content', serialize($data));
  }
  
  /**
   * Sets the Status
   * 
   * @param int $status
   * @return $this
   */
  function setStatus($status){
    return $this->setData('status', $status);  
  }
  
  /**
   * Saves the Document
   * 
   * @return $this
   */
  function save(){
    $time = REQUEST_TIME;
    $this->setData('document_changed', $time);
    
    if(!$this -> id){
      $this->setData('document_created', $time);
      $this -> id = db_insert(self::TABLE)->fields($this -> data)->execute();
    }
    else {
      
      $data = $this -> data;
      if($data['id']){
        unset($data['id']);
      }
      
      db_update(self::TABLE)
              ->fields($data)
              ->condition('id', $this->id, '=')
              ->execute();
    }
    
  return $this;  
  }
  
  
  /**
   * Sets the title
   * 
   * @param string $title
   * @return $this
   */
  function setTitle($title){
    return $this->setData('document_title', $title);
  }
  
  /**
   * Sets the footnote
   * 
   * @param string $footnote
   * @return $this
   */
  function setFootnote($footnote){
    return $this->setData('document_footnote', $footnote);
  }
  
  /**
   * Set the present
   * 
   * @param string $present Preset
   * @return $this
   */
  function setPreset($present){
    return $this->setData('document_preset', $present);
  }
  
  /**
   * Sets the Layout
   * 
   * @param type $layout
   * @return $this
   */
  function setLayout($layout){
    return $this->setData('document_layout', $layout);
  }
  
  
  /**
   * Gets the content
   * 
   * @return array
   */
  function getContent(){
    return unserialize($this -> data['document_content']);
  }
  
  /**
   * Gets the ID
   * 
   * @return id
   */
  function getId(){
    return $this -> id;
  }
  
  function getStatus(){
    return $this->data['status'];
  }
  

  function setUser($uid){
    $this -> setData('uid', $uid);
    
    return $this;
  }
  
  function getData(){
    return $this -> data;
  } 
  
  /**
   * Gets back Data for Template-Usage
   * 
   * @return array
   */
  function getTemplateData(){
    return $this->getData() + [
      'id' => $this->getId()
    ];   
  }
  
  function remove(){
    
      $count = db_delete(self::TABLE)
          ->condition('id', $this -> id)
          ->execute();
      
      return $count;
  }
  
  /**
   * Gets back the String
   * of the object
   * 
   * @return string
   */
  function __toString() {
    return $this->getTitle() . " (" .ucfirst($this->getCategory())  .")";
  } 
}
