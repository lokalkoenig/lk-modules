<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK;


/**
 * Description of Component
 *
 * @author Maikito
 */
abstract class Component {
    //put your code here
    use \LK\Log\LogTrait;
    
    /**
     * Gets back the current User
     * 
     * @return \LK\User
     */
    static function getCurrentUser(){
        return current();
    }
    
}
