<?php

namespace LK\VKU\Pages\StaticPages;

/**
 * Description of Online
 *
 * @author Maikito
 */
class Online {

  /**
   * Renders out the page
   * 
   * @param \LK\PDF\LK_PDF $pdf
   * @param \VKUCreator $vku
   * @param type $page
   */
  function render(\LK\PDF\LK_PDF $pdf, \VKUCreator $vku, $page){

    $pdf->AddPage();
    $pdf -> SetRightMargin(125);
    $pdf->Image($pdf->getAssetDir() .'/shutterstock_online_small.jpg', 180, 30.2, 130);
    $pdf->addHeadline("Was spricht für die ...");
 
    $pdf -> SetLeftMargin(30);
    $pdf->SetFontClass('h2');
    $pdf->MultiCell(0, 0, "Online Werbung (Display-Ads)", 0, 'L', 0);
    $pdf -> Ln(10);
    $pdf -> SetLeftMargin(34);
    $pdf->SetFontClass('big');
  
    $array = array();
    $array[] = 'geeignet zum Image-Aufbau, zur Adressgenerierung und für den Verkauf von Produkten oder Dienstleistungen';
    $array[] = 'kann sofortige Handlungsimpulse erzeugen';
    $array[] = 'thematische Aussteuerung der Werbung, bei Buchung spezieller Rubriken, Inhalte, (Sonder-)Themen';
    $array[] = 'PR-Text bzw. thematische Micro-Sites buchbar';
    $array[] = 'Werbeerfolgskontrolle: Gute Messbarkeit der Werbeeinblendungen, Klick-Raten o.ä.';
    $array[] = 'Werbeinhalt kann problemlos geändert werden';
    $array[] = 'aktive Kundenansprache: zahlreiche Möglichkeiten der Interaktivität';

    $pdf->SetFillColor(69, 67, 71);

    foreach($array as $text){
      $x = $pdf -> GetY();
      $pdf -> Rect(30 , $x + 2.5 , 2.5 , 2.5, 'F');
      $pdf->MultiCell(0, 8, $text, 0, 'L', 0);
      $pdf -> Ln(2);
    }
  }
}
