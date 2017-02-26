<?php

namespace LK\VKU\Export;
use LK\VKU\PageManager;

/**
 * Description of Manager
 *
 * @author Maikito
 */
class Manager extends PageManager {

  function __construct() {
    parent::__construct();
  }
  
  /**
   * Finalize the VKU and saves the PDF/PPTX
   *
   * @param \VKUCreator $vku
   * @return boolean
   */
  function finalizeVKU(\VKUCreator $vku){
    $pdf = $this->generatePDF($vku);
    $fn = $vku -> getId() . ".pdf";
    $file_path = $_SERVER['DOCUMENT_ROOT'] . $this->save_dir .'/'. $fn;
    $pdf->Output($file_path, 'F');

    // PPTX
    if(\vku_is_update_user_ppt()):
      $pptx = $this->generatePPTX($vku);
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
   * @param \VKUCreator $vku
   * @param $line_item optional-page-id
   * @param boolean $output direct
   */
  function generatePDF(\VKUCreator $vku, $line_item = 0, $output = false){

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
   * @param \VKUCreator $vku
   * @param $line_item optional-page-id
   * @param boolean $output direct
   */
  function generatePPTX(\VKUCreator $vku, $line_item = 0){

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
  function generateSampleKampagne($pdf, $node){
    $obj = $this->getModule('node');
    $obj ->getOutputPDF(['node' => $node, 'data_serialized' => ''], $pdf);
  }
}
