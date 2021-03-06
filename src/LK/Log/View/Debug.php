<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Log\View;

/**
 * Description of Debug
 *
 * @author Maikito
 */
class Debug extends LogReader {
    //put your code here
    use \LK\UI\LogNotice;
    
    var $log_background = [
        'error' => "#ffdbce",
        'debug' => 'White',
        'cron' => "#f5f5f5",
        'kampagne' => "#ebd799"
    ];
    
   function __construct($data) {
        $this -> data = $data;
   }
   
  function render() {
        $data = $this -> data;
        
        $bg = "";
        $category = $this -> data -> category;
        
        if(isset($this -> log_background[$category])){
           $bg = $this -> log_background[$category];
        }
        
        $category_show = ucfirst($data -> category);
        
        if($data -> sub_category){
          $data -> message = '<strong>' . ucfirst($data -> sub_category) . '</strong> ' . $data -> message;
        }
        
        return $this ->out(
                $bg, 
                '<small>' .format_date($data -> request_time) . '</small> | '
                . \LK\u($data -> uid) . '<br />' . $data -> message  . $this -> getContext(), 
                '<small class="label label-primary">'. $category_show .' #'. $data -> id .'</small><br />' . $this ->getNode());
  }
}
