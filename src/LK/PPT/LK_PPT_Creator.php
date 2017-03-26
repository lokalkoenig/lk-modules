<?php
namespace LK\PPT;

use PhpOffice\PhpPowerpoint\Autoloader;
use PhpOffice\PhpPresentation\Settings;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\Slide;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\AbstractShape;
use PhpOffice\PhpPresentation\DocumentLayout;
use PhpOffice\PhpPresentation\Shape\Drawing;
use PhpOffice\PhpPresentation\Shape\MemoryDrawing;
use PhpOffice\PhpPresentation\Shape\RichText;
use PhpOffice\PhpPresentation\Shape\RichText\BreakElement;
use PhpOffice\PhpPresentation\Shape\RichText\TextElement;
use PhpOffice\PhpPresentation\Style\Bullet;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Fill;


/**
 * Default Margin Left / Right
 */
define('LK_PPT_MARGIN', 60);


class LK_PPT_Creator {
    
    /**
     * @var VKUCreator $vku 
     */
    var  $vku;
    
    /**
     * Slide count
     * @var Int 
     */
    var $slide_count = 0;
    
    /**
     * Temporary Images
     * @var Array 
     */
    private $tmp_images = array();
    
    /**
     * VKU Settings
     * @var Array 
     */
    private $settings = array();
    
    /** 
     *
     * @var PhpPresentation @ppt
     */
    var $ppt;
    
    
    /**
     * Starttime
     *
     * @var Int 
     */
    var $start_time;
    
    var $oMasterSlide = null;
    var $oSlideLayout = null;
    
    
    function setVKU(\VKUCreator $vku){
      $this -> vku = $vku;
      $account = \LK\get_user($this->vku->getAuthor());
      $this->setAccount($account);
    }

    /**
     * Sets the User
     *
     * @param \LK\User $account
     */
    function setAccount(\LK\User $account) {
      $this -> settings = \LK\VKU\VKUManager::getVKU_RenderSettings($account);
    }

    /**
     * Creates a Rich-Text Shape
     *
     * @return \PhpOffice\PhpPresentation\Shape\RichText
     */
    function createRichTextShape(){

      $slide = $this->ppt->getActiveSlide();
      $shape = $slide->createRichTextShape();
      $shape->getActiveParagraph()->getFont()->setColor($this->getTextColor());
      $shape->setInsetLeft(0);
      $shape->setInsetRight(0);
   
      return $shape;
    }


    /**
     * Constructur
     * Sets basic Settings
     * 
     * @param Integer $vku_id
     * @return \LK_PPT
     */
    function __construct() {
        $this -> start_time = microtime();
        $this -> ppt = new PhpPresentation();
        return $this;
    }
    
    
    public function getVku(){
        return $this -> vku;
    }
    
    public function getAuthor(){
        return $this -> vku ->getAuthor();
    }
    
    /**
     * Return as VKU setting
     * 
     * @param String $id
     * @return Mixed
     */
    function getSetting($id){
        
      if(isset($this -> settings[$id])){
        return $this -> settings[$id];
      }
        
      return false;
    }
    
    /**
     * Gets Back the Slide count
     * 
     * @return Int
     */
    function getPageCount(){
      return $this -> ppt -> getSlideCount();
    }
    
    /**
     * Gets an Image File
     * 
     * @global type $base_url
     * @param type $uri
     * @param type $style
     * @return type
     */
    function getImageFile($uri, $style){
      $img_url = image_style_url($style, $uri);

      return \LK\Files\FileGetter::get($img_url);
    }
   
    /**
     * Creates a Text
     * 
     * @param type $textrun
     * @param Bolean $bold
     * @param Integer $size
     */
    function text($textrun, $bold, $size){
      $color = $this ->getTextColor();
      $font = $textrun->getFont()->setName($this ->getFont())->setColor($color);
      $font -> setBold($bold);

      if($bold){
          $font -> setName($this -> getFontSemiBold());
      }

      $font -> setSize($size);
    }
    
    /**
     * Adds an Image
     *
     * @param string $image
     * @param array $options
     * @return Drawing
     */
    function addImage($image, $options){

      $slide = $this -> ppt ->getActiveSlide();

      $shape_image = new Drawing();
      $shape_image->setName('logo')->setPath($image);

      if(isset($options["height"])){
        $shape_image -> setHeight($options["height"]);
      }

       if(isset($options["width"])){
        $shape_image -> setWidth($options["width"]);
       }

       if(isset($options["offsetX"])){
        $shape_image -> setOffsetX($options["offsetX"]);
       }

       if(isset($options["offsetY"])){
        $shape_image -> setOffsetY($options["offsetY"]);
       }

       $slide->addShape($shape_image);

       return $shape_image;
    }

    /**
     * Creates a Slide
     *
     * @return \PhpOffice\PhpPresentation\Slide
     */
    function createSlide(){
      
      if($this -> slide_count === 0){
        $slide = $this -> ppt ->getActiveSlide();
      }
      else {
        $slide = $this -> ppt ->createSlide();
        $this->ppt->setActiveSlideIndex($this->ppt->getActiveSlideIndex() + 1);
      }

      $this -> slide_count++;
      
      return $slide;
    }
    
    function getFont(){
      return 'Calibri';
    }
    
