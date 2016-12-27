<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\PPT\Pages;

use LK\PPT\PPT_Base;
use LK\PPT\LK_PPT_Creator;

use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Fill;
use PhpOffice\PhpPresentation\Style\Border;


/**
 * Description of Nodes
 *
 * @author Maikito
 */
class RenderNode extends PPT_Base {
 
    var $node;
    
    var $online_max_height = 800;
    var $print_max_width = 900;
    
    var $hide_online_label = 'no';
    
    function getNode(){
      return $this -> node;  
    }   
    
    function setNode($node){
        $this -> node = $node;
    }    
    
    function __construct(LK_PPT_Creator $reference) {
        parent::__construct($reference);
    }
    
    
    function render($page = array()){
         
         if(is_array($page)){
            $node = node_load($page["data_entity_id"]);
            _vku_load_vku_settings_node($node, $page);
         }
         
         if(is_object($page)){
             $node = $page;
            _vku_load_vku_settings_node($node, array());
         }
        
        $this -> hide_online_label = $this -> reference -> getSetting('hide_size_online');
         
        //Load Node Settings
        
        $this -> setNode($node);
        
        if($node -> vku_hide == false){
           $this ->addGeneralDescription($node);
        }
    
        foreach($node -> medien as $medium){
        
            if($medium -> vku_hide == false):
                $this ->addMedumDescription($medium);
            endif;
        
            if($medium -> vku_hide_varianten){
                continue;
            }
        
            $filetype = _lk_get_medientyp_print_or_online($medium->field_medium_typ['und'][0]['tid']);
        
            if($filetype === 'print'){
                $this ->addMediumPrint($medium);
            }
            else {
                $this -> addMediumOnline($medium);
            }
        }
    }    
    
    
    function addMediumPrintHeader($medium){
        $slide2 = $this ->getCurrentSlide();
        
        $shape = $slide2->createRichTextShape()->setHeight(40)->setWidth(500)->setOffsetX(60)->setOffsetY(130);
        $this ->textRun($shape, $medium -> title, 30, false);
    }
    
    
    function addMediumPrint($medium){
        
        $slide2 = $this ->createSlide();
        $this -> addMediumPrintHeader($medium);
      
        $tax = $medium->field_medium_typ['und'][0]['tid'];
        $term = taxonomy_term_load($tax);
        $name = $term -> description;

        $width = 190;
   
        if(isset($term->field_medientyp_pdf_width['und'][0]['value'])){
           $width = $term->field_medientyp_pdf_width['und'][0]['value'] * 3.4;
        }
        
        $spacing = 40;
        
        if($width < 190){
           $spacing = $spacing + (190 - $width); 
        } 
        
        
        $offset_x = 60;
        $i = 0;
        $i_count = count($medium->field_medium_varianten["und"]);
        
        foreach($medium->field_medium_varianten["und"] as $variante){
            //if($variante["uri"])
           $url = $variante["uri"];
           $teaser_img_url = $this -> getImageFile($url, 'varianten'); 
           $size = getimagesize($teaser_img_url);
           
           $image_height = $size[1];
           $image_width = $size[0]; 
           $quotient = $image_width / $width;
           $calc  = (int)($image_height / $quotient); 

           $shape = $slide2->createRichTextShape()->setHeight(40)->setWidth(250)->setOffsetX($offset_x)->setOffsetY(200);
           $this ->textRun($shape, $name, 12);

           $shape = $slide2->createDrawingShape();
           $shape->setName('logo')->setPath($teaser_img_url)->setWidth($width)->setHeight($calc)->setOffsetX($offset_x + 10)->setOffsetY(230);
           $y = $shape -> getOffsetY();

           $shape = $slide2->createRichTextShape()->setHeight(40)->setWidth(250)->setOffsetX($offset_x)->setOffsetY(230 + $calc);
           $this ->textRun($shape, $variante["title"]);
               
           $offset_x += $width + $spacing;
           
           $i++;
           
           if(($offset_x + $width) > $this -> print_max_width AND $i != $i_count){
               $offset_x = 60;
               $this ->finalize();
               $slide2 = $this ->createSlide();
               $this -> addMediumPrintHeader($medium);
      
           }
           
        }
   
   
        $this ->finalize();
    }
    
