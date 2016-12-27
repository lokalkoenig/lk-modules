<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Log;

/**
 * Description of Kampagne
 *
 * @author Maikito
 */
class Kampagne extends LogInterface{
    //put your code here
    
    function __construct($node_nid, $message) {
        $this ->setNid($node_nid);
        $this ->init('kampagne', $message);
    }
    
    function __toString() {
        return "Foobar";
    }
}
