<?php

namespace LK\VKU\Pages\StaticPages;

/**
 * Description of Title
 *
 * @author Maikito
 */
class Title {


  function renderTitlePage(\LK\PDF\LK_PDF $pdf, array $array){

    $title_bg_color = $pdf->getUserSettings('title_bg_color_rgb', [0,0,0]);
    $title_vg_color = $pdf->getUserSettings('title_vg_color_rgb', [255,255,255]);

    $pdf->AddPage();

    $pdf -> SetLeftMargin(0);
    $pdf -> SetRightMargin(30);
    $pdf -> SetLeftMargin(30);

    $pdf->SetFillColor($title_bg_color[0], $title_bg_color[1], $title_bg_color[2]);
    $pdf->SetTextColor($title_vg_color[0], $title_vg_color[1], $title_vg_color[2]);
    $pdf->Rect(0, 30, 297, 90, 'F');
    $pdf->SetFont('','',50);

    $titlev = $array['title'];
    $title_company = $array['company'];
    $title_unter = $array['underline'];
    $title = strlen($titlev);

    if($title > 45){
      $pdf->SetFont('','',35);
      $pdf->SetY(52);
      $pdf -> SetRightMargin(30);
      $pdf -> SetLeftMargin(30);
      $pdf -> MultiCell(0, 25, $titlev, 0, "C", 0);
      $pdf -> SetRightMargin(0);
      $pdf -> SetLeftMargin(0);
    }
    elseif($title > 25){
      $pdf -> SetRightMargin(30);
      $pdf -> SetLeftMargin(30);
      $pdf->SetY(58);
      $pdf->SetFont('','',45);
      $pdf -> MultiCell(0, 25, $titlev, 0, "C", 0);
      $pdf -> SetRightMargin(0);
      $pdf -> SetLeftMargin(0);
    }
    else {
      $pdf->MultiCell(0, 95, $titlev , 0, 'C', 0, '', '', 60);
    }

    $pdf->SetTextColor(69, 67, 71);
    $pdf->SetY(125);
    $pdf -> Ln(15);
    $pdf->SetFont('','B',25);
    $pdf->MultiCell(0, 0,$title_company, 0, 'C', 0);
    $pdf->SetFont('','',20);
    $pdf->MultiCell(0, 20, $title_unter , 0, 'C', 0);
    $pdf->MultiCell(0, 20, date("d.m.Y", time()) , 0, 'C', 0);
  }


  /**
   * Renders the Page
   *
   * @param \LK\PDF\LK_PDF $pdf
   * @param \VKUCreator $vku
   * @param array $page
   */
  function render(\LK\PDF\LK_PDF $pdf, \VKUCreator $vku, $page){
    
    $array = [
      'title' => $vku -> get("vku_title", false),
      'company' =>  $vku -> get("vku_company", false),
      'underline' =>  $vku -> get("vku_untertitel", false),   
    ];
    
    $this->renderTitlePage($pdf, $array);
  }
}
