<?php
namespace DynamicDocument;


class DocumentContent {
 
    var $title;
    var $data = array();
    var $machine_name;
    
    
    function __construct($data = array()) {
        $this -> data = $data;
    }
    
    function getData(){
        return $this -> data;
    }
    
    function renderPPT(LK_PPT $ppt){
        
    }
    
    function renderPDF(PDF $pdf){
        
    }
}