    function getFontBold(){
      return $this ->getFont();
    }
    
    function getFontSemiBold(){
      return $this ->getFont();
    }
    
    function getTextColor(){
        $color =  new Color();
        $color -> setRGB('454347');
        
    return $color;    
    }
    
    
    /**
     * Writes the PPTX or ODP Document
     * 
     * @param String $filename
     * @return String
     */
    public function write($dir, $filename){
      $xmlWriter = IOFactory::createWriter($this -> ppt, 'PowerPoint2007');
      ob_clean();
      $xmlWriter->save($dir . '/' . $filename . ".pptx");
      $this -> clear();
      return  $filename . ".pptx";      
    }
    
    /**
     * Clears all the temporary files
     */
    private function clear(){
      $dir = file_directory_temp();
      
      foreach($this -> tmp_images as $img){
          unlink($dir .'/' .  $img);
      }
      
      
      
    }
    
    /**
     * Return an Color Object from HEX Value
     * 
     * @param String $input
     * @return Color
     */
    public function getColorFromHex($input){
      
      $color_convert = strtoupper(str_replace('#', '', $input));
      $color = new Color("FF" . $color_convert);
    
      return $color;   
    }

    /**
     * Adds a Text-Run
     *
     * @param type RichText $shape
     * @param string $text
     * @param int $size
     * @param boolean $bold
     * @return \PhpOffice\PhpPresentation\Shape\RichText\Run
     */
    function createTextRun(RichText $shape, $text, $size = false, $bold = false, $color = FALSE){

      $textrun = $shape->getActiveParagraph()->createTextRun($text);
      $textrun-> getFont()->setColor($this ->getTextColor());

      if($size){
        $textrun->getFont()->setSize($size);
      }

      if($bold){
        $textrun->getFont()->setBold($bold);
      }

      if($color && is_string($color)){
        $takecolor = $this ->getColorFromHex($color);
        $textrun->getFont()->setColor($takecolor);
      }

      return $textrun;
    }
    
    /**
     * Adds Header and Footer to the slide
     * 
     * @param \PhpOffice\PhpPresentation\Slide $currentSlide
     */
    public function slide_finalize($currentSlide){
        
      // Add Background-Color
      $color = $this -> getColorFromHex($this ->getSetting('vku_hintergrundfarbe'));
      $color_line = $this -> getColorFromHex($this ->getSetting('title_bg_color'));

      // Immitate a Border-top
      $shape_footer_top = $currentSlide->createRichTextShape()->setHeight(101)->setWidth(960);
      $shape_footer_top->setOffsetX(0);
      $shape_footer_top->setOffsetY(0);
      $shape_footer_top->getFill()->setFillType(Fill::FILL_SOLID)->setRotation(90)->setStartColor($color_line);

      $logo_pos = $this ->getSetting('logo_position');
      $shape2 = $currentSlide->createRichTextShape()->setHeight(100)->setWidth(960);
      $shape2->setOffsetX(0);
      $shape2->getFill()->setFillType(Fill::FILL_SOLID)->setRotation(90)->setStartColor($color)->setEndColor($color);
      $header_logo = $this ->getSetting('logo_oben');

      if($header_logo):
        $shape = $currentSlide->createDrawingShape();
        $image = $this -> getImageFile($header_logo, "ppt_logos");
        $shape->setName('logo')->setDescription('logo')->setPath($image)->setHeight(60)->setOffsetX(60)->setOffsetY(20);
     
        // when the Logo position is right side
        if($logo_pos === 'right'):
          $size = getimagesize($image);
          $calc = 60 / $size[1];
          $width = $size[0] * $calc;
          $shape -> setOffsetX(960 - $width - LK_PPT_MARGIN);
        endif;
      endif;

      // Immitate a Border-top
      $shape_footer2 = $currentSlide->createRichTextShape()->setHeight(1)->setWidth(960);
      $shape_footer2->setOffsetX(0);
      $shape_footer2->setOffsetY(620);
      $shape_footer2->getFill()->setFillType(Fill::FILL_SOLID)->setRotation(90)->setStartColor($color_line);

      $shape_footer = $currentSlide->createRichTextShape()->setHeight(102)->setWidth(960);
      $shape_footer->setOffsetX(0);
      $shape_footer->setOffsetY(621);
      $shape_footer->getFill()->setFillType(Fill::FILL_SOLID)->setRotation(90)->setStartColor($color)->setEndColor($color);
        
      $logos = $this ->getSetting('logos_unten');
      $logo_height = 40;
        
      if($logos):
        $offset_x = 60;
        $x = 0;
        $marken_shapes = array();

        foreach($logos as $logo){

          $logo_img = \LK\Files\FileGetter::get(image_style_url('pxedit_footer_logo', $logo));

          $size = getimagesize($logo_img);
          $height = $size[1];
          $width = $size[0];
          $calc =  $logo_height / $height;
          $calc_width = round($width * $calc, 0);
          $marken_shapes[$x] = $currentSlide->createDrawingShape();
          $marken_shapes[$x]->setName('logo2')->setPath($logo_img)->setHeight($logo_height)->setWidth($calc_width)->setOffsetX($offset_x)->setOffsetY(650);
          $offset_x += $calc_width + 20;
          $x++;
        }

      endif;
    }
}