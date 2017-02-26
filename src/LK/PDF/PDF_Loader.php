<?php

namespace LK\PDF;
use LK\PDF\LK_PDF;


/**
 * Description of PDF_Loader
 *
 * @author Maikito
 */
class PDF_Loader {
    /**
     * Returns back an PDF-Object
     * 
     * @return \LK_PDF
     */
    static function load(){
      require_once __DIR__ .'/pdf_class.php';
      
      $pdf = new LK_PDF('L');
      return $pdf;
    }
    
    
    /**
     * Returns a branded PDF-Object
     * 
     * @param type $uid
     * @return \PDF
     */
    static function getPDF($uid){
      $pdf = self::load();
      
      $account = \LK\get_user($uid);
      $pdf->setUserSettings(\LK\VKU\VKUManager::getVKU_RenderSettings($account));
      return $pdf;
    }

    /**
     * Outputs the PDF to the browser
     * 
     * @param \LK_PDF $pdf
     */
    static function output(LK_PDF $pdf){
      drupal_get_messages();
      ob_clean();
      $pdf->Output();
      drupal_exit(); 
    }

    /**
     * Renders a Test-Node
     * 
     * @param \stdClass $node
     * @param type $output
     * @return \LK_PDF
     */
    static function renderTestNode(\stdClass $node, $output = true){
      $pdf = self::getPDF(\LK\current()->getUid());
        
      $manager = new \LK\VKU\Export\Manager();
      $manager->generateSampleKampagne($pdf, $node);
        
      if($output){
        self::output($pdf);
      }
        
      return $pdf;
    } 
}
