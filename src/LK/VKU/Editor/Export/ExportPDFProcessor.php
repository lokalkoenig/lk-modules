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
  var $offset_y_save = 0;
  var $text_size = 9;

  function __construct(Document $document) {
    parent::__construct($document);

    if(isset($_GET['debug'])){
      $this->debug = TRUE;
    }
  }

  function getHeight() {
    return 115;
  }

  function getTopMargin() {
    return 55;
  }

  function getTextSeperator() {
    return 5;
  }

  function getSavedOffsetY() {
    return $this->offset_y_save;
  }

  function getOffsetY() {
    $this->offset_y_save = $this->getPDF()->GetY() - $this->getTopMargin();

    return $this->offset_y_save;
  }

  function getWidth() {
    return 297;
  }

  /**
   * Get the Margin
   *
   * @return int
   */
  function getMargins() {
    return 25;
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
    $pdf ->SetFont('', '', 7.5);
    $pdf->setColor('text', 119, 119, 119);
    $pdf->Cell(0, 10, $footnote, 0, 0, 'R');
  }

  /**
   * Processed the PDF
   *
   * @param LK_PDF $pdf
   */
  public function processPDF(LK_PDF $pdf){
    $this -> pdf = $pdf;
    
    $document = $this ->getDocument();
    $this->addPageTitle($document->getPageTitle());
    $this->processContent();
    $this->addFootnote($document ->getFootnote());
  }

  function addContent($field, $left_run, $top_run, $content_width_calc, $content_height, $base_width) {
    $pdf = $this->getPDF();
    $this->PDF_Position($pdf, $left_run, $top_run, $content_width_calc, $content_height, $base_width);
    $this->addPDFContent($pdf, $content_width_calc, $content_height, $field);
  }


  private function PDF_Position(LK_PDF $pdf, $x, $y, $width, $height, $base_width){

    $pdf -> SetXY($x, $y);

    // calc margins
    $margin_right = 25;
    $margin_left = 25;

    if($x === 25){
      // exclude 100 % layouts
      if($width != 247) {
        $margin_right = $this->getWidth() - $x - $width;
      }
      else {
        $margin_left = 25;
      }
    }
    else {
      $margin_left = $x;
      $margin_right_test = $x + $width;
      $margin_right = $this->getWidth() - $margin_right_test;
    }

    $pdf -> SetLeftMargin($margin_left);
    $pdf -> SetRightMargin($margin_right - 0);
    $pdf ->SetFont(VKU_FONT, '', 10.5);
    
    if($this -> debug && $height !== 115){
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

    if(count($value['rows'][0]) === 5){
      $cellwidth = [20, 20, 20, 20, 20];
    }

    $pdf->SetFont('','', 9);
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
        $table .= '<td width="'. $cellwidth[$y] .'%" bgcolor="'. $bg_color . '">'. $this->getTableMarkupSpripped($cell) . '</td>';
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

    $pdf->SetFont('DejaVu', '', $this->text_size);
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
          $pdf->WriteOwnHTML($this->removeTrailingBR(html_entity_decode($li-> innertext)));
          
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

      $pdf->SetFont('DejaVu', '', $this->text_size);
      $x++;
    }
  }

  protected function getEditorMarkup($html) {
    $html = str_replace('<p><br></p>', '<p> </p>', $html);

    return $html;
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
      
      $instance->load('<html><body>'. $this->getEditorMarkup($content['value']) .'</body></html>');
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
