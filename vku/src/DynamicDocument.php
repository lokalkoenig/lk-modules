<?php

namespace DynamicDocument;


/**
 * Types:
 * 
 * 1col-2col
 * 1col-1col
 * 2col-2col
 * 2col-1col
 * full
 */


class Manager {
   
    var $id;
    var $data;
    var $title;
    var $category;
    
    var $content_types = array(
        'image' => 'Grafik',
        'list' => "Liste",
        'text' => "Freier Text",
        'table' => "Tabelle"
    );
    
    var $availableLayouts = array(
        '1col2col' => '1 Spalte | 2 Spalten',
        '2col2col' => '2 Spalten | 1 Spalte',
        //'2col2col' => '2 Spalten | 2 Spalten',
        //'2col2col' => '2 Spalten | 1 Spalte',
        //'full' => '100%'
    );
    
    var $entity;
    
    
    var $categories = array(
        'print' => "Print",
        'online' => "Online",
        'sonstiges' => "Sonstiges"
    );
    
    var $document = null;
    
    /**
     * Return the Title of the Document
     * 
     * @return String 
     */
    function getTitle(){
        return $this -> title;
    }   
    
    function setTitle($new_title){
        $this -> entity -> title = $new_title;
        $this -> entity -> save();
        $this -> loadEntity($this -> entity);
    }
    
    
    function getTemplates($category, $uid){
        
        // db_query
        
        
        
        
    }
    
    /** 
     * Creates a new Document
     * 
     * @param String $category
     * @param String $document
     */
    public function create($category, $document){
        
         $entity = entity_create('vku_document', array('type' =>'vku_document', 
                                                       'category' => $category, 
                                                       'document_type' => $document));
         $entity -> save();
         $this -> entity = $entity;
         
         $key = $entity -> document_type;
         $cn = '\Types\Type_' . $key;
         $this -> document = new $cn($this, array());
         $this -> document -> initialize();
         $this -> saveDocument($this -> document -> serialize());
    }
    
    private function loadEntity($entity){
        
        if(!$this -> id){
            return false;
        }
        
        $this -> entity = $entity;
        $this -> id = $entity -> id;
        $this -> title = 'Test-Entity';
        
        if(!$this -> document){
            $key = $this -> entity -> document_type;
            $cn = '\Types\Type_' . $key;
            $this -> document = new $cn($this, array());
        }
        
    return $this -> document;    
    }
  
    function getContentTypes(){
        return $this -> content_types;
    }
    
    function saveDocument($arr){
        $data = serialize($arr);
        $this -> entity -> document_data = $data;
        $this -> _saveEntity();
    }
       
    
    /**
     * Gets the Document
     * 
     * @return /Base/DocumentType
     */
    public function getDocument(){
        return $this -> document;
    }

    private function _saveEntity(){
        
        if(!$this -> id){
            return false;
        }
        
        $this -> entity -> save();
        $this ->loadEntity($this -> entity);
    }
    
    function getLayoutsHTML(){
        $types = $this ->availableLayouts;
        $array = array();
        
        while(list($key, $var) = each($types)){
            $cn = '\Types\Type_' . $key;
            $obj = new $cn($this, array());
            $array[] = array("id" => $key, 'title' => $obj -> getTitle(), 'markup' => $obj -> getLayoutHTML());
        }
    
    return $array;    
    }
    
    
    /**
     * Returns the available Categories
     * 
     * @return Array
     */
    function getCategories(){
        return $this -> categories;
    }
    
    function load($id){
        $entity = entity_load_single('vku_document', $id);
        $this -> id = $id;
        $this ->loadEntity($entity);
    }
    
    
}

function my_autoloader($class) {
    //print $class . "<br />";
    include $class . '.php';
}

spl_autoload_register('\DynamicDocument\my_autoloader');
