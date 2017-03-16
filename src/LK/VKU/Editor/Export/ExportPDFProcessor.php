<?php

namespace LK\VKU\Editor\Export;

use LK\PDF\LK_PDF;
use \LK\VKU\Editor\Document;
use LK\VKU\Editor\Export\Interfaces\ExportProcessorInterface;

/**
 * ExportProcessor for PDF
 *
 * @author Maikito
 */
class ExportPDFProcessor extends ExportProcessorInterface {

  var $pdf = null;
  var $debug = FALSE;
  var $text_sep = 5;
  var $calculated_height = 0;

  function __construct(Document $document) {
    parent::__construct($document);

    if(isset($_GET['debug'])){
      $this->debug = TRUE;
    }
  }

  /**
   * Gets the PDF Object
   *
   * @return LK_PDF
   */
  private function getPDF(){
    return $this->pdf;
  }

  private function addPageTitle($title){
    $pdf = $this->getPDF();

    $pdf->AddPage();
    $pdf->addHeadline($title);
  }

  private function addFootnote($footnote){
    $pdf = $this->getPDF();

    $pdf->setX(25);
    $pdf->setY(174.5);
    $pdf ->SetLeftMargin(25);
    $pdf ->SetRightMargin(25);
    $pdf ->SetFont('', '', 8);
    //$pdf->Cell();
    $pdf->setColor('text', 119, 119, 119);
    $pdf->Cell(0, 10, $footnote, 0, 0, 'R');
  }

  function setCalculatedRegionHeight($height){
    $this->calculated_height = $height;
  }

  function getCalculatedRegionHeight(){
    return $this->calculated_height;
  }

  /**
   * Processed the PDF
   *
   * @param LK_PDF $pdf
   */
  public function processPDF(LK_PDF $pdf){
    $this -> pdf = $pdf;
    
    $document = $this ->getDocument();
    $defintion = $this->getLayoutDefinition();
    $content = $document->getContent();
    $this->addPageTitle($document->getPageTitle());
  
    $left_y = 25;
    $top_x = 55;

    $text_sep = $this->text_sep;

    $height_100 = 115;
    $width_100 = 297 - (2 * $left_y) + $text_sep ;

    $top_run = $top_x;
    $left_run = $left_y;

    $fields = 0;
    $regions_processed = 0;

    while(list($key, $val) = each($defintion)){

      if($val['height'] === 'auto'){
        $height_region = ($height_100 * 100) / 100;
      }
      elseif($val['height'] === 'calc') {
        $this->setCalculatedRegionHeight($pdf -> getY() - $top_x);
        $height_region = $height_100 - ($pdf -> getY() - $top_x + $text_sep);
      }
      else {
        $height_region = ($height_100 * $val['height']) / 100;
      }

      $width_region = ($width_100 * $val['width']) / 100;
      $top_run = $top_x;
    
      foreach($val['fields'] as $region){

        if($val['height'] === 'auto'){
          $content_height = -1;
        }
        // Continue on height = 0
        elseif($region['height'] === 0){
          $fields++;
          continue;
        }
        elseif($region['height'] === 100){
          $content_height = $height_region + $text_sep;
        }
        else {
          $content_height = ($region['height'] * $height_region) / 100;
        }

        if(isset($region['left'])){
          $top_run = $top_run_region + $text_sep;

          if($region['left'] === 0){
            $left_run = 25;
          }

          if($region['left'] === 33){
            $left_run = 110;
          }

          if($region['left'] === 66){
            $left_run = 193.8;
          }

          if($region['left'] === 50){
            $left_run = 152;
          }
        }

        if(isset($region['top'])){
          if($region['top'] === 0){
            $top_run = $top_x;
          }
        }

        $content_width = ($region['width'] * $width_region) / 100;

        $this->PDF_Position($pdf, $left_run, $top_run, $content_width, $content_height);
        $field = $content[$fields];
        $this->addPDFContent($pdf, $content_width - $text_sep, $content_height, $field);

        $top_run += $text_sep + $content_height;
        $fields++;
      }
      $top_run_region = $pdf ->GetY();
      $regions_processed++;
      $left_run += $width_region + ($text_sep / 3);
    }
    
    $this->addFootnote($document ->getFootnote());
  }

  private function PDF_Position(LK_PDF $pdf, $x, $y, $width, $height){

    $pdf -> SetXY($x, $y);

    $width_100 = 297 - 50;
    $content_spacing = $this->text_sep;

    // calc margins
    $margin_right = 25;
    $margin_left = 25;

    if($x === 25){
      $margin_right = 297 - $x - $width + ($content_spacing); //(float)$width_100 - (float)($width) + (float)($content_spacing / 2);
    }  
    else {
      $margin_left = $x;
      $margin_right_test = $x + $width;
      $margin_right = 297 - $margin_right_test + ($content_spacing);
    }

    $pdf -> SetLeftMargin($margin_left);
    $pdf -> SetRightMargin($margin_right - 0);
    $pdf ->SetFont(VKU_FONT, '', 10.5);
    
    if($this -> debug && $height !== -1){
      $pdf ->SetFont(VKU_FONT, '', 6);
      $pdf -> Text($x, $y - 4,  'X: ' . round($x, 1) . '; Y: ' . round($y, 1) . ", Height: " . round($height, 1) . "; Width: " . round($width, 1) . " / MR ". round($margin_right, 1) . '/ML ' . round($margin_left, 1));
      $pdf ->SetFont(VKU_FONT, '', 10.5);
      $pdf -> SetXY($x, $y);
    }
  }

