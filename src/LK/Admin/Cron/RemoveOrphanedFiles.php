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
    $dirname = 'sites/default/files/test/';
  
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

    // Remove some Entries from Watchdog
    db_query("DELETE FROM watchdog WHERE message='Login attempt failed for %user.' OR type IN ('access denied', 'page not found')");

    $x = 0;
    $vku_dir = \LK\VKU\Export\Manager::save_dir;
    $dir_vku = opendir($vku_dir);
    while($file = readdir($dir_vku)) {

      if(in_array($file, ['.', '..'])) {
        continue;
      }

      $explode = explode('.', $file);
      $id = $explode[0];

      $test = \LK\VKU\VKUManager::getVKU($id);
      if(!$test) {
        unlink($vku_dir . '/' . $file);
        $x++;
      }
    }
    closedir($dir_vku);
 }
}
