<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Tests;

/**
 * Description of TestCase
 *
 * @author Maikito
 */
abstract class TestCase {
    //put your code here
    
    var $lines;
    var $output = '';
    
    protected abstract function build();
   
    public function run(){
        $time = microtime(true); 
        $this -> build();
        $time_elapsed_secs = (microtime(true) - $time);
        $return =(string)$this;
        return $return . "<hr /><p class='text-center'>". __NAMESPACE__ ."/Time: ".  round($time_elapsed_secs, 4) ."s</p><hr />";
    }
    
    protected function append($html){
        $this -> output .= $html;
    }

    function getForm(){
      return [];
    }

    protected function printLine($title, $value){
        $this -> lines[] = array($title, $value);
    }
    
    function printInfo($value){
        drupal_set_message($value);
    }
    
    function __toString() {
        if($this -> lines){
             $this -> output .= theme('table', array('header' => array(), 'rows' => $this -> lines));
        }
      
        return '<div class="well well-white">' . $this -> output . '</div>';
    }
}
