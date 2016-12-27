<?php

namespace LK\PDF;

/**
 * Description of PDF_Loader
 *
 * @author Maikito
 */
class PDF_Loader {
    //put your code here
    
    static function load(){
    
        require_once __DIR__ .'/pdf_class.php';
        $pdf = new \PDF('L');
         $pdf -> SetMargins(0);
         $pdf -> SetTopMargin(30);
         $pdf -> AliasNbPages();
         $pdf -> SetTextColor(69, 67, 71);

    return $pdf;    
    }
    
    static function renderTestNode(\stdClass $node){
        $pdf = self::load();
        
        // refactor later
        require('sites/all/modules/lokalkoenig/vku/pages/b-medias.php');
        drupal_get_messages();
        $pdf->Output();
        drupal_exit();
    }
    
}
