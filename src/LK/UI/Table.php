<?php
namespace LK\UI;

/**
 * Description of Table
 *
 * @author Maikito
 */
class Table implements Component {
    //put your code here
    var $header = array();
    var $class_names = 'table table-striped table-hover';
    var $rows = array();
    
    function setHeader($header){
        $this -> header = $header;
    }
    
    function addRow($row){
        $this -> rows[] = $row;
    }
    
    function __toString() {
        $table_attributes = array('class' => $this -> class_names);
        return theme('table', array('attributes' => $table_attributes, 'header' => $this -> header, "rows" => $this -> rows)); //$this -> header, $this -> rows, $table_attributes);
    }
}
