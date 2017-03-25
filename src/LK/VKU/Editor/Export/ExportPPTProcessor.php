<?php

namespace LK\VKU\Editor\Export;

use PhpOffice\PhpPresentation\Shape\RichText\Paragraph;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Fill;
use PhpOffice\PhpPresentation\Shape\RichText;
use PhpOffice\PhpPresentation\Style\Border;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Style\Bullet;
use LK\PDF\PDF_Loader;
use LK\VKU\Editor\Export\ExportPDFProcessor;

/**
 * Description of ExportPPTProcessor
 *
 * @author Maikito
 */
class ExportPPTProcessor extends Interfaces\ExportProcessorInterface {
 
  protected $ppt = NULL;
  protected $slide = NULL;
  protected $text_sep = 25;
  protected $table_font_size = 8;
  protected $h1_font_size = 23;
  protected $h2_font_size = 17;
  protected $text_font_size = 9.5;
  protected $pdf_multiplicator = 2.6;

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

  function getPDFAutoHeight(){
    $pdf = PDF_Loader::getPDF($this->getPPT()->getVku()->getAuthor());
    $pdf_process = new ExportPDFProcessor($this->getDocument());
    $pdf_process -> processPDF($pdf);

    return $pdf_process->getCalculatedRegionHeight();
  }

  /**
   * Renders out the PPT
   *
   * @param \LK\PPT\LK_PPT_Creator $ppt
   */
  function renderPPT(\LK\PPT\LK_PPT_Creator $ppt){
    $this->ppt = $ppt;
    $this->slide = $ppt->createSlide();
   
    // Take the PDF for calculations in consideration

    // Title
    $shape = $this->slide->createRichTextShape()->setHeight(100)->setWidth(800)->setOffsetX(60)->setOffsetY(120);
    $shape->setInsetLeft(0);
    $shape->setInsetRight(0);
    $this->addH1($shape, $this->getDocument()->getPageTitle());

    $defintion = $this->getLayoutDefinition();
    $content = $this->getDocument()->getContent();

    $left_y = 60;
    $top_x = $top_run_region = 180;

    $text_sep = $this->text_sep;
    $height_100 = 380;
    $width_100 = 960 - (2 * $left_y);

    $top_run = $top_x;
    $left_run = $left_y;

    $fields = 0;
    $regions_processed = 0;

    while(list($key, $val) = each($defintion)){

      if($val['height'] === 'auto'){
        $height_region = ($height_100 * 100) / 100;
      }
      // only the case in the Preiskalulation!
      elseif($val['height'] === 'calc') {
        $pdf_calc = $this->getPDFAutoHeight();
        $top_run_region = $top_x + $text_sep + ($pdf_calc * $this->pdf_multiplicator);
        $height_region = $height_100;
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
            $left_run = 60;
          }

          if($region['left'] === 33){
            $left_run = (($width_100 + 20) * 0.33) + 60;
          }

          if($region['left'] === 66){
            $left_run = (($width_100 + 20) * 0.66) + 60;;
          }

          if($region['left'] === 50){
            $left_run = (($width_100 + 20) * 0.5) + 60;;
          }
        }

        if(isset($region['top'])){
          if($region['top'] === 0){
            $top_run = $top_x;
          }
        }

        $content_width = ($region['width'] * $width_region) / 100;

        if(isset($content[$fields])) {
          $field = $content[$fields];
          $content_width_calc = $content_width;
          
          if($content_width_calc !== $width_100) {
            $content_width_calc -= $text_sep;
          }

          $this->addContent($field, $left_run, $top_run, $content_width_calc, $content_height);
        }
        
        $top_run += $content_height + ($text_sep / 2);
        $fields++;
      }
      
