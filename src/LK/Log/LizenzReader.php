<?php
namespace LK\Log;

use LK\Lizenz;

/**
 * Drupal integration
 *
 * @author Maikito
 */
class LizenzReader extends \views_handler_field {
    
    use \LK\UI\Well;
    
    function render($values) {
        //ID if the value
        $value = $this->get_value($values);
        
        $lizenz = new Lizenz($value);
        
        if($lizenz -> is()){
          return $this ->construct($lizenz ->getSummary());
        }
    }
    
    function loadLog($id){
        //$dbq = db_query("SELECT * FROM " . \LK\Log\LogInterface::DB_TABLE . " WHERE id='". $id ."'");
        //return $dbq -> fetchObject();
    }   
}