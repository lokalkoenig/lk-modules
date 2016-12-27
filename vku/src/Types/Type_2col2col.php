<?php

namespace Types;
use Base\DocumentType as Document;

class Type_2col2col extends Document {
    
    var $slots = 4;
    var $machine_name = '2col2col';
    var $title = '2 Spalten | 2 Spalten';
    
    function __construct(\DynamicDocument\Manager $reference, $data) {
        parent::__construct($reference, $data);
    }
    
    function getLayoutHTML(){
        return '<div class="row document"><div class="row"><div class="col-xs-6 col-half">1</div><div class="col-xs-6 col-half">2</div></div><div class="row"><div class="col-xs-6 col-half">3</div><div class="col-xs-6 col-half">4</div></div></div>';
    }
    
    
    function render(){
    
        
    }
}


