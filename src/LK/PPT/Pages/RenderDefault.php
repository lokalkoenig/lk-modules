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
use PhpOffice\PhpPresentation\Style\Bullet;

/**
 * Renders default VKU-Elements to 
 * a PPTX
 * 
 * This class contains 5 Pages
 * 
 */
class RenderDefault extends PPT_Base {
    
    /**
     * Cellwidth of the Callendar element
     * 
     * @var Int 
     */
    var $table_cell_width = 24;
    
    
    /**
     * Cellheight of the Callendar element
     * 
     * @var Int 
     */
    var $table_cell_height = 14;
       
    
    /**
     * Constructor
     * 
     * @param LK_PPT $reference
     */
    function __construct(LK_PPT_Creator $reference) {
        parent::__construct($reference);
    }
    
    
    /**
     * Renders out the Pages
     * 
     * @param Array $page
     */
    function render($page){
        
        switch($page["data_class"]){
          case 'kontakt':
              $this -> ppt_render_default_kontakt();
              break;
          
          case 'title';
              $this ->ppt_render_default_title();
              break;
          
          case 'tageszeitung':
              $this ->ppt_render_default_tageszeitung();
              break;
          
          case 'wochen';
              $this ->ppt_render_default_wochen();
              break;
          
          case 'onlinewerbung':
              $this ->ppt_render_default_onlinewerbung();
              break;
          
          case 'kplanung':
              $this ->ppt_render_default_kplanung();
              break;
        }    
    }    
    
    /**
     * Renders default kontakt Page
     */
    function ppt_render_default_kontakt(){
    
      $this ->createSlide();
      $author = $this -> getAuthor();
      $account = user_load($author);
      $prof = profile2_load_by_user($account);

      /** Definitions */
      $layout = $this ->getSetting('contact_layout');
      
      $vorname = $prof['main']->field_profile_vorname['und'][0]['value'];
      $name = $prof['main']->field_profile_name['und'][0]['value'];

      $telefon = $prof['main']->field_profile_telefon['und'][0]['value'];
      $mobil = @$prof['main']->field_profile_telefon_mobil['und'][0]['value'];
      $email = $account -> mail;
      $org = $prof['main']->field_profile_adresse['und'][0]['organisation_name'];
      $title = $prof['main']->field_profile_title['und'][0]['value'];

      $address = $prof['main']->field_profile_adresse['und'][0]['thoroughfare'];
      $zip = $prof['main']->field_profile_adresse['und'][0]['postal_code'];
      $town = $prof['main']->field_profile_adresse['und'][0]['locality'];

      $bild = false; 
      if(isset($prof['main']->field_profile_bild['und'][0]['uri'])){
          $bild = $this ->getImageFile($prof['main']->field_profile_bild['und'][0]['uri'], 'image-framed');
      }

      // Add Telefone-Symbol in the Background
      $bg_image = 'sites/all/modules/lokalkoenig/vku/pages/telefon.jpg';
      $this ->getPPT()->addImage($bg_image, array("height" => 400, "offsetX" => 500, "offsetY" => 280));
      
      if($layout === 'default'){
        // General headline
        $shape_title = $this->getPPT()->createRichTextShape();
        $shape_title->setHeight(100)->setWidth(800)->setOffsetX(60)->setOffsetY(140);
        $this->getPPT()->createTextRun($shape_title, "Ich bin gerne für Sie da!" , 45);
      }
    
      $array_data = array();
      $array_data[] = array('text' => $vorname . " " . $name, 'size' => 24, 'bold' => false);
      $array_data[] = array('text' => $title, 'size' => 20, 'bold' => false);

      $array_data[] = array();
      $array_data[] = array('text' => 'Telefon: ' . $telefon, 'size' => 16, 'bold' => false); 

      if($mobil){
        $array_data[] = array('text' => 'Mobil: ' . $mobil,'size' => 16, 'bold' => false); 
      }

      $array_data[] = array('text' => 'E-Mail: ' . $email, 'size' => 16, 'bold' => false); 
      $array_data[] = array();
      $array_data[] = array('text' => $org,'size' => 16, 'bold' => false); 
      $array_data[] = array('text' => $address, 'size' => 16, 'bold' => false); 
      $array_data[] = array('text' => $zip . " " . $town, 'size' => 16, 'bold' => false); 
      $array_data[] = array('text' => false);

      // Add Profile Image
      if($layout == 'default'){
        $this->getPPT()->addImage($bild, array("height" => 300, "offsetX" => 60, "offsetY" => 260));
        $shape = $this->getPPT()->createRichTextShape();
        $shape->setHeight(300)->setWidth(500)->setOffsetX(310)->setOffsetY(260);
      }
      else {
        $this->getPPT()->addImage($bild, array("height" => 200, "offsetX" => 60, "offsetY" => 140));
        $shape = $this->getPPT()->createRichTextShape();
        $shape->setHeight(300)->setWidth(500)->setOffsetX(60)->setOffsetY(150 + 190);
      }

      foreach($array_data as $item){
        if(!$item){
          $this->getPPT()->createTextRun($shape, ' ');
          $shape->createBreak();

          continue;
        }

        if(!$item["text"]){
          continue;
        }

        $this->getPPT()->createTextRun($shape,  $item["text"], $item["size"], $item["bold"]);
        $shape->createBreak();
      }
        
      $this -> finalize();
    }  
    
