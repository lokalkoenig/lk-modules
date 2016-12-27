<?php

$pdf->AddPage();
$pdf -> SetTopMargin(30);
$pdf -> SetLeftMargin(25);
$pdf -> SetRightMargin(25);

$pdf -> Ln(15); 
$pdf->Image($module_dir .'shutterstock_43524835_small.jpg', 0, 71.5, 170);   

$pdf -> SetLeftMargin(120);
$pdf->SetFont(VKU_FONT,'B',28);
$pdf->MultiCell(0, 0, "Was spricht für die Werbung in ...", 0, 'R', 0); 
$pdf -> SetLeftMargin(165);
$pdf -> Ln(25); 

$pdf->SetFont(VKU_FONT,'',24);
$pdf->MultiCell(0, 10, "Wochen-/Anzeigenblättern", 0, 'R', 0); 



$pdf -> Ln(5); 

$pdf -> SetLeftMargin(172);


$pdf->SetFont(VKU_FONT,'',14);

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



?>