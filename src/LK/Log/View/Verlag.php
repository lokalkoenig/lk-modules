<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Log\View;

/**
 * Description of Kam
 *
 * @author Maikito
 */
class Verlag extends LogReader {
    //put your code here
    
    function __construct($data) {
        $this -> data = $data;
    }
    
    function render() {
        $data = $this -> data;
        
        
        return '<div class="well well-white well-log clearfix"><div class="row"><div class="col-xs-8">'
                . '<small>' .format_date($data -> request_time) . '</small> | '
                . \LK\u($data -> uid) . '<br />' . $data -> message . $this -> getContext()
                . '</div><div class="text-right col-xs-4">'
                . '<small class="label label-default">#'. $data -> id .'</small><br />'
                . $this ->getNode() 
                . '</div></div></div>';
    }
}