    /**
     * Renders default title Page
     */
    function ppt_render_default_title(){
    
    
        $slide = $this -> createSlide();
        $vku = $this ->getVku();
    
        $title = $vku -> get("vku_title", true);
        $sub_title = $vku -> get("vku_untertitel", true);
        $company = $vku -> get("vku_company", true);

        $title_bg_color = $this ->getColorFromHex($this -> getSetting('title_bg_color'));
        $shape_bg = $slide->createRichTextShape()->setHeight(300)->setWidth(960)->setOffsetX(0)->setOffsetY(100);
        $shape_bg -> getFill()->setFillType(Fill::FILL_SOLID)->setRotation(90)->setStartColor($title_bg_color)->setEndColor($title_bg_color);

        $shape_title = $slide->createRichTextShape()->setHeight(300)->setWidth(960)->setOffsetX(0)->setOffsetY(100);
        $shape_title -> getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
 
        // Defining Title Font size    
        $title_size = 50;
        if(strlen($title) > 25){  $title_size = 45; }
        if(strlen($title) > 45){ $title_size = 35; }
        
        $this -> textRun($shape_title, $title, $title_size, false, $this -> getSetting('title_vg_color'));
   
        // TITLE END
        $shape2 = $this->getPPT()->createRichTextShape();
        $shape2 ->setHeight(300)->setWidth(960)->setOffsetX(0)->setOffsetY(430);
        $shape2 -> getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
      
        // Snd
        if($company){
           $this -> textRun($shape2, $company, 28, true); 
           $shape2->createBreak();
        }

        if($sub_title){
            $this -> textRun($shape2, $sub_title, 25, true); 
            $shape2->createBreak();
        }
    
        $shape2->createBreak();
        $this -> textRun($shape2, date("d.m.Y"), 20, false, new Color('FF646464')); 
        $this -> finalize($slide);
    }
    
    
    /**
     * Renders default wochen Page
     */
    function ppt_render_default_wochen(){
    
      $this ->createSlide();
      $this->getPPT()->addImage('sites/all/modules/lokalkoenig/vku/pages/shutterstock_43524835_small.jpg', array("height" => 445, "offsetX", 0, "offsetY" => 180));

      $shape = $this->getPPT()->createRichTextShape();
      $shape = $shape->setHeight(100)->setWidth(600)->setOffsetX(400)->setOffsetY(120 + 40);
      $this -> textRun($shape, 'Was spricht für die Werbung in ...', 23, true);

      $shape2 = $this->getPPT()->createRichTextShape()->setHeight(100)->setWidth(370)->setOffsetX(540)->setOffsetY(180 + 40);
      $this ->textRun($shape2, 'Wochen-/Anzeigenblättern', 20, false);

      $shape3 = $this->getPPT()->createRichTextShape()->setHeight(600)->setWidth(350)->setOffsetX(560)->setOffsetY(220 + 50);
      $shape3->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)
                                                     ->setMarginLeft(25)
                                                     ->setIndent(-25);

      $shape3->getActiveParagraph()->getFont()->setSize(16);
      $shape3->getActiveParagraph()->getBulletStyle()->setBulletType(Bullet::TYPE_BULLET);
      $shape3->createTextRun('kostenlos für jeden Leser');
      $shape3->createParagraph()->createTextRun('sehr hohe Reichweite');
      $shape3->createParagraph()->createTextRun('Verteilung auch an Werbeverweigerer (Haushalte mit dem Aufkleber "Bitte keine Werbung einwerfen")');
      $shape3->createParagraph()->createTextRun('generell sehr hohe Akzeptanz bei den Lesern');
      $shape3->createParagraph()->createTextRun('oft das entscheidende Medium für den geplanten Einkauf');
      $shape3->createParagraph()->createTextRun('PR-Artikel buchbar');

