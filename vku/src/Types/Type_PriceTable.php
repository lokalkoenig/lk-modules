<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Types;
use Base\DocumentType as Document;

class Type_PriceTable extends Document {
    
    var $slots = 0;
    var $machine_name = 'price-table';
    var $title = 'Preiskalkulation';
    var $entity_type = 'vkuaddon';
    var $bundle = 'vkuaddon_preise';
    var $category = 'sonstiges';
    
    
    
    
    function __construct(\DynamicDocument\Manager $reference, $data) {
        parent::__construct($reference, $data);
    }
    
    public function getLayoutHTML(){
        return '<div class="row document"><div class="row"><div class="col-xs-12">Preiskalkulation</div></div></div>';
    }
    
    public function getForm(){
        
    }
    
    public function create($category, $document){
        
        
    }
    
    
    
}