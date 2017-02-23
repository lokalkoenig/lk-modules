<?php

/**
 * @changed 2015-11-03 Shanghai
 * @author Maikito
 */

$pdf -> SetLeftMargin(0);
$pdf->AddPage();
$pdf -> SetRightMargin(30);
$pdf -> SetLeftMargin(30);
$pdf->SetFillColor($pdf -> title_bg_color[0], $pdf -> title_bg_color[1], $pdf -> title_bg_color[2]);
$pdf->Rect(0, 30, 297, 90, 'F');
$pdf->SetTextColor($pdf -> title_vg_color[0], $pdf -> title_vg_color[1], $pdf -> title_vg_color[2]);
$pdf->SetFont(VKU_FONT,'',50);

// If there is no Object, then we make a exception here
if(isset($vku) AND is_a($vku, "VKUCreator")){
    $titlev = $vku -> get("vku_title", false);
    $title_company = $vku -> get("vku_company", false);
    $title_unter = $vku -> get("vku_untertitel", false);
}
else{
   $titlev = 'Ihr Angebot';
    $title_company = 'Ihr Unternehmen';
    $title_unter = 'Untertitel'; 
}

$title = strlen($titlev);

if($title > 45){
  $pdf->SetFont(VKU_FONT,'',35); 
  $pdf->SetY(52); 
  $pdf -> SetRightMargin(30);
  $pdf -> SetLeftMargin(30);
  $pdf -> MultiCell(0, 25, $titlev, 0, "C", 0); //, 0, 0, 'C'); // [, integer ln] [, string align] [, integer fill] [, mixed link])
  $pdf -> SetRightMargin(0);
  $pdf -> SetLeftMargin(0);
}           
elseif($title > 25){
  $pdf -> SetRightMargin(30);
  $pdf -> SetLeftMargin(30);
  $pdf->SetY(58); 
  $pdf->SetFont(VKU_FONT,'',45); 
  $pdf -> MultiCell(0, 25, $titlev, 0, "C", 0); //, 0, 0, 'C'); // [, integer ln] [, string align] [, integer fill] [, mixed link])
  $pdf -> SetRightMargin(0);
  $pdf -> SetLeftMargin(0);
}
else {
  $pdf->MultiCell(0, 95, $titlev , 0, 'C', 0); 
}

$pdf->SetTextColor(69, 67, 71);
$pdf->SetY(125); 
$pdf -> Ln(15);
$pdf->SetFont(VKU_FONT,'B',25);
$pdf->MultiCell(0, 0,$title_company, 0, 'C', 0);    
$pdf->SetFont(VKU_FONT,'',20);
$pdf->MultiCell(0, 20, $title_unter , 0, 'C', 0);    
$pdf->MultiCell(0, 20, date("d.m.Y", time()) , 0, 'C', 0);    