      $this ->finalize();
    }    
    
    
    
      /**
     * Renders default onlinewerbung Page
     */
    function ppt_render_default_onlinewerbung(){

      $this ->createSlide();
      $this->getPPT()->addImage('sites/all/modules/lokalkoenig/vku/ppt/pages/online.jpg', ['height' => 525, 'offsetX' => 585, "offsetY" => 100]);

      $shape = $this->getPPT()->createRichTextShape();
      $shape->setHeight(100)->setWidth(500)->setOffsetX(LK_PPT_MARGIN)->setOffsetY(120);
      $this ->textRun($shape, 'Was spricht für die ...', 23, true);

      $shape = $this->getPPT()->createRichTextShape();
      $shape->setHeight(100)->setWidth(500)->setOffsetX(LK_PPT_MARGIN + 15)->setOffsetY(180);
      $this ->textRun($shape, 'Online Werbung (Display-Ads)', 20, false);

      $shape = $this->getPPT()->createRichTextShape();
      $shape->setHeight(600)->setWidth(490)->setOffsetX(LK_PPT_MARGIN)->setOffsetY(220);
      $shape->getActiveParagraph()->getAlignment()->setMarginLeft(15)->setIndent(-15);
      $shape->getActiveParagraph()->getFont()->setSize(16);
      $shape->getActiveParagraph()->getBulletStyle()->setBulletType(Bullet::TYPE_BULLET);
      
      $shape->createTextRun('geeignet zum Image-Aufbau, zur Adressgenerierung und für den Verkauf von Produkten oder Dienstleistungen');
      $shape->createParagraph()->createTextRun('kann sofortige Handlungsimpulse erzeugen');
      $shape->createParagraph()->createTextRun('thematische Aussteuerung der Werbung, bei Buchung spezieller Rubriken, Inhalte, (Sonder-) Themen');
      $shape->createParagraph()->createTextRun('PR-Text bzw. thematische Micro-Sites buchbar');
      $shape->createParagraph()->createTextRun('Werbeerfolgskontrolle: Gute Messbarkeit der Werbeeinblendungen, Klick-Raten o.ä.');
      $shape->createParagraph()->createTextRun('Werbeinhalt kann problemlos geändert werden');
      $shape->createParagraph()->createTextRun('aktive Kundenansprache: zahlreiche Möglichkeiten der Interaktivität');
      
      $this -> finalize();
    }   
    
    
    /**
     * Renders default tageszeitung Page
     */
    function ppt_render_default_tageszeitung(){
    
        $this ->createSlide();
        $this->getPPT()->addImage('sites/all/modules/lokalkoenig/vku/pages/shutterstock_64607632_small.jpg', ['height' => 525, 'offsetX' => 599, "offsetY" => 100]);

        $shape = $this->getPPT()->createRichTextShape();
        $shape->setHeight(100)->setWidth(500)->setOffsetX(LK_PPT_MARGIN)->setOffsetY(120);
        
        $textRun = $shape->createTextRun('Was spricht für die Werbung in...');
        $textRun->getFont()->setSize(23)->setBold(TRUE);

        $shape = $this->getPPT()->createRichTextShape();
        $shape->setHeight(100)->setWidth(500)->setOffsetX(LK_PPT_MARGIN + 15)->setOffsetY(180);
        $this ->textRun($shape, 'Tageszeitungen', 20, false);

        $shape = $this->getPPT()->createRichTextShape();
        $shape->setHeight(600)->setWidth(510)->setOffsetX(LK_PPT_MARGIN)->setOffsetY(220);
        $shape->getActiveParagraph()->getAlignment()->setMarginLeft(15)->setIndent(-15);
        $shape->getActiveParagraph()->getFont()->setSize(16);
        $shape->getActiveParagraph()->getBulletStyle()->setBulletType(Bullet::TYPE_BULLET);
        
        $shape->createTextRun('hohe Glaubwürdigkeit (höchste Glaubwürdigkeit aller Medien)');
        $shape->createParagraph()->createTextRun('sehr hohe Leser-Blatt-Bindung (die meisten Leser sind Abonnenten seit etlichen Jahren)');
        $shape->createParagraph()->createTextRun('gut geeignete Werbeimpulse (kurzfristig buchbar)');
        $shape->createParagraph()->createTextRun('sehr gute Reichweite');
        $shape->createParagraph()->createTextRun('Leserschaft gehört meistens zu den eher Wohlhabenden');
        $shape->createParagraph()->createTextRun('PR-Text buchbar');
        $shape->createParagraph()->createTextRun('geographische, zielgerichtete Ausstreuung der Werbung durch Buchung einzelner Ausgaben');
        $shape->createParagraph()->createTextRun('thematische Aussteuerung, bei Buchung spezieller Rubriken, Inhalte, (Sonder-)Themen, Beilagen o.ä.');

        $this -> finalize();
    }
 
    /**
     * Renders default kplanung Page
     */
     function ppt_render_default_kplanung(){
        
        $currentSlide = $this ->createSlide();
        
        $dates = array();
        $years = array();

        $dates[] = array(date("m"), date("Y"));

        for($x = 1; $x < 6; $x++){
          $dates[] = array(
            date("m", strtotime("+". $x ." month")), 
            date("Y", strtotime("+". $x ." month"))
          ); 
        }
        
     
        foreach($dates as $date){
         $years[$date[1]] = $date[1];
        }
    
        $color = $this ->getTextColor();

        $shape = $currentSlide->createRichTextShape()->setHeight(100)->setWidth(500)->setOffsetX(60)->setOffsetY(120);
        $shape->getActiveParagraph()->getAlignment()->setHorizontal( Alignment::HORIZONTAL_LEFT );
        $textRun = $shape->createTextRun('Kampagnenplanung ' . implode(" / ", $years));
        $shape->setInsetLeft(0);
        $shape->setInsetRight(0);
        $textRun->getFont()->setName($this->getFont())->setBold(true)->setSize(23)->setColor($color);
        
        $dateComponents = getdate();

        $curr = date('m');

        $monate = array(
        0 => 'Januar',
        1 => 'Februar',
        2 => 'März',
        3 => 'April',
        4 => 'Mai',
        5 => 'Juni',
        6 => 'Juli',
        7 => 'August',
        8 => 'September',
        9 => 'Oktober',
        10 => 'November',
        11 => 'Dezember');
  
        $y_offset = 180;
        $x_offset = 60;
        $x = 0;
        foreach($dates as $date){
            
            if($x == 3){
                $y_offset = 180;
                $x_offset = 490;
            }
            
            $int_month = $date[0] - 1;
            $title = $monate[$int_month] . " " . $date[1];
            
            $this -> addMonatsKallender($date[0], $date[1], $title, $y_offset + 30, $x_offset);
            $y_offset += 140;
            $x++;
            
               
       }
   
        $this ->finalize();
    }
    
    
    
    /**
     * Sets a Cel for K-Planung
     * 
     * @param String $content
     * @param Color $bg_color
     * @param Boolean $bold
     * @param Int $y
     * @param Int $x
     */
    function setCellBgColor($content, $bg_color, $bold, $y, $x){
        $currentSlide = $this ->getCurrentSlide();  
        
        $shape = $currentSlide->createRichTextShape()->
                setHeight($this -> table_cell_height)->setWidth($this -> table_cell_width)->setOffsetX($x)->setOffsetY($y);
        $shape->getActiveParagraph()->getAlignment()->setHorizontal( Alignment::HORIZONTAL_CENTER )->setVertical(Alignment::VERTICAL_CENTER);
        $shape -> getFill()->setFillType(Fill::FILL_SOLID)
                               ->setStartColor(new Color("FF" .strtoupper($bg_color)))
                                ->setEndColor(new Color("FF" . strtoupper($bg_color)));
        //$shape -> getAlignment()->setVertical(Alignment::VERTICAL_BASE);
        $shape -> setInsetLeft(1);
        $shape -> setInsetRight(1);
        
        $textRun = $shape->createTextRun($content);
        $textRun->getFont()->setName($this->getFont())->setBold($bold)->setSize(6.5);
    }
    
    
    /**
     * Adds a full callendar
     * 
     * @param Int $month
     * @param Int $year
     * @param String $title
     * @param Int $y_pos
     * @param Int $x_pos
     */
    function addMonatsKallender($month, $year,$title, $y_pos, $x_pos){
         
         
        $zeitstempel = strtotime($year . "-" . $month . "-01 01:00:00"); 
        $monat = date("F", $zeitstempel);            //aktuellen Monat ermitteln 
        $tag_der_woche = date("N", $zeitstempel); //für die generierung von Leerzellen zu Beginn eines Monats 
        
        $currentSlide = $this ->getCurrentSlide();
        
        $shape = $currentSlide->createRichTextShape()->setHeight(20)->setWidth(200)->setOffsetX($x_pos)->setOffsetY($y_pos - 30);
        $shape->setInsetLeft(0);
        $shape->setInsetRight(0);
        $shape->getActiveParagraph()->getAlignment()->setHorizontal( Alignment::HORIZONTAL_LEFT );
        $this ->textRun($shape, $title, 12, true);
        
        $shape = $currentSlide->createRichTextShape()->setHeight(20)->setWidth(200)->setOffsetX($x_pos + 220)->setOffsetY($y_pos - 30);
        $shape->getActiveParagraph()->getAlignment()->setHorizontal( Alignment::HORIZONTAL_LEFT );
        $shape->setInsetLeft(0);
        $shape->setInsetRight(0);
        
        $this ->textRun($shape, "Thema", 10, true);
        
        $this ->drawLine($x_pos + 230, $y_pos + 0, 165);
        $this ->drawLine($x_pos + 230, $y_pos + 20, 165);
        $this ->drawLine($x_pos + 230, $y_pos + 40, 165);
        $this ->drawLine($x_pos + 230, $y_pos + 60, 165);
        
        $x_pos = $x_pos + 1;
        $w = $this -> table_cell_width;
        
        $this -> setCellBgColor("KW", 'e2e4e5', true, $y_pos, $x_pos + $w *0);
        $this -> setCellBgColor("Mo", 'e2e4e5', true, $y_pos, $x_pos + $w *1);
        $this -> setCellBgColor("Di", 'e2e4e5', true, $y_pos, $x_pos + $w *2);
        $this -> setCellBgColor("Mi", 'e2e4e5', true, $y_pos, $x_pos + $w *3);
        $this -> setCellBgColor("Do", 'e2e4e5', true, $y_pos, $x_pos + $w *4);
        $this -> setCellBgColor("Fr", 'e2e4e5', true, $y_pos, $x_pos + $w *5);
        $this -> setCellBgColor("Sa", 'e2e4e5', true, $y_pos, $x_pos + $w * 6);
        $this -> setCellBgColor("So", 'e2e4e5', true, $y_pos, $x_pos + $w * 7);
        
        //Ende des Tabellenkopfes 
        while ($monat == date("F", $zeitstempel)) {    //Schleife wird so lange durchlaufen, bis sich der Monat ändert 
            $aktuelle_kw = date("W", $zeitstempel); 
            $a = 0;
            $y_pos = $y_pos +  $this -> table_cell_height;
            
            $this -> setCellBgColor($aktuelle_kw, "e2e4e5", true, $y_pos, $x_pos + $w * $a);
            
            $a++;
            if ($tag_der_woche > 1 && date("d", $zeitstempel) == 1) { 
                for ($i = $tag_der_woche; $i > 1; $i--) { 
                     $this -> setCellBgColor(" ", "FFFFFF", false, $y_pos, $x_pos + $w * $a);
                     $a++;
                } 
            } 

            $x = 0;
            while ($aktuelle_kw == date("W", $zeitstempel)) {    //Schleife wird so lange durchlaufen, bis sich die KW ändert 
                //$temp_klasse1 = 'fcd5b4'; 

                $temp_klasse1 = 'FFFFFF';
                $test_saso = date("N", $zeitstempel);

                if($test_saso == 6 OR $test_saso == 7){
                    $temp_klasse1 = 'fcd5b4'; 
                }
                $x++;

                $this -> setCellBgColor(date("d", $zeitstempel), $temp_klasse1, false, $y_pos, $x_pos + $w * $a);
                $zeitstempel = $zeitstempel + (60*60*24); 
                $a++;
                if (date("j", $zeitstempel) == 1) break;    //Abbruch, wenn sich wärend der Woche der Monat ändert 

            }     
        }
    
        // Filling the empty onces
        for($y = $x; $y < 7; $y++){
         //$this -> setCellBgColor($row, " ", "FFFFFF", false);
        }     
    }
}    