    /**
     * Adds the General Description
     * 
     * @param type $node
     */
    function addGeneralDescription($node){
    
        $slide = $this ->createSlide();
        $title = $node -> title;
    
        $subtitle = trim($node->field_kamp_untertitel['und'][0]['value']);
        $teaser_text = trim($node->field_kamp_teasertext['und'][0]['value']);
        $teaser_img = $node->field_kamp_teaserbild['und'][0]['uri'];
        $teaser_img_url = $this -> getImageFile($teaser_img, 'medium');
    
        // Image
        $shape = $slide->createDrawingShape();
        $shape->setName('logo')->setPath($teaser_img_url)->setWidth(200)->setOffsetX(150)->setOffsetY(140);
    
        $color = $this ->getTextColor();
    
        // Teaser  + Other
        $shape = $slide->createRichTextShape()->setHeight(40)->setWidth(500)->setOffsetX(400)->setOffsetY(130);
        $textRun = $shape->createTextRun($title);
        $textRun->getFont()->setName($this ->getFont())->setBold(true)->setSize(30)->setColor($color);
        
        $shape -> createBreak();
        $shape -> createBreak();
        
        $this ->textRun($shape, $subtitle, 26);
        
        $shape -> createBreak();
        $shape -> createBreak();
        
        // Add Kampa-Number
        
        
        
        $shape_no = $slide->createRichTextShape()->setHeight(40)->setWidth(290)->setOffsetX(600)->setOffsetY(570);
        $this->textRun($shape_no, "A". _lk_get_kampa_sid($this -> node), 12, false, '969696');
        $shape_no->getActiveParagraph()->getAlignment()->setHorizontal( Alignment::HORIZONTAL_RIGHT );
        $this ->textRun($shape, trim($teaser_text), 16);
        
        $this ->finalize();
    }
    
    
    /**
     * Adds and Medium Description
     * 
     * @param Object $medium
     */
    function addMedumDescription($medium){
    
        if(!isset($medium->field_medium_bild['und'][0]['uri'])){
            return ;
        }
    
        $slide2 = $this ->createSlide();
        $url = $medium->field_medium_bild['und'][0]['uri'];
        $title = $medium -> title;
        $teaser_img_url = $this -> getImageFile($url, 'ppt_crop_kampagnen_image');
        $teaser_text = trim($medium->field_medium_beschreibung['und'][0]['value']);
         
        $shape = $slide2->createDrawingShape();
        $shape->setName('logo')->setPath($teaser_img_url)->setHeight(530)->setOffsetX(350)->setOffsetY(100);
        
        $shape = $slide2->createRichTextShape()->setHeight(40)->setWidth(500)->setOffsetX(60)->setOffsetY(180);
        $this ->textRun($shape, $title, 30, false);
    
        $shape2 = $slide2->createRichTextShape()->setWidth(300)->setOffsetX(60)->setOffsetY(250);
        $this ->textRun($shape2, $teaser_text, 14);
        
        $this -> finalize();
    } 
    
    
    
