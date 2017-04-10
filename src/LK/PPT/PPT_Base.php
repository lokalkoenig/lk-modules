<?php

namespace LK\PPT;

use PhpOffice\PhpPresentation\Style\Fill;

/**
 * Description of PPT_Base
 *
 * @author Maikito
 */
abstract class PPT_Base {
    
      
    /**
     * @var LK_PPT PPT-Base-Class
     */
    var $reference;
    
    /**
     * @var \PhpOffice\PhpPresentation\Slide Current Slide
     */
    var $current_slide;
    
    function __construct(LK_PPT_Creator $reference) {
        $this -> reference = $reference; 
    }

    /**
     *
     * @return \LK\PPT\LK_PPT_Creator
     */
    function getPPT() {
      return $this->reference;
    }
    
    function getFont(){
      return 'Calibri';
    }   

    /**
     *
     * @return \VKUCreator
     */
    function getVku(){
        return $this -> reference ->getVku();
    }
    
    function getSetting($key){
        return $this -> reference ->getSetting($key);
    }
    
    function drawLine($x, $y, $width, $height = 1, $color = null){
        
      $bg_color = $this ->getTextColor();
      if(!$color){
        $bg_color = $this ->getColorFromHex($color);
      }

      $currentSlide = $this -> getCurrentSlide();
      $shape = $currentSlide->createRichTextShape()->setHeight($height)->setWidth($width);
      $shape->setOffsetX($x);
      $shape->setOffsetY($y);
      $shape->getFill()->setFillType(Fill::FILL_SOLID)->setStartColor($bg_color);
    
    return $shape;
    }
    
    /**
     * Creates a Slide
     * 
     * @return \PhpOffice\PhpPresentation\Slide
     */
    function createSlide(){

      $this -> current_slide = $this->getPPT()->createSlide();

      return $this -> current_slide;
    }
    
    
    function getTextColor(){
        return $this -> reference -> getTextColor();
    }    
    
    function getColorFromHex($hex){
        return $this -> reference -> getColorFromHex($hex);
    }
    
    function getAuthor(){
        return $this -> reference -> getAuthor();
    }
    
    function getImageFile($uri, $style){
        return $this -> reference ->getImageFile($uri, $style);
    }    
    
    /**
     * Returns current Slide
     * 
     * @return \PhpOffice\PhpPresentation\Slide
     */
    function getCurrentSlide(){
      return $this -> current_slide;
    }
    
    function addImage($image, $options){
      return $this->addImage($image, $options);
    }   
    
    function finalize(){
        $this -> reference ->slide_finalize($this -> current_slide);
    }
    
    function textRun($shape, $text, $size = FALSE, $bold = FALSE, $color = FALSE){
      return $this->getPPT()->createTextRun($shape, $text, $size, $bold, $color);
    }
    
}