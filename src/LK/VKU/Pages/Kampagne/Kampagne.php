<?php

namespace LK\VKU\Pages\Kampagne;

/**
 * Description of Kampagne
 *
 * @author Maikito
 */
class Kampagne {

  protected $node;
  protected $pdf;

  /**
   * Constructor
   *
   * @param \stdClass $node
   */
  function __construct(\stdClass $node) {
    $this -> node = $node;
  }

  /**
   * Gets the Node
   *
   * @return \stdClass
   */
  protected function getNode(){
    return $this->node;
  }

  /**
   * Get the PDF Object
   *
   * @return \LK\PDF\LK_PDF
   */
  protected function getPDF(){
    return $this->pdf;
  }

  protected function setMediaTitle($title){
    $pdf = $this->getPDF();
    $pdf -> SetTopMargin(40);
    $pdf->SetFontClass('h2');
    $pdf->MultiCell(0, 10, $title, 0, 'L', 0);
  }


  /**
   * Renders the Node
   *
   * @param \LK\PDF\LK_PDF $pdf
   */
  function render(\LK\PDF\LK_PDF $pdf){
    $this -> pdf = $pdf;
    $node = $this->getNode();

    if($node -> vku_hide == false){
      $this->addTitlePage();
    }

    $hideonlinesize = $pdf->getUserSettings('hide_size_online', 'no');

    $pdf -> SetLeftMargin(115);
    $pdf -> SetRightMargin(25);
    $pdf->SetTextColor(69, 67, 71);

    $pdf -> SetLeftMargin(25);
    foreach($node -> medien as $medium){

      if($medium -> vku_hide == false && isset($medium ->field_medium_bild['und'][0]['uri'])):
         $this->addMediaDescription($medium);
      endif;

      // Varianten
      // Checken if there is any Variante to be displayed
      if($medium -> vku_hide_varianten){
        continue;
      }

      $pdf->AddPage();
      $this->setMediaTitle($medium -> title);

      // Varianten hinzufügen
      $pdf -> SetRightMargin(25);
      
      $tax = \taxonomy_term_load($medium->field_medium_typ['und'][0]['tid']);
      $abstand = 15;
      $normalabstand = 25;
      $x = $normalabstand;
      $zwischenabstand = 22;
      $width = 35;
      $calc = 0;

      if(isset($tax->field_medientyp_pdf_width['und'][0]['value'])){
        $width = $tax->field_medientyp_pdf_width['und'][0]['value'];
      }

      if($width < 50){
        $abstand = 40;
      }
      
      $filetype = $medium -> media_type;
      if($filetype != 'print'){
        $pdf->SetFont('', '', 14);
        $pdf->Text(233, 45, "Animiertes Banner");
        $pdf->SetFont('', '', 12);
      }

      // Go through the Media
      $pdf->SetFont('','',12);

      foreach($medium->field_medium_varianten["und"] as $variante){
        $test = $x + $width;
        $y = 65;

        if($test > 290 AND ($filetype === 'print')){
          $x = $normalabstand;
          $pdf->AddPage();
          $this->setMediaTitle($medium -> title);

          $y = 70;
          $pdf->SetFont('','',12);
        }
        elseif($test > 290 AND $filetype !== 'print') {
          $y += 60;
          $test2 = $y + $calc;

          if($test2 > 200){
            $x = $normalabstand;
            $y = 70;
            $pdf->AddPage();
            $this->setMediaTitle($medium -> title);
            $pdf->SetFont('', '', 12);
          }

          $x = $normalabstand;
        }

        if($filetype !== 'print'){

          $frames = $variante['gif'];
          if(!$frames){
            return ;
          }

          if($hideonlinesize == 'yes'){
            $online_title = trim($tax -> field_medientyp_online_label["und"][0]["value"]) . " (". ($variante["title"]).")";
          }
          elseif($hideonlinesize == 'no-label') {
            $online_title = $variante["title"];
          }
          else {
            $online_title = $tax -> name . " (". $variante["title"]. ")";
          }

          $pdf -> Text($x, $y - 5, ($online_title));
          $t = 1;

          $count_frames = count($frames);
          foreach($frames as $frame){
            $pdf->SetFont('', '', 10);
            $pdf -> SetFillColor(0,0,0);
            $pdf->Rect($x - 0.1 + 1, $y + 3, 14.7, 5.3, 'F');
            $pdf -> SetTextColor(255, 255, 255);
            $pdf -> Text($x + 1 , $y + 2.75, ("Frame " . ($t)));
            $pdf -> SetTextColor(69, 67, 71);
            $pdf->SetFont('','',12);
            $pdf->Image($frame, $x + 1, $y + 8, $width);

            $calculate_height =  getimagesize($frame);
            $image_height = $calculate_height[1];
            $image_width = $calculate_height[0];

            $module_dir = $pdf->getAssetDir();

            if($t != $count_frames){
              $pdf -> Image($module_dir . "/repeat_000000_64.png", $x + $width - 4, $y + 4 - 0.5, 4);
            }
            else {
              $pdf -> Image($module_dir ."/refresh_000000_64.png", $x + $width - 4, $y + 4 - 0.5, 4);
            }

            $quotient = $image_width / $width;
            $calc  = (int)($image_height / $quotient);
            $abstand2 = 3;
            $pdf->Rect($x + 1, $y +8, $width, $calc, 'D');
            $x+=$width + $abstand2;
            $t++;
          }

          for($i = $t; $i <= 3; $i++){
            $x+=$width + $abstand2;
          }

          $x += $zwischenabstand;
          continue;
        }

        if($variante["uri"]){
          $url = $this->getImageStyle($variante["uri"], 'medium');
        }

        $medium_desc = $tax -> name;

        if($tax -> description){
          $medium_desc = $tax -> description;
        }

        $postion_image = 60;

        $pdf -> Text($x, $postion_image, $medium_desc);
        $pdf->Image($url, $x + 1, $postion_image + 7, $width);

        $calculate_height =  getimagesize($url);
        $image_height = $calculate_height[1];
        $image_width = $calculate_height[0];
        $quotient = $image_width / $width;

        $calc  = (int)($image_height / $quotient);
        $pdf -> Text($x, $postion_image + $calc + 9, ($variante["title"]));
        $x+=$width + $abstand;
      }
    }
  }


