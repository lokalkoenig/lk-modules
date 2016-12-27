<?php

$pdf->AddPage();
$pdf -> SetTopMargin(30);
$pdf -> SetLeftMargin(25);
$pdf -> SetRightMargin(120);

$pdf -> Ln(15); 

$pdf->SetFont(VKU_FONT,'B',28);
$pdf->MultiCell(0, 0, "Was spricht für die Werbung in ...", 0, 'L', 0); 

$pdf -> Ln(15); 

$pdf->SetFont(VKU_FONT,'',24);
$pdf->MultiCell(0, 10, "Tageszeitungen", 0, 'L', 0); 

$pdf->Image($module_dir .'shutterstock_64607632_small.jpg', 180, 30.2, 130);   

$pdf -> Ln(5); 

$pdf -> SetLeftMargin(30);


$pdf->SetFont(VKU_FONT,'',14);

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



?>