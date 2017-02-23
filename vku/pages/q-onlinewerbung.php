<?php

$pdf->AddPage();
$pdf->SetTextColor(69, 67, 71);
$pdf -> SetTopMargin(30);
$pdf -> SetLeftMargin(25);
$pdf -> SetRightMargin(125);
$pdf -> Ln(15); 
$pdf->Image($module_dir .'/shutterstock_online_small.jpg', 180, 30.2, 130);   
$pdf->SetFont(VKU_FONT,'B',28);
$pdf->MultiCell(0, 0, "Was spricht für die ...", 0, 'L', 0); 
$pdf -> SetLeftMargin(40);
$pdf -> Ln(15); 
$pdf->SetFont(VKU_FONT,'',24);
$pdf->MultiCell(0, 10, "Online Werbung (Display-Ads)", 0, 'L', 0); 
$pdf -> Ln(5); 
$pdf -> SetLeftMargin(44);
$pdf->SetFont(VKU_FONT,'',14);

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
 $pdf -> Rect(40 , $x + 2.5 , 2.5 , 2.5, 'F');
 $pdf->MultiCell(0, 8, $text, 0, 'L', 0); 
 $pdf -> Ln(2);
}