      $top_run_region = $this->getSlide()->getOffsetY(); //$pdf ->GetY();
      $regions_processed++;
      $left_run += $width_region + ($text_sep / 3);
    }

    $footer = trim($this->getDocument()->getFootnote());

    if($footer) :
      $footer_shape = $this->slide->createRichTextShape()->setHeight(20)->setWidth(825)->setOffsetX(60)->setOffsetY(590);
      $footer_shape->setInsetLeft(0);
      $footer_shape->setInsetRight(0);
      $footer_shape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
      $footer_shape->getActiveParagraph()->getFont()->setSize(8)->setColor(new Color('FF777777'));
      $footer_shape->createTextRun($footer);
    endif;

    $ppt->slide_finalize($this->getSlide());
  }

  function addH1(RichText $shape, $text){
    $shape->getActiveParagraph()->getFont()->setSize($this->h1_font_size)->setBold(true);
    $this->SimpleMarkupParser($shape->getActiveParagraph(), $text, $this->h1_font_size);
    
    $paragrah = $shape->createParagraph();
    $paragrah->getFont()->setSize(5);
    $paragrah->createTextRun(' ');
  }

  /**
   * Adds a H2 Headline
   *
   * @param RichText $shape
   * @param string $text
   */
  function addH2(RichText $shape, $text){
    $shape->getActiveParagraph()->getFont()->setSize($this->h2_font_size)->setBold(FALSE);
    $this->SimpleMarkupParser($shape->getActiveParagraph(), $text, $this->h2_font_size);

    $paragrah = $shape->createParagraph();
    $paragrah->getFont()->setSize(5);
    $paragrah->createTextRun(' ');
  }

  /**
   * Adds Table Content
   *
   * @param array $content
   * @param int $x
   * @param int $y
   * @param int $width
   * @param int $height
   */
  function addContentTable($content, $x, $y, $width, $height){

    $size = count($content['rows'][0]);
    $y_parsed = $y;

    // If title
    if($content['title']):
      $headline = $this->getSlide()->createRichTextShape()->setOffsetY($y)->setOffsetX($x);
      $headline->setWidth($width);
      $headline->setInsetTop(0);
      $headline->setInsetLeft(0);
      $headline->setInsetRight(0);

      $this->addH2($headline, $content['title']);
      $y_parsed = $headline->getOffsetY();
      $y_parsed += 30;
      
    endif;

    $shape = $this->getSlide()->createTableShape($size);
    $shape->getBorder()->setLineStyle(Border::LINE_NONE);
    $shape->setWidth($width);
    $shape->setOffsetX($x);
    $shape->setOffsetY($y_parsed);
    
    $x_row = 0;
    foreach($content['rows'] as $row_content){

      $row = $shape->createRow();
      
      if($x_row % 2){
        $row->getFill()->setFillType(Fill::FILL_GRADIENT_LINEAR)
               ->setStartColor(new Color('FFEEEEEE'))
               ->setEndColor(new Color('FFEEEEEE'));
      }
      else {
        $row->getFill()->setFillType(Fill::FILL_GRADIENT_LINEAR)
               ->setStartColor(new Color('FFFFFFFF'))
               ->setEndColor(new Color('FFFFFFFF'));

      }
      $row->setHeight(10);

      foreach($row_content as $cell_content){
        $cell = $row->nextCell();
        $cell->getBorders()->getBottom()->setLineWidth(0)->setLineStyle(Border::LINE_NONE);
        $cell->getBorders()->getLeft()->setLineWidth(0)->setLineStyle(Border::LINE_NONE);
        $cell->getBorders()->getTop()->setLineWidth(0)->setLineStyle(Border::LINE_NONE);
        $cell->getBorders()->getRight()->setLineWidth(0)->setLineStyle(Border::LINE_NONE);
        $cell->setColSpan(0);
        $cell->setRowSpan(0);
        
        $paragraph = $cell->getActiveParagraph();
        $paragraph->getAlignment()->setMarginLeft(0)->setIndent();
        $paragraph->getFont()->setSize($this->table_font_size);
        $cell_content_sanitized = $this->getTableMarkupSpripped($cell_content);

        if($cell_content_sanitized) {
          $this->SimpleMarkupParser($paragraph, $this->removeTrailingBR(html_entity_decode($cell_content_sanitized)), $this->table_font_size, false);
        }
        else {
          $paragraph->createTextRun(' ');
        }
      }

      $x_row++;
    }
  }

  /**
   * Simple Markup Parser for PPT
   *
   * @param Paragraph $paragraph
   * @param type $markup
   */
  private function SimpleMarkupParser(Paragraph $paragraph, $markup, $font_size = 8, $add_spacing = true){

    $body = $this->loadMarkup(trim($markup));
    
    $paragraph->getFont()->setSize($font_size);
    $paragraph->getFont()->setColor($this->getPPT()->getTextColor());

    if(count($body -> children()) === 0){
      if($markup) {
        $tr = $paragraph->createTextRun($markup);
        $tr->getFont()->setSize($font_size);
      }
      return ;
    }

    $child_count = count($body -> children());
    $x = 0;
    foreach($body -> nodes as $child){

      if($child->tag === "text"){
        $paragraph->getFont()->setSize($font_size);
        $paragraph->createTextRun($child ->innertext);
      }

      // if empty
      if($child->tag === "br"){
        $paragraph->createTextRun(' ');
        //$paragraph->getFont()->setSize(5);
        $paragraph->createBreak();
        //dpm('BR: ' . $markup);
        //$paragraph->getFont()->setSize($font_size);
        continue;
      }

      if($child->tag === 'b' || $child->tag=="strong"){
        $paragraph->getFont()->setBold(TRUE);

        if($child->nodes){
          $this->SimpleMarkupParser($paragraph, html_entity_decode($child-> innertext), $font_size);
        }

        $paragraph->getFont()->setBold(FALSE);
      }

      if($child->tag === 'em' || $child->tag=="i"){
        $paragraph->getFont()->setItalic(TRUE);

        if($child->nodes){
          $this->SimpleMarkupParser($paragraph, html_entity_decode($child-> innertext), $font_size);
        }

        $paragraph->getFont()->setItalic(FALSE);
      }

      $x++;
    }
  }

  /**
   * Adds the Editor-Content
   *
   * @param type $content
   * @param type $x
   * @param type $y
   * @param type $width
   * @param type $height
   */
  private function addEditorContent($content, $x, $y, $width, $height){

    if(!isset($content['value']) || empty($content['value'])) {

      return ;
    }

    $body = $this->loadMarkup($content['value']);

    $section = $this->getSlide()->createRichTextShape()->setOffsetY($y)->setOffsetX($x);
    $section->setInsetLeft(2);
    $section->setInsetTop(0);
    $section->setInsetRight(2);
    $section->setWidth($width);
    $section->setParagraphs([]);

    $x = 0;
    foreach($body -> nodes as $child){
      
      if($child->tag === 'h2'){
        $section->createParagraph();
        $this->addH2($section, $this->removeTrailingBR(html_entity_decode($child-> innertext)));
      }

      if($child->tag === 'h1'){
        $section->createParagraph();
        $this->addH1($section, $this->removeTrailingBR(html_entity_decode($child-> innertext)));
      }

      if($child->tag === 'ul' || $child->tag === 'ol'){
        $section->createParagraph();
        $list = $section->getActiveParagraph();

        $list->getAlignment()->setMarginLeft(15)->setIndent(-15);
        $list->getFont()->setSize($this->text_font_size);
        $list->getBulletStyle()->setBulletType(Bullet::TYPE_BULLET);

        if($child->tag === 'ol') {
          $list->getBulletStyle()->setBulletType(Bullet::TYPE_NUMERIC);
        }

        $e = 0;
        foreach($child -> children() as $li):
          $text = $this->removeTrailingBR(html_entity_decode($li-> innertext));

          if($e === 0){
            $this->SimpleMarkupParser($section->getActiveParagraph(), $text, $this->text_font_size);
          }
          else {
            $this->SimpleMarkupParser($section->createParagraph(), $text, $this->text_font_size);
          }

          $e++;
        endforeach;

        $paragrah2 = $section->createParagraph();
        $section->getActiveParagraph()->getAlignment()->setMarginLeft(0)->setIndent(0);
        $section->getActiveParagraph()->getBulletStyle()->setBulletType(Bullet::TYPE_NONE);
        $paragrah2->getFont()->setSize($this->text_font_size / 2);
        $paragrah2->createTextRun(' ');
      }

      if($child->tag === 'p'){
        $shape = $section->createParagraph();
        $this->SimpleMarkupParser($shape, $this->removeTrailingBR(html_entity_decode($child-> innertext)), $this->text_font_size);
        
        $paragrah2 = $section->createParagraph();
        $paragrah2->getFont()->setSize($this->text_font_size / 2);
        $paragrah2->createTextRun(' ');
      }
    }
  }
  
  /**
   * Adds Content
   *
   * @param type $content
   * @param type $x
   * @param type $y
   * @param type $width
   * @param type $height
   */
  function addContent($content, $x, $y, $width, $height){

    $ppt = $this->getPPT();

    if($content['widget'] === 'table'){
      $this->addContentTable($content, $x, $y, $width, $height);
    }

    if($content['widget'] === 'editor'){
      $this->addEditorContent($content, $x, $y, $width, $height);
    }

    if($content['widget'] === 'image'){
      if (!isset($content['fid']) || empty($content['fid'])) {
        return ;
      }
     
      $file = file_load($content['fid']);
      $fn = \LK\VKU\Editor\Export\ImageCropper::process($file, $content);
      $ppt -> addImage($fn, array("height" => $height, 'width' => $width, "offsetX" => $x, "offsetY" => $y));
     }
  }
}
