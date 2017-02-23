<?php

namespace LK\VKU\Editor\Export;

/**
 * Description of ExportProcessor
 *
 * @author Maikito
 */
class ExportProcessor {

  var $document = null;
  var $pdf = null;

  function __construct(\LK\VKU\Editor\Document $document) {
    $this->document = $document;
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
   * @return \PDF
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

  function addPageTitle($pdf){
    $pdf->AddPage();
    $pdf -> SetTopMargin(30);
    $pdf -> SetLeftMargin(25);
    $pdf -> SetRightMargin(25);
    $pdf -> Ln(15);
    $pdf->SetFont(VKU_FONT,'',22);
    $pdf->MultiCell(0, 0, $this->getDocument()->getPageTitle(), 0, 'L', 0);
  }

  function addFootnote(\PDF $pdf, $footnote){

    //$pdf ->SetTextColor($r);
    $pdf->setX(0);
    $pdf -> ln(1);
    $pdf->setY(175.5);
    $pdf ->SetLeftMargin(25);
    $pdf ->SetRightMargin(25);
    $pdf ->SetFont(VKU_FONT, '', 8);
    $pdf->Cell(125, 10, $footnote, 0,0, 'R');
  }


  public function processPDF(\PDF $pdf){
    $this -> pdf = $pdf;
    
    $document = $this ->getDocument();
    $defintion = $this->getLayoutDefinition();
    $content = $document->getContent();
    $this->addPageTitle($pdf);
  
    $left_y = 25;
    $top_x = 55;

    $text_sep = 5;

    $height_100 = 115;
    $width_100 = 297 - (2 * $left_y);

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
      $left_run+=$width_region + $text_sep;
    }
    
    $this->addFootnote($pdf, $document ->getFootnote());
  }


  private function PDF_Position(\PDF $pdf, $x, $y, $width, $height){
    $pdf -> SetXY($x, $y);
    $width_100 = 290 - 25;
    $content_spacing = 15;

    // calc margins
    $margin_right = 25;
    $margin_left = 25;

    if($x === 25){
      $margin_right = $width_100 - ($width) + $content_spacing;
    }
    else {
      $margin_left += ($width);
    }

    $pdf -> SetLeftMargin($margin_left);
    $pdf -> SetRightMargin($margin_right);
    $pdf ->SetFont(VKU_FONT, '', 10.5);

    //kpr($width);
    //exit;
    //$pdf->WriteHTML('X: ' . $x . '; Y: ' . $y . ", Height: " . $height . "; Width: " . $width . " / MR ". $margin_right . '/ML ' . $margin_left);
    //$pdf -> Ln(5);
  }

  private function addEditorDocument(\simple_html_dom $dom){

    $pdf = $this->getPDF();
    $pdf->SetFont('DejaVu', '', 10);

    $body = $dom -> find('body');
    $x = 0;
    foreach($body[0] -> children() as $child){

       if(empty($child -> plaintext)){
          continue;
       }

       if($x > 0){
          $pdf->Ln(2);
       }

      if($child -> tag === 'h2'){
        $pdf->SetFont('DejaVu', '', 18);
        $pdf->WriteHTML($child -> innertext);
        $pdf->Ln(6);
      }

      if($child -> tag === 'ol' OR $child -> tag === 'ul'){
        $ol = 1;
        $left_margin = $pdf ->left;
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
          $pdf ->WriteHTML($li-> innertext);
          $pdf->Ln(4.5);
          $pdf->SetLeftMargin($left_margin);
        
          $ol++;
        }

        $pdf->SetLeftMargin($left_margin);
        $pdf->ln(2);
      }


      if($child -> tag === 'h1'){
        $pdf->SetFont('DejaVu', '', 22);
        $pdf->WriteHTML($child -> innertext);
        $pdf->Ln(6);
      }

      if($child -> tag === 'p'){
        $pdf ->WriteHTML($child-> innertext);
        $pdf->ln(4);
      }

      $pdf->SetFont('DejaVu', '', 10);
      $x++;
    }
 }


  private function addPDFContent(\PDF $pdf, $width, $height, $content){
    $pdf ->SetFont(VKU_FONT, '', 10);

    if($content['widget'] === 'editor'){
      $instance = new \simple_html_dom();
      $instance->load('<html><body>'. 'AAA' . $content['value'] .'</body></html>');
      $this ->addEditorDocument($instance);
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
   
      $pdf ->SetX($pdf -> getX() + 1);
      $pdf->Image($content['url'], NULL, NULL, $real_width - 1, $height);
    }
  }
}
