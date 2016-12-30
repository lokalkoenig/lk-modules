<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Log;



/**
 * Description of Reader
 *
 * @author Maikito
 */
class Reader extends \views_handler_field {
    //put your code here
    
     function render($values) {
        //ID if the value
        $value = $this->get_value($values);
        $complete_entry = $this ->loadLog($value);
        
        if($complete_entry -> category == "verlag"){
            $object = new View\Verlag($complete_entry);
        }
        else {
            $object = new View\Debug($complete_entry); 
        }
        
    return $object -> render();    
    }
    
    function loadLog($id){
        $dbq = db_query("SELECT * FROM " . \LK\Log\LogInterface::DB_TABLE . " WHERE id='". $id ."'");
        return $dbq -> fetchObject();
    }   
}




