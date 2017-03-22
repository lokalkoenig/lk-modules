<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Files;

/**
 * Description of FileGetter
 *
 * @author Maikito
 */
class FileGetter {
  
    const PLACEHOLDER = 'sites/default/files/vkucache/img-placeholder-160x160.png';
    
    //put your code here
    public static function get($url){
        //$hash = md5($url);
        //dpm($url);
        // remove ?
        $path_parts = pathinfo($url);    
        $file_type = $path_parts['extension'];
        
        // itok from drupal
        $explode = explode("?", $file_type);
        $file_type = $explode[0];
        
        $md5 = md5($url) . '.'. $file_type;
        $file_save = 'sites/default/files/vkucache/' . $md5;
        
        if(!file_exists($file_save)):
            $mydir = 'public://vkucache';
            file_prepare_directory($mydir, FILE_CREATE_DIRECTORY);
            $dir = drupal_realpath($mydir);
            
            $file = file_get_contents($url);
            if(!$file){
              $error = new \LK\Log\Debug('Die VKU Datei wurde nicht gefunden: ' . $url);
              $error ->setCategory('error');
              $error ->save();

              return self::PLACEHOLDER;
            }
            
            file_put_contents($dir . '/' . $md5, $file);
        endif;
        
    return $file_save;    
    }
}
