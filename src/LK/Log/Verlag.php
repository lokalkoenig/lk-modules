<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Log;

/**
 * Description of Verlag
 *
 * @author Maikito
 */
class Verlag extends LogInterface {
    //put your code here
    
    function __construct($message, $context = []) {
         $this -> init("verlag", $message, $context);
    }
    
    function setMerkliste($mid){
        $this ->setContext("merkliste", $mid);
    }
    
    function __toString() {
        return "Foobar";
    }
}