  /**
   * Adds an Table-Widget to the PDF
   *
   */
  private function addTableWidget($value, $height, $width){

    $pdf = $this->getPDF();

    if($value['title']){
      $pdf ->SetFont('', '', 18);
      $pdf->Write(5, $value['title']);
      $pdf -> ln(10);
      $pdf ->SetX($pdf->getX());
    }

    $cellwidth = [50,50];

    if(count($value['rows'][0]) === 3){
      $cellwidth = [33, 33, 34];
    }

    if(count($value['rows'][0]) === 4){
      $cellwidth = [25, 25, 25, 25];
    }

    $pdf->SetFont('','', 10);
    $x = 0;
    $table = '<table cellspacing="0" cellpadding="4">';
    foreach($value['rows'] as $row){
      $table .= '<tr>';

      $bg_color = '#fff';

      if($x%2){
        $bg_color = '#eee';
      }
      $y = 0;
      foreach($row as $cell){
        $stripped = strip_tags($cell, "<br><b><strong><u><i><em>");
        $table .= '<td width="'. $cellwidth[$y] .'%" bgcolor="'. $bg_color . '">'. $stripped. '</td>';

        $y++;

      }
      
      $table .= '</tr>';
      $x++;
    }
    $table .= '</table>';

    $pdf->WriteHTML($table, true, false, true, false, '');
  }

  /**
   * Adds an Editor-Markup
   *
   * @param \simple_html_dom $dom
   * @param int $height
   */
  private function addEditorDocument(\simple_html_dom $dom, $height){

    $pdf = $this->getPDF();
    $y = $pdf->GetY();
    $maxY = $y + $height;

    $pdf->SetFont('DejaVu', '', 10);
    $body = $dom -> find('body');
    $x = 0;
    foreach($body[0] -> children() as $child){

      if($pdf ->GetY() > $maxY && $height !== -1){
        break;
      }

      // if empty
      if(empty($child -> plaintext)){
          continue;
      }

      if($x > 0){
        $pdf->Ln(2);
      }

      if($child -> tag === 'h2'){
        $pdf->SetFont('DejaVu', '', 18);
        $pdf->WriteOwnHTML($this->removeTrailingBR(html_entity_decode($child -> innertext)));
        $pdf->Ln(8);
      }

       if($child -> tag === 'h1'){
        $pdf->SetFont('DejaVu', '', 22);
        $pdf->WriteOwnHTML($this->removeTrailingBR(html_entity_decode($child -> innertext)));
        $pdf->Ln(8);
      }

      if($child -> tag === 'ol' OR $child -> tag === 'ul'){
        $ol = 1;
        $left_margin = $pdf ->getMarginLeft();
        $x_pos = $pdf->GetX();
        $max_items = count($child -> children());

        foreach($child -> children() as $li){
          $pdf->SetLeftMargin($left_margin);
          $pdf->SetX($x_pos);

          if($child -> tag === 'ol'){
            $pdf->Write(5, $ol . '.');
          }
          else {
            $pdf->Write(5, '•');
          }

          $pdf->SetLeftMargin($left_margin + 5);
          $pdf ->WriteOwnHTML($this->removeTrailingBR(html_entity_decode($li-> innertext)));
          
          if($ol !== $max_items){
            $pdf->Ln(4);
          }
          else {
            $pdf->Ln(2);
          }

          $pdf->SetLeftMargin($left_margin);
          $ol++;
        }
        $pdf->Ln(2);
        $pdf->SetLeftMargin($left_margin);
      }

      if($child -> tag === 'p'){
        $pdf ->WriteOwnHTML($this->removeTrailingBR(html_entity_decode($child-> innertext)));
        $pdf->Ln(4);
      }

      $pdf->SetFont('DejaVu', '', 10);
      $x++;
    }
  }

  /**
   * Adds an Document Type to the PDF
   *
   * @param \LK\PDF\LK_PDF $pdf
   * @param int $width
   * @param int $height
   * @param int $content
   */
  private function addPDFContent(\LK\PDF\LK_PDF $pdf, $width, $height, $content){

    $pdf ->SetFont('', '', 10);

    if($this -> debug && $height !== -1){
      $pdf->SetFillColor(254, 254, 254);
      $pdf->Rect($pdf->GetX(), $pdf->GetY(), $width, $height, 'all');
    }
   
    $pdf->SetFillColor(255, 255, 255);

    if($content['widget'] === 'editor'){
      $instance = new \simple_html_dom();
      $instance->load('<html><body>'. $content['value'] .'</body></html>');
      $this ->addEditorDocument($instance, $height);
    }
    elseif($content['widget'] === 'table') {
      $this->addTableWidget($content, $height, $width);
    }
    elseif($content['widget'] === 'image') {
      
      if (!isset($content['fid']) || empty($content['fid'])) {
        return ;
      }

      $file = file_load($content['fid']);
      $fn = \LK\VKU\Editor\Export\ImageCropper::process($file, $content);
      
      $pdf->Image($fn, $pdf->GetX(), $pdf->GetY(), $width, $height, '', '', "C");
    }
  }
}
