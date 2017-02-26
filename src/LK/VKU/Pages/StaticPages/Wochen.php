<?php

namespace LK\VKU\Pages\StaticPages;

/**
 * Description of Wochen
 *
 * @author Maikito
 */
class Wochen {

  /**
   * Renders out the page
   *
   * @param \LK\PDF\LK_PDF $pdf
   * @param \VKUCreator $vku
   * @param type $page
   */
  function render(\LK\PDF\LK_PDF $pdf, \VKUCreator $vku, $page){

    $pdf->AddPage();
    $pdf->Image($pdf->getAssetDir() .'/shutterstock_43524835_small.jpg', 0, 71.5, 170);
    $pdf -> SetLeftMargin(120);

    $pdf->addHeadline("Was spricht für die Werbung in ...", 'R');
    $pdf -> SetLeftMargin(165);
    
    $pdf->SetFontClass('h2');
    $pdf->MultiCell(0, 0, "Wochen-/Anzeigenblättern", 0, 'L', 0);
    $pdf -> Ln(10);
    $pdf -> SetLeftMargin(172);
    $pdf->SetFontClass('big');

    $array = array();
    $array[] = 'kostenlos für jeden Leser';
    $array[] = 'sehr hohe Reichweite';
    $array[] = 'Verteilung auch an Werbeverweigerer (Haushalte mit dem Aufkleber "Bitte keine Werbung einwerfen")';
    $array[] = 'generell sehr hohe Akzeptanz bei den Lesern';
    $array[] = 'oft das entscheidende Medium für den geplanten Einkauf';
    $array[] = 'PR-Artikel buchbar';

    $pdf->SetFillColor(69, 67, 71);

    foreach($array as $text){
     $x = $pdf -> GetY();
     $pdf -> Rect(168 , $x + 2.5 , 2.5 , 2.5, 'F');
     $pdf->MultiCell(0, 8, $text, 0, 'L', 0);
     $pdf -> Ln(2);
    }
  }
}
