<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\UI\Kampagne;

/**
 * Description of Teaser
 *
 * @author Maikito
 */
class Teaser {
  //put your code here
  
  public static function get($nid){
    
    $node = \node_load($nid);
    $view = \node_view($node, 'teaser');
    
  return \drupal_render($view);  
  }
}
