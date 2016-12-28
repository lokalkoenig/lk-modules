<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Kampagne;

use GifFrameExtractor;

class GIFExtractor {
  
  var $file = null;
  
  var $dir = 'sites/default/private/gifs';
  
  function __construct($file) {
    $this -> file = $file;
  }
  
  function toArray(){
     $url = file_create_url($this -> file["uri"]);
     
     if (!GifFrameExtractor::isAnimatedGif($url)) {
        return false;
     }
     
     $dir_name = $this -> dir . "/" . $file["fid"];
     
     if(!is_dir($dir_name)){
        $this -> extractGif();
     }
     
     return $this ->readFromDirectory();
  }
  
  
  private function extractGif(){
    
    
    
    
    $gfe = new GifFrameExtractor();
    $url = file_create_url($this -> file["uri"]);
    $fid = $this -> file["fid"];
   
    $dir_name = $this -> dir . "/" . $fid;
    drupal_mkdir($dir_name);

    // Hook File-system to make the URL relative
    $url = str_replace("http://lk.dev/system/files/varianten", "sites/default/private/varianten", $url);
    $url = str_replace("http://www.lokalkoenig.de/system/files/varianten", "sites/default/private/varianten", $url);
    $url = str_replace("http://lk.dev/sites/default/files/varianten", "sites/default/files/varianten", $url);
    $url = str_replace("http://www.lokalkoenig.de/sites/default/files/varianten", "sites/default/files/varianten", $url);

    $size = getimagesize($url);
    $gfe -> extract($url, true);   
  
    $array = array(); 
    $x = 0;
  
    $pos = $gfe->getFramePositions();
    $last = null;
  
    foreach ($gfe->getFrameImages() as $frame) :
         $name = $x . '.gif';

         imagegif($frame, $dir . "/" . $fid . "/" . $name);
         $new_size =  getimagesize($dir_name . "/" . $name);

         if($x != 0){
            $dest = imagecreatefromgif($dir_name . '/' . $last);
            imagecopymerge($dest, $frame, $pos[$x]["x"], $pos[$x]["y"], 0, 0, $new_size[0], $new_size[1], 99);
            imagegif($dest, $dir_name . "/" . $name); 
         }

         $last = $name;
         $x++;       
     endforeach;
     
     \LK\Component::logNotice('Generated ' . $x . " frames from " . $url);
  }
  
  private function readFromDirectory(){
    
    $dir_name = $this -> dir . "/" . $this -> file['fid'];
    
    $array = array();
    $data = opendir($dir_name);
    while($all = readdir($data)){
      if(is_dir($dir_name . "/" . $all)) {
          continue;
      }
      $array[] = $dir_name . "/" . $all; 
    }
  
  return $array;    
  }
  
  
  static function testCase(){
    
    
    
  }
}
