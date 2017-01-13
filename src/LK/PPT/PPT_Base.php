<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\PPT;

use PhpOffice\PhpPresentation\Shape\Drawing\File;
use PhpOffice\PhpPresentation\Style\Fill;
use PhpOffice\PhpPresentation\Shape\Drawing;


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
    
    function getFont(){
        $this -> reference ->getFont();
    }   
    
    function getVku(){
        return $this -> reference ->getVku();
    }
    
    function getFontBold(){
        $this -> reference ->getFontBold();
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
       $this -> current_slide = $this -> reference ->createSlide(); 
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
       //$shape_image = $this -> current_slide -> createDrawingShape();
       $shape_image = new Drawing();
       $shape_image->setName('logo')->setPath($image);
       //$shape_image->setMimeType(Drawing\Gd::MIMETYPE_DEFAULT);
       
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
       
       $this -> current_slide->addShape($shape_image);
       return $shape_image;
    }   
    
    function finalize(){
        $this -> reference ->slide_finalize($this -> current_slide);
    }
    
    function textRun(&$shape, $text, $size = false, $bold = false, $color = false){
        $textrun = $shape->createTextRun($text);
        $textrun-> getFont()->setName($this->getFont())->setColor($this ->getTextColor());
        
        if($size){
            $textrun->getFont()->setSize($size);
        } 
        
        if($bold){
            $textrun->getFont()->setBold($bold);
        } 
        
        if($color AND is_string($color)){
            $takecolor = $this ->getColorFromHex($color);
            $textrun->getFont()->setColor($takecolor);
        }
        
        return $textrun;     
    }         
    
}