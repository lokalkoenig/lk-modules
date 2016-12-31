<?php
namespace LK\UI;

/**
 * Description of Table
 *
 * @author Maikito
 */
trait Table {
    //put your code here
    var $header = array();
    var $class_names = 'table table-striped table-hover';
    var $rows = array();
    
    function UI_Table_setHeader($header){
        $this -> header = $header;
    }
    
    function UI_Table_addRow($row){
        $this -> rows[] = $row;
    }
    
    function UI_Table_render() {
        $table_attributes = array('class' => $this -> class_names);
        return theme('table', array('attributes' => $table_attributes, 'header' => $this -> header, "rows" => $this -> rows)); //$this -> header, $this -> rows, $table_attributes);
    }
}
