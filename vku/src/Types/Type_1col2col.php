<?php

namespace Types;
use Base\DocumentType as Document;

class Type_1col2col extends Document {
    
    var $slots = 3;
    var $machine_name = '1col2col';
    var $title = '1 Spalte | 2 Spalten';
    
    function __construct(\DynamicDocument\Manager $reference, $data) {
        parent::__construct($reference, $data);
    }
    
    function getLayoutHTML(){
        return '<div class="row document"><div class="col-xs-6 col-full">1</div><div class="row"><div class="col-xs-6 col-half">2</div><div class="col-xs-6 col-half">3</div></div></div>';
    }
    
    
    function render(){
    
        
    }
}


