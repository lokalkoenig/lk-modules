<?php

namespace LK\PDF;

/**
 * Description of PDF_Loader
 *
 * @author Maikito
 */
class PDF_Loader {
    /**
     * Returns back an PDF-Object
     * 
     * @return \PDF
     */
    static function load(){
      require_once __DIR__ .'/pdf_class.php';
      
      $pdf = new \PDF('L');
      $pdf -> SetMargins(0);
      $pdf -> SetTopMargin(30);
      $pdf -> AliasNbPages();
      $pdf -> SetTextColor(69, 67, 71);

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
      $verlag = $account ->getVerlag();
      $pdf -> setVKUDefaults();
      
      if($account ->isModerator()){
        $pdf -> setVerlag(LK_TEST_VERLAG_UID);
      }
      else {
        $pdf -> setVerlag($verlag);
      }
      
      return $pdf;
    }

    /**
     * Outputs the PDF to the browser
     * 
     * @param \PDF $pdf
     */
    static function output(\PDF $pdf){
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
     * @return \PDF
     */
    static function renderTestNode(\stdClass $node, $output = true){
        $pdf = self::load();
        
        $manager = new \LK\VKU\PageManager();
        $manager->generateSampleKampagne($pdf, $node);
        
        if($output){
          self::output($pdf);
        }
        
        return $pdf;
    } 
}
