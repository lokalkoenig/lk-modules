<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Kampagne\Tests;
use LK\Tests\TestCase;
use LK\Kampagne\GIFExtractor;

/**
 * Description of GIFExtractorTest
 *
 * @author Maikito
 */
class GIFExtractorTest extends TestCase {
  //put your code here
  
  function build() {
 
    $gif = new GIFExtractor();
    $dir = $gif ->getDirectory();
    
    $last = null;
    $open = opendir($dir);
    while($item = readdir($open)){
      if($item == "." OR $item == "..") {
        continue;
      }  
      
      $last = $item;
    }
    $file = file_load($last);
    
    $return = $gif ->toArray((array)$file);
    $this -> printLine('Files / ' . $last, "<pre>" .print_r($return, true) . "</pre>");
    
    // Delete those
    foreach($return as $file_item){
      unlink($file_item);
    }
    
    $this -> printLine('Recreate', $last);
    $return2 = $gif ->toArray((array)$file, true);
    
    $this -> printLine('New Created / ' . $last, "<pre>" .print_r($return2, true) . "</pre>");
  }     
}
