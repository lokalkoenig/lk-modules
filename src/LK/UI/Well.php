<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\UI;

/**
 * Description of Well
 *
 * @author Maikito
 */
trait Well {
    //put your code here
    function construct($data, $class = "well well-white") {
        return '<div class="well well-white">'. $this -> value .'</div>';
    }   
}
