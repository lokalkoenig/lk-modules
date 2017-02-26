<?php

namespace LK\VKU\Editor\Export;

use LK\PDF\LK_PDF;

/**
 * Description of ExportProcessor
 *
 * @author Maikito
 */
class ExportProcessor {

  var $document = null;
  var $pdf = null;
  var $debug = FALSE;

  function __construct(\LK\VKU\Editor\Document $document) {
    $this->document = $document;

    if(isset($_GET['debug'])){
      $this->debug = TRUE;
    }
  }

  /**
   * Gets the Document
   *
   * @return \LK\VKU\Editor\Document
   */
  private function getDocument(){
    return $this->document;
  }

  /**
   * Gets the PDF Object
   *
   * @return LK_PDF
   */
  private function getPDF(){
    return $this->pdf;
  }


  /**
   * Gets the Layout Definition
   *
   * @return array
   */
  private function getLayoutDefinition(){
    $document = $this->getDocument();
    $layout = $document->getLayout();
    $manager = new \LK\VKU\Editor\Manager();
    $obj = $manager->getLayout($layout);

    return $obj -> getDefinition();
  }

  function addPageTitle(LK_PDF $pdf){
    $pdf->AddPage();
    $pdf->addHeadline($this->getDocument()->getPageTitle());
  }

  private function addFootnote(LK_PDF $pdf, $footnote){

    $pdf->SetFillColor(255, 255, 255);
    $pdf -> Rect(0,175.5, 297, 30, 'F');

    $pdf->setX(25);
    $pdf->setY(178.5);
    $pdf ->SetLeftMargin(25);
    $pdf ->SetRightMargin(25);
    $pdf ->SetFont(VKU_FONT, '', 8);
    $pdf->MultiCell(0, 10, $footnote, '', 'R');
  }


  public function processPDF(LK_PDF $pdf){
    $this -> pdf = $pdf;
    
    $document = $this ->getDocument();
    $defintion = $this->getLayoutDefinition();
    $content = $document->getContent();
    $this->addPageTitle($pdf);
  
    $left_y = 25;
    $top_x = 55;

    $text_sep = 5;

    $height_100 = 115;
    $width_100 = 297 - (2 * $left_y) + $text_sep;

    $top_run = $top_x;
    $left_run = $left_y;

    $fields = 0;
    $regions_processed = 0;

    while(list($key, $val) = each($defintion)){
      $width_region = ($width_100 * $val['width']) / 100;
      $height_region = ($height_100 * $val['height']) / 100;

      $top_run = $top_x;

      foreach($val['fields'] as $region){
        $content_height = ($region['height'] * $height_region) / 100;
        $content_width = ($region['width'] * $width_region) / 100;

        if($region['height'] === 100){
          $content_height += $text_sep;
        }

        $this->PDF_Position($pdf, $left_run, $top_run, $content_width, $content_height);
        $field = $content[$fields];

        $this->addPDFContent($pdf, $content_width - $text_sep, $content_height, $field);

        $top_run += $text_sep + $content_height;
        $fields++;
      }

      $regions_processed++;
      $left_run += $width_region + ($text_sep / 2);
    }
    
    $this->addFootnote($pdf, $document ->getFootnote());
  }

  private function PDF_Position(LK_PDF $pdf, $x, $y, $width, $height){
    $pdf -> SetXY($x, $y);
    
    $width_100 = 297 - 50;
    $content_spacing = 5;

    // calc margins
    $margin_right = 25;
    $margin_left = 25;

    if($x === 25){
      $margin_right += (float)$width_100 - (float)($width) + (float)($content_spacing / 2);
    }  
    else {
      $margin_left += (float)($width) + round($content_spacing / 2, 1);
    }

    $pdf -> SetLeftMargin($margin_left);
    $pdf -> SetRightMargin($margin_right);
    $pdf ->SetFont(VKU_FONT, '', 10.5);
    
    if($this -> debug){
      $pdf ->SetFont(VKU_FONT, '', 8);
      $pdf -> Text($x, $y - 4,  'X: ' . $x . '; Y: ' . $y . ", Height: " . $height . "; Width: " . $width . " / MR ". $margin_right . '/ML ' . $margin_left);
      $pdf ->SetFont(VKU_FONT, '', 10.5);
      $pdf -> SetXY($x, $y);
    }
    //kpr($width);
    //exit;
    //$pdf->Text('0', 'X: ' . $x . '; Y: ' . $y . ", Height: " . $height . "; Width: " . $width . " / MR ". $margin_right . '/ML ' . $margin_left);
    //$pdf -> SetXY($x, $y);
    
    //$pdf -> Ln(5);
  }

  /**
   * Adds an Table-Widget to the PDF
   *
   */
  function addTableWidget($value, $height, $width){

    $pdf = $this->getPDF();

    $pdf ->SetFontClass('h2');
    $pdf->Write(5, $value['title']);
    $pdf -> ln(10);
    $pdf ->SetX($pdf->getX());

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

      if($pdf ->GetY() > $maxY){
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
        $pdf->WriteOwnHTML($child -> innertext);
        $pdf->Ln(10);
      }

      if($child -> tag === 'ol' OR $child -> tag === 'ul'){
        $ol = 1;
        $left_margin = $pdf ->getMarginLeft();
        $x_pos = $pdf->GetX();

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
          $pdf ->WriteOwnHTML(html_entity_decode($li-> innertext));
          $pdf->Ln(4);
          $pdf->SetLeftMargin($left_margin);
         
          $ol++;
        }
        $pdf->Ln(2);
        $pdf->SetLeftMargin($left_margin);
      }


      if($child -> tag === 'h1'){
        $pdf->SetFont('DejaVu', '', 22);
        $pdf->WriteOwnHTML(html_entity_decode($child -> innertext));
        $pdf->Ln(6);
      }

      if($child -> tag === 'p'){
        $pdf ->WriteOwnHTML(html_entity_decode($child-> innertext));
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
  private function addPDFContent($pdf, $width, $height, $content){

    $pdf ->SetFont('', '', 10);

    if($this -> debug){
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
      // little fix for now
      $size = getimagesize($content['url']);
      $image_width = $size[0];
      $image_height = $size[1];
      $ratio2 = $height / $width;
      $ratio_img = $image_height / $image_width;

      $diff = $ratio_img - $ratio2;
      $real_width = round($width * $diff);

      if($real_width < 0.0 || ($diff * 100) <= 2){
        $real_width = $width;
      }
      else {
        $real_width_diff = ($width - $real_width) / 2;
        $pdf -> setX($pdf -> getX() + $real_width_diff + 1);
      }

      
      $pdf->Image($content['url'], NULL, NULL, $real_width - 1, $height);
    }
  }
}
