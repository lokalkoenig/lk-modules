<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Admin\Cron;

/**
 * Description of RemoveOrphanedFiles
 *
 * @author Maikito
 */
class RemoveOrphanedFiles {
  
  public static function executeCron(){
    
    // Delete unused preview files
    $dirname = 'sites/default/private/vkutest/';
  
    $time = time() - (60*10); 
    $dir = opendir($dirname);
    while($date = readdir($dir)){
      if($date == '.' OR $date == '..') continue;

      $filetime = filemtime($dirname . "/" . $date);

      // Löschen wenn älter als 10 Minuten
      if($filetime < $time){
        unlink($dirname . "/" . $date);
      } 
    }
    
  }
}
