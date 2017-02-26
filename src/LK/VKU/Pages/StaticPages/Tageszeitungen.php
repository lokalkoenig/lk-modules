<?php

namespace LK\VKU\Pages\StaticPages;

/**
 * Description of Tageszeitungen
 *
 * @author Maikito
 */
class Tageszeitungen {

  var $module_dir = 'sites/all/modules/lokalkoenig/vku/pages';
  
  /**
   * Renders out the page
   * 
   * @param \LK\PDF\LK_PDF $pdf
   * @param \VKUCreator $vku
   * @param type $page
   */
  function render(\LK\PDF\LK_PDF $pdf, \VKUCreator $vku, $page){
   
    $pdf->AddPage();
    $pdf -> SetRightMargin(120);
    $pdf->addHeadline("Was spricht für die Werbung in ...");
    $pdf->SetFontClass('h2');
    $pdf->MultiCell(0, 10, "Tageszeitungen", 0, 'L', 0);
    $pdf->Image($pdf->getAssetDir() .'/shutterstock_64607632_small.jpg', 180, 30.2, 130);
    $pdf -> Ln(10);
    $pdf -> SetLeftMargin(30);
    $pdf->SetFontClass('big');

    $array = array();
    $array[] = 'hohe Glaubwürdigkeit (höchste Glaubwürdigkeit aller Medien)';
    $array[] = 'sehr hohe Leser-Blatt-Bindung (die meisten Leser sind Abonnenten seit etlichen Jahren)';
    $array[] = 'gut geeignete Werbeimpulse (kurzfristig buchbar)';
    $array[] = 'sehr gute Reichweite';
    $array[] = 'Leserschaft gehört meistens zu den eher Wohlhabenden';
    $array[] = 'PR-Text buchbar';
    $array[] = 'geographische, zielgerichtete Ausstreuung der Werbung durch Buchung einzelner Ausgaben';
    $array[] = 'thematische Aussteuerung, bei Buchung spezieller Rubriken, Inhalte, (Sonder-)Themen, Beilagen o.ä.';

    $pdf->SetFillColor(69, 67, 71);
    foreach($array as $text){
     $x = $pdf -> GetY();
     $pdf -> Rect(26 , $x + 2.5 , 2.5 , 2.5, 'F');
     $pdf->MultiCell(0, 8, $text, 0, 'L', 0);
     $pdf -> Ln(2);
    }
  }
}
