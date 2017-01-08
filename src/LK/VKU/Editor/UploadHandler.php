<?php

namespace LK\VKU\Editor;
use LK\VKU\Editor\Manager;

/**
 * Description of UploadHandler
 *
 * @author Maikito
 */
class UploadHandler extends \LK\PXEdit\Upload\ImageUploader {
  
  /**
   * Constructor-Gives Information to 
   * the parent constructors
   */
  function __construct() {
    parent::__construct([
          "upload_dir" => \file_directory_temp() . "/", 
          'upload_url' => \file_directory_temp() . "/"]
    );
  }
  
  /**
   * Generates the Response
   * Saves the Image to the Database
   * 
   * @param type $content
   * @param type $print_response
   */
  function generate_response($content, $print_response = true){
           // one file
    $manager = new Manager();
    $derivates = $manager ->getImagePresets();
      
    foreach ($content["files"] as $file){
        $handle = \file_get_contents(\file_directory_temp() . '/' .$file -> name);
        $drupal_file = \file_save_data($handle, 'public://editor_files/' . \transliteration_clean_filename($file -> name), \FILE_EXISTS_RENAME);
      
        $json = [];
        $json['image_id'] = $drupal_file -> fid;
      
        $json['versions'] = [];
        while(list($key, $val) = each($derivates)){
          $json['versions'][$key] = image_style_url($key, $drupal_file -> uri);;
        }
               
        unlink(\file_directory_temp() . '/' .$file -> name);
        $manager ->sendJson($json);
      }
   }
}
