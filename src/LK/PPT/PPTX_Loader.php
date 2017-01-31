<?php
namespace LK\PPT;

/**
 * Description of PPTX_Loader
 *
 * @author Maikito
 */
class PPTX_Loader {

  /**
   * Gives back an PPTX-Object
   *
   * @return \LK\PPT\LK_PPT_Creator
   */
  public static function load(){
    return new \LK\PPT\LK_PPT_Creator();
  }


  /**
   * Writes a PPT to a directory
   * 
   * @param \LK\PPT\LK_PPT_Creator $ppt
   * @param string $dir
   * @param string $file_name
   * @return string
   */
  public static function save(\LK\PPT\LK_PPT_Creator $ppt, $dir, $file_name){
    return $ppt ->write($dir, $file_name);
  }

}
