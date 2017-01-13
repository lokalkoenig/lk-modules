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

use LK\PPT\Pages\RenderDefault;
use LK\PPT\Pages\RenderNode;


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
    
    /**
     * Constructur
     * Sets basic Settings
     * 
     * @param Integer $vku_id
     * @return \LK_PPT
     */
    function __construct($vku_id) {
        $this -> vku = new \VKUCreator($vku_id);
        $account = \LK\get_user($this->vku->getAuthor());
        
        // Get common VKU Settings
        $this -> settings = \LK\VKU\VKUManager::getVKU_RenderSettings($account);
        $this -> start_time = microtime();
        
        // https://github.com/PHPOffice/PHPPresentation/issues/266
        
        $this -> ppt = new PhpPresentation();
        
        //$this -> oMasterSlide =  $this -> ppt->getAllMasterSlides()[0];
        //$this -> oSlideLayout =  $this -> oMasterSlide->getAllSlideLayouts()[0];
        
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
    global $base_url;    
    
        $dir = file_directory_temp();
        
        $real = drupal_realpath($uri);
        $img_url = image_style_url($style, $uri);
        
       return \LK\Files\FileGetter::get($img_url);
    }
    
    /**
     * Adds all the necessary Slides based on the Configuration
     */
    function process(){
        $pages = $this -> vku ->getPages();
       
         while(list($id, $page) = each($pages)){
             // ppt_render_node_kampagne 
             $cn = "LK\PPT\Pages\Render" . ucfirst($page["data_module"]);
             $obj = new $cn($this);
             $obj -> render($page); // namespace LK\PPT;
         }
    }
    
    
    /**
     * 
     */
    function testNode($node){
        $cn = "LK_PPT_render_node";
        $obj = new LK_PPT_render_node($this);
        $obj -> render($node);
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
    
    function createSlide(){
        
        if($this -> slide_count === 0){
           //$this -> ppt->removeSlideByIndex(0);
           $slide = $this -> ppt ->getActiveSlide(); 
           //$slide->setSlideLayout($this -> oSlideLayout);
        }
        else {
            $slide = $this -> ppt ->createSlide();
            //$slide ->setSlideLayout($this -> oSlideLayout);
        }
        
        $this -> slide_count++;
        return $slide;
    }
    
    function getFont(){
        $font = ucfirst($this ->getSetting('font'));
        return $font;
    }
    
    function getFontBold(){
        return $this ->getFont() . 'Bold';
    }
    
    function getFontSemiBold(){
        return $this ->getFont() . ' Semi Bold';
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
        $color = new Color("FF" . strtoupper($input));
    
       return $color;   
    }
    
    
    
    /**
     * Adds Header and Footer to the slide
     * 
     * @param type $currentSlide
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
        
            $shape->setName('logo')
                ->setDescription('logo')
                ->setPath($image)
                ->setHeight(80)
                ->setOffsetX(60)
                ->setOffsetY(10);
            
                // when the Logo position is right side
                if($logo_pos == 'right'):
                    $size = getimagesize($image);
                    $calc = 80 / $size[1];
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
        
        
        //$shape->getShadow()->setVisible(true)->setAlpha(75)->setBlurRadius(2)->setDirection(45);
        
        $logos = $this ->getSetting('logos_unten');
        $logo_height = 40;
        
        if($logos):
            $offset_x = 60;
            $x = 0;
            $marken_shapes = array();
            foreach($logos as $logo){
                $size = getimagesize($logo);
                $height = $size[1];
                $width = $size[0];
                $calc =  $logo_height / $height;
                $calc_width = round($width * $calc, 0);
                $marken_shapes[$x] = $currentSlide->createDrawingShape();
                $marken_shapes[$x]->setName('logo2')->setPath($this -> getImageFile($logo, 'ppt_logos'))->setHeight($logo_height)->setWidth($calc_width)->setOffsetX($offset_x)->setOffsetY(650);
                $offset_x += $calc_width + 20;
                $x++;
            }
        endif;
    }
}