    function addOnlineMediumHeader($medium){
      
      $slide2 = $this ->getCurrentSlide(); 
        
      $shape = $slide2->createRichTextShape()->setHeight(40)->setWidth(500)->setOffsetX(60)->setOffsetY(130);
      $this ->textRun($shape, $medium -> title, 30, false);
      
      $shape = $slide2->createRichTextShape()->setHeight(40)->setWidth(500)->setOffsetX(400)->setOffsetY(140);
      $tr = $this ->textRun($shape, 'Animiertes Banner', 16, false);
      $tr -> getFont();
      
      $shape->getActiveParagraph()->getAlignment()->setHorizontal( Alignment::HORIZONTAL_RIGHT );
    }
    
    
    function addMediumOnline($medium){
        $slide2 = $this ->createSlide();
        $this ->addOnlineMediumHeader($medium);
        
        $tax = $medium->field_medium_typ['und'][0]['tid'];
        $term = taxonomy_term_load($tax);
        $name = $term -> field_medientyp_online_label["und"][0]["value"];
     
        $offset_x = $offset_x_base = 60;
        $offset_y = $offset_y_base = 250;
        $color_black = new Color("FF000000");
      
        $oFill = new Fill();
        $oFill->setFillType(Fill::FILL_SOLID)->setStartColor($color_black);
  
        $hoehe = $term->field_medientyp_hoehe['und'][0]['value'];
        $breite = $term->field_medientyp_breite['und'][0]['value'];
        
        $destinated_breite = 120;
        
        if($breite > $destinated_breite){
            $calc =  $destinated_breite / $breite;
            $hoehe = $hoehe * $calc;
            $breite = $destinated_breite;
        }

        $v = 0;
        foreach($medium->field_medium_varianten["und"] as $variante){
      
            // Gif get
            $frames = lk_process_gif_ani($variante);
            $count = count($frames);
      
            if($count == 0){
                continue;
            }
            $slideshape = array();
            $textshape = array();
            $x = 0;
            
            if($this -> hide_online_label == 'yes'):
                $variante_title = $variante["title"];
            else:
                $variante_title = $name . " (". $variante["title"] .")";
            endif;
            
            $desc = $slide2->createRichTextShape()->setHeight(20)->setWidth(300)->setOffsetX($offset_x)->setOffsetY($offset_y - 60);
            $this ->textRun($desc, $variante_title, 12);
            
            
            $offset_y = $offset_y - 10;
            
            foreach($frames as $frame){
                $textshape[$x . 'a'] = $slide2->createRichTextShape()->setHeight(20)->setWidth(80)->setOffsetX($offset_x + 9)->setOffsetY($offset_y - 20);
                $textshape[$x . 'a'] -> setFill($oFill);
        
                $image = "sites/all/modules/lokalkoenig/vku/pages/repeat_000000_64.png";
        
                // Last Frame
                if($count == ($x + 1)){
                    $image = "sites/all/modules/lokalkoenig/vku/pages/refresh_000000_64.png";
                }
                    
                
                $slideshape[$x] = $slide2->createDrawingShape();
                $slideshape[$x]->setName('logo_' . $x )->setPath($image)->setWidth(18)->setHeight(18)->setOffsetX($offset_x + 10 + $breite - 18)->setOffsetY($offset_y - 20);
        
                $textshape[$x] = $slide2->createRichTextShape()->setHeight(20)->setWidth(90)->setOffsetX($offset_x + 10)->setOffsetY($offset_y - 30);
                $textshape[$x] -> getActiveParagraph()->getAlignment()->
                        setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_BASE);
                $this ->textRun($textshape[$x], 'Frame ' . ($x + 1), 10, false, "FFFFFF");
                
                $textshape[$x] -> setInsetTop(12);
                
                $slideshape[$x] = $slide2->createDrawingShape();
                $slideshape[$x]->setName('logo_' . $x )->setPath($frame)->setWidth($breite)->setHeight($hoehe)->setOffsetX($offset_x + 10)->setOffsetY($offset_y);
                $slideshape[$x]->getBorder()->setColor($color_black)->setLineStyle(Border::LINE_SINGLE)->setDashStyle(Border::DASH_SOLID);
        
                $slideshape[$x . 'a'] = $slide2->createRichTextShape()->setWidth($breite)->setHeight($hoehe)->setOffsetX($offset_x + 10)->setOffsetY($offset_y);
                $slideshape[$x . 'a']->getBorder()->setColor($color_black)->setLineStyle(Border::LINE_SINGLE)->setDashStyle(Border::DASH_SOLID);
                $offset_x += $breite + 10;
                $x++;
            }
    
            $offset_x += 50;
      
            if($v == 1){
              // after the Second item 
              $offset_y += $hoehe + 100;
              $offset_x = $offset_x_base;

              if($offset_y + $hoehe > $this -> online_max_height){
                  $offset_y = $offset_y_base;
                  $this -> finalize();
                  $slide2 = $this ->createSlide();
                  $this ->addOnlineMediumHeader($medium);
              }    
            }
      
      $v++;
  }
  
  $this -> finalize();    
}
    
    
}