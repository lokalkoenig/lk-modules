<?php

namespace LK\VKU\Export;
use LK\VKU\PageManager;

/**
 * Description of Manager
 *
 * @author Maikito
 */
class Manager extends PageManager {

  function __construct(\VKUCreator $vku) {
    parent::__construct($vku);
  }
  
  /**
   * Finalize the VKU and saves the PDF/PPTX
   *
   * @param \VKUCreator $vku
   * @return boolean
   */
  function finalizeVKU(){
    $vku = $this->getVKU();
    $pdf = $this->generatePDF();
    $fn = $vku -> getId() . ".pdf";
    $file_path = $_SERVER['DOCUMENT_ROOT'] . $this->save_dir .'/'. $fn;
    $pdf->Output($file_path, 'F');

    // PPTX
    if(\vku_is_update_user_ppt()):
      $pptx = $this->generatePPTX();
      $file_pptx = \LK\PPT\PPTX_Loader::save($pptx, $this->save_dir, $vku ->getId());
      $vku -> set('vku_ppt_filename', $file_pptx);
      $file_size = filesize($this->save_dir . '/' . $file_pptx);
      $vku -> set('vku_ppt_filesize', $file_size);
    endif;

    $vku -> set("vku_ready_filename", $fn);
    $vku -> set("vku_ready_time", time());
    $vku -> set("vku_ready_filesize", filesize($file_path));

    return true;
  }

    /**
   * Gets back a generated PDF from the VKU
   * @param $line_item optional-page-id
   * @param boolean $output direct
   */
  function generatePDF($line_item = 0, $output = false){

    $vku = $this->getVKU();
    $pdf = \LK\PDF\PDF_Loader::getPDF($vku ->getAuthor());
    $pages = $vku -> getPages();

    while(list($key, $page) = each($pages)){
      if(!$page["data_active"]) {
          continue;
      }

      if($line_item && $line_item != $key){
        continue;
      }

      $mod = $this->getModule($page["data_module"]);
      if($mod){
        $mod->getOutputPDF($page, $pdf);
      }
    }

    if($output){
      \LK\PDF\PDF_Loader::output($pdf);
    }

    return $pdf;
  }

  /**
   * Gets back a generated PDF from the VKU
   * @param $line_item optional-page-id
   * @param boolean $output direct
   */
  function generatePPTX($line_item = 0){

    $vku = $this->getVKU();
    $ppt = \LK\PPT\PPTX_Loader::load();
    $ppt ->setVKU($vku);
    $pages = $vku -> getPages();

    while(list($key, $page) = each($pages)){
      if(!$page["data_active"]) {
          continue;
      }

      if($line_item && $line_item != $key){
        continue;
      }

      $mod = $this->getModule($page["data_module"]);
      if($mod){
        $mod->getOutputPPT($page, $ppt);
      }
    }

    return $ppt;
  }



  /**
   * Generates a Sample Kampagne
   */
  public static function generateSampleKampagne($pdf, $node){
    \LK\VKU\Pages\PageKampagne::_vku_load_vku_settings($node);
    $manager = new \LK\VKU\Pages\Kampagne\Kampagne($node);
    $manager ->render($pdf);
  }
}
