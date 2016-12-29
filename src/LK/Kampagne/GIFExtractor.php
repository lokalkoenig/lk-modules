<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Kampagne;
use GifFrameExtractor\GifFrameExtractor;

/**
 * Extracts the frames from a GIF
 */
class GIFExtractor {
  
  var $file = null;
  var $dir = 'sites/default/private/gifs';
  
  /**
   * Gets the GIF Directory in the system
   * 
   * @return String Directory
   */
  public function getDirectory(){
    return $this -> dir;
  }
  
  /**
   * Retrieve an Array
   * 
   * @param type $file
   * @param boolean $force
   * @return boolean|Array
   */
  public function toArray($file, $force = false){
     $this -> file = $file;
     $url = file_create_url($this -> file["uri"]);
     
     if (!GifFrameExtractor::isAnimatedGif($url)) {
       \LK\Component::logError('['. $file['fid'] .'] File is not a valid Gif ' . $url);
        return false;
     }
     
     $dir_name = $this -> dir . "/" . $this -> file["fid"];
     
     if(!is_dir($dir_name) || $force === true){
       $this -> extractGif();
     }
     
     return $this ->readFromDirectory();
  }
  
  /**
   * Extract the frames from a GIF
   */
  private function extractGif(){
    
    $gfe = new GifFrameExtractor();
    $url = file_create_url($this -> file["uri"]);
    $fid = $this -> file["fid"];
   
    $dir_name = $this -> dir . "/" . $fid;
    
    if(!is_dir($dir_name)){
      drupal_mkdir($dir_name);
    }
    
    // Hook File-system to make the URL relative
    $url = str_replace($GLOBALS['base_url'] . "/sites/default/files/varianten", "sites/default/files/varianten", $url);
    
    $size = getimagesize($url);
    $gfe -> extract($url, true);   
    $x = 0;
  
    $pos = $gfe->getFramePositions();
    $last = null;
  
    foreach ($gfe->getFrameImages() as $frame) :
         $name = $x . '.gif';
        
         imagegif($frame, $dir_name . "/" . $name);
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
  
  /**
   * Reads the actual File-Directory
   * 
   * @return Array
   */
  private function readFromDirectory(){
    
    $dir_name = $this -> dir . "/" . $this -> file['fid'];
    if(!is_dir($dir_name)){
      return array();
    }
   
    $array = array();
    
    $data = opendir($dir_name);
    if(!$data){
      return array();
    }
    
    while($all = readdir($data)){
      if(is_dir($dir_name . "/" . $all)) {
          continue;
      }
      $array[] = $dir_name . "/" . $all; 
    }
  
  return $array;    
  }
}
