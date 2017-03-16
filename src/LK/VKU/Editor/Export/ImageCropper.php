<?php

namespace LK\VKU\Editor\Export;

use JBZoo\Image\Image;

/**
 * Description of ImageCropper
 *
 * @author Maikito
 */
class ImageCropper {

  const SAFE_DIR = 'public://vkucache';

  /**
   * Processes an Croppie-Based images and gives the Cropped version
   * 
   * @param \stdClass $file
   * @param array $options
   * @return string Image-URL
   */
  public static function process(\stdClass $file, $options){

    //$url = file_create_url($file->uri);
    $url = image_style_url('jpg', $file->uri);
    
    // if there are no croppie-information
    if(!isset($options['points']) && !isset($options['zoom'])){
      return $url;
    }

    $points = $options['points'];
    $fn = [$file->fid, $points[0], $points[1], $points[2], $points[3]];
    $fn[] = str_replace('.', '-', $options['zoom']);
    
    $ext = 'jpg';
    $filename = implode('-', $fn) . "." . $ext;

    $dir = drupal_realpath(self::SAFE_DIR);
    if(!file_exists($dir . '/' . $filename)){
      $img = new Image(file_get_contents($url));
      $img -> crop($points[0], $points[1], $points[2], $points[3]);

      if($img->getWidth() > 1000) {
        $img->bestFit(1000, 1000);
      }
   
      $img->saveAs($dir . '/' . $filename, 90);
    }

    return $dir . '/' . $filename;
  }
}
