<?php

namespace LK\VKU\Editor\Export;

use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Fill;

/**
 * Description of ExportPPTProcessor
 *
 * @author Maikito
 */
class ExportPPTProcessor extends Interfaces\ExportProcessorInterface {
 
  protected $ppt = NULL;
  protected $slide = NULL;
  protected $text_sep = 5;


  /**
   * Initializing
   *
   * @param \LK\VKU\Editor\Document $document
   */
  function __construct(\LK\VKU\Editor\Document $document) {
    $this->document=$document;
    parent::__construct($document);
  }

  /**
   * Gets the PPT
   *
   * @return \LK\PPT\LK_PPT_Creator
   */
  function getPPT(){
    return $this->ppt;
  }

  /**
   * Gets the Slide
   *
   * @return \PhpOffice\PhpPresentation\Slide
   */
  function getSlide(){
    return $this->slide;
  }

  /**
   * Renders out the PPT
   *
   * @param \LK\PPT\LK_PPT_Creator $ppt
   */
  function renderPPT(\LK\PPT\LK_PPT_Creator $ppt){
    $this->ppt=$ppt;
    $this->slide = $ppt->createSlide();

    // Title
    $shape = $this->slide->createRichTextShape()->setHeight(100)->setWidth(800)->setOffsetX(60)->setOffsetY(120);
    $shape-> getActiveParagraph()->getAlignment()->setHorizontal( Alignment::HORIZONTAL_LEFT );
    $ppt->createTextRun($shape, $this->getDocument()->getPageTitle(), 23, TRUE);

    $defintion = $this->getLayoutDefinition();
    $content = $this->getDocument()->getContent();

    $left_y = 60;
    $top_x = 180;

    $text_sep = $this->text_sep;
    $height_100 = 400;
    $width_100 = 900 - (2 * $left_y) + $text_sep ;

    $top_run = $top_x;
    $left_run = $left_y;

    $fields = 0;
    $regions_processed = 0;

    while(list($key, $val) = each($defintion)){

      if($val['height'] === 'auto'){
        $height_region = ($height_100 * 100) / 100;
      }
      elseif($val['height'] === 'calc') {
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


        //$this->PDF_Position($pdf, $left_run, $top_run, $content_width, $content_height);
        $field = $content[$fields];
        $this->addContent($field, $left_run, $top_run, $content_width, $content_height);
        //$this->addPDFContent($pdf, $content_width - $text_sep, $content_height, $field);

        $top_run += $text_sep + $content_height;
        $fields++;
      }
      $top_run_region = $this->getSlide()->getOffsetY(); //$pdf ->GetY();
      $regions_processed++;
      $left_run += $width_region + ($text_sep / 3);
    }
    

    


    $ppt->slide_finalize($this->slide);
  }

  function addContentTable($content, $x, $y, $width, $height){

    $size = count($content['rows'][0]);

    $shape = $this->getSlide()->createTableShape($size);
    //$shape->setHeight($height);
    $shape->setWidth($width);
    $shape->setOffsetX($x);
    $shape->setOffsetY($y);

    foreach($content['rows'] as $row_content){
      $row = $shape->createRow();
      $row->getFill()->setFillType(Fill::FILL_NONE);
      //         ->setStartColor(new Color('FFE06B20'))
      //         ->setEndColor(new Color('FFFFFFFF'));
      
      foreach($row_content as $cell_content){
        $stripped = strip_tags($cell_content);
        $cell = $row->nextCell();
        $cell->createTextRun($stripped)->getFont()->setBold(false)->setSize(10);
      }
    }

    

  }

  function addContent($content, $x, $y, $width, $height){

    $ppt = $this->getPPT();

    $shape2 = $this->getSlide()->createRichTextShape();
    $shape2 ->setHeight($height)->setWidth($width)->setOffsetX($x)->setOffsetY($y);

    if($content['widget'] === 'table'){
      $this->addContentTable($content, $x, $y, $width, $height);
    }

    if($content['widget'] === 'image'){
      if (!isset($content['fid']) || empty($content['fid'])) {
        return ;
      }
     
      $file = file_load($content['fid']);
      $fn = \LK\VKU\Editor\Export\ImageCropper::process($file, $content);

      dpm(array("height" => $height, 'width' => $width, "offsetX", $x, "offsetY" => $y));

      $ppt -> addImage($fn, array("height" => $height, 'width' => $width, "offsetX" => $x, "offsetY" => $y));
     }
  }
}
