<?php

/**
 * DEPRICATED
 */
function lokalkoenig_test_gif(){  }

/**
 * 
 * @param Array $file
 * @return boolean|string
 */
function lk_process_gif_ani_refactored($file){
 
  $url = file_create_url($file["uri"]);
  $dir = 'sites/default/private/gifs';
  
  // URL is not an GIF
  if (!GifFrameExtractor::isAnimatedGif($url)) {
      return false;
  }
  
  $dir_name = $dir . "/" . $file["fid"];
  if(!is_dir($dir_name)){
      drupal_mkdir($dir_name);
  } 
  else {
      $array = array();
      $data = opendir($dir_name);
      while($all = readdir($data)){
          if(is_dir($dir_name . "/" . $all)) {
              continue;
          }
          $array[] = $dir_name . "/" . $all; 
      }
      
      if($array):
          return $array;
      endif;
  }
  
  $gfe = new GifFrameExtractor();
  
  // Hook File-system to make the URL relative
  $url = str_replace("http://lk.dev/system/files/varianten", "sites/default/private/varianten", $url);
  $url = str_replace("http://www.lokalkoenig.de/system/files/varianten", "sites/default/private/varianten", $url);
  $url = str_replace("http://lk.dev/sites/default/files/varianten", "sites/default/files/varianten", $url);
  $url = str_replace("http://www.lokalkoenig.de/sites/default/files/varianten", "sites/default/files/varianten", $url);
 
  $size = getimagesize($url);
  $gfe->extract($url, true);   
  
  $array = array(); 
  $x = 0;
  
  $pos = ($gfe->getFramePositions());
  $data = $gfe -> frameSources;
  $last = null;
  
  foreach ($gfe->getFrameImages() as $frame) :
       $name = $x . '.gif';
       
       imagegif($frame, $dir . "/" . $file["fid"] . "/" . $name);
       $new_size =  getimagesize($dir . "/" . $file["fid"] . "/" . $name);
       
       if($x != 0){
          $dest = imagecreatefromgif($dir_name . '/' . $last);
          imagecopymerge($dest, $frame, $pos[$x]["x"], $pos[$x]["y"], 0, 0, $new_size[0], $new_size[1], 99);
          imagegif($dest, $dir . "/" . $file["fid"] . "/" . $name); 
       }
       
       $last = $name;
       $array[] = $dir . "/" . $file["fid"] . "/" . $name;
       
       $x++;       
   endforeach;
   
   if(!$array){
       return false;
   }
   
   return $array;
}


?>