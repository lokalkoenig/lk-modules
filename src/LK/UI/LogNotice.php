<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\UI;

/**
 * Description of LogNotice
 *
 * @author Maikito
 */
trait LogNotice {
    //put your code here\
    function out($background, $col1, $col2){
        return '<div class="well well-white well-log" style="background-color:'. $background .'; ">'
                . '<div class="row">'
                . '<div class="col-xs-8">'. $col1 .'</div>'
                . '<div class="col-xs-4 text-right">'. $col2 .'</div>'
                . '</div>'
                . '</div>';
    }
}