  /**
   * Get Image-Style URL
   *
   * @param string $url
   * @param string $name
   * @return string
   */
  function getImageStyle($url, $name) {
    $url_style = image_style_url('pdf_' . $name, $url);
    return \LK\Files\FileGetter::get($url_style);
  }

  /**
   * Adds a Media Description
   *
   * @param \stdClass $medium
   */
  protected function addMediaDescription($medium){
    $pdf = $this->getPDF();
    $pdf->AddPage();

    if($medium ->field_medium_bild['und'][0]['uri']) {
      $bild = $this->getImageStyle($medium ->field_medium_bild['und'][0]['uri'], 'big');
      $pdf->Image($bild, 110, 30.25, 246.2);
    }

    $pdf -> SetRightMargin(130);
    
    $pdf->SetFontClass('h2');
    $pdf->MultiCell(0, 0, $medium -> title, 0, 'L', 0);
    $pdf -> Ln(10);
    $pdf -> SetRightMargin(185);
    $pdf->SetFont('','',15);
    $pdf->MultiCell(0, 7, $medium -> field_medium_beschreibung['und'][0]["value"] , 0, 'L', 0);
  }

  /**
   * Adds the Title-Page
   */
  protected function addTitlePage(){

    $node = $this->getNode();
    $pdf = $this->getPDF();
    $pdf->AddPage();

    $pdf -> SetLeftMargin(115);

    if($node ->field_kamp_teaserbild['und'][0]['uri']){
      $bild = $this ->getImageStyle($node ->field_kamp_teaserbild['und'][0]['uri'], 'medium');
      $pdf->Image($bild, 40, 50, 50);
    }

    $pdf -> Ln(10);
    $pdf ->SetFontClass('h1');
    $pdf->MultiCell(0, 16, ($node -> title), 0, 'L', 0);
    $pdf -> Ln(5);
    $pdf ->SetFontClass('h2');
    $pdf->MultiCell(0, 12, ($node -> field_kamp_untertitel['und'][0]['value']) , 0, 'L', 0);
    $pdf -> Ln(10);
    $pdf->SetFont('','',15);
    $pdf->MultiCell(0, 7, ($node -> field_kamp_teasertext['und'][0]['value']) , 0, 'L', 0);
    $pdf->SetFont('','',12);
    $pdf->SetTextColor(150, 150, 150);
    $pdf->Text(260, 175, $node -> sid);
    $pdf->SetTextColor(69, 67, 71);
  }
}
