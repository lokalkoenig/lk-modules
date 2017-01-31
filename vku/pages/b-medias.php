<?php

// if nothing to present
if(!isset($node -> medien)) return ;

if(!isset($page)){
    $page = array();
}

if(!isset($calcpages)){
   $calcpages = false; 
}

$pdf -> SetTopMargin(30);
$node -> title = swca($node -> title);

$totalpages = 0;

$pdf -> SetLeftMargin(115);
$pdf -> SetRightMargin(25);

$hideonlinesize = $pdf -> verlag_hide_size_online;

if($node -> vku_hide == false){
    $pdf->AddPage();
    $totalpages++;

    if($node ->field_kamp_teaserbild['und'][0]['uri']){
        $bild = max_res_img_test(($node ->field_kamp_teaserbild['und'][0]['uri']), 'medium');
        $pdf->Image($bild, 40, 50, 50); 
    }


    $pdf -> Ln(20);
    $pdf->SetFont(VKU_FONT,'B',30);
    $pdf->MultiCell(0, 16, ($node -> title), 0, 'L', 0);    
    $pdf->SetFont(VKU_FONT,'',26);
    $pdf -> Ln(5);
    $pdf->MultiCell(0, 12, ($node -> field_kamp_untertitel['und'][0]['value']) , 0, 'L', 0);    
    $pdf -> Ln(10);
    $pdf->SetFont(VKU_FONT,'',15);
    $pdf->MultiCell(0, 7, ($node -> field_kamp_teasertext['und'][0]['value']) , 0, 'L', 0);    
    $pdf->SetFont(VKU_FONT,'',12);
    $pdf -> SetTextColor(150, 150, 150);     
    $pdf->Text(260, 175, $node -> sid);
    $pdf -> SetTextColor(69, 67, 71);
}

$pdf -> SetLeftMargin(25);
foreach($node -> medien as $medium){

  if(!isset($medium ->field_medium_bild['und'][0]['uri']) AND !$medium ->field_medium_bild['und'][0]['uri'] AND !$medium -> field_medium_beschreibung['und'][0]["value"]){
      // Bild existiert nicht
  }
  else {
    
    if($medium -> vku_hide == false):  
        $pdf->AddPage();
        $totalpages++;
    
        if($medium ->field_medium_bild['und'][0]['uri']) {
            $bild = max_res_img_test(($medium ->field_medium_bild['und'][0]['uri']), 'big');
            $pdf->Image($bild, 110, 30.25, 246.2); 
        }
    
        $pdf -> SetRightMargin(130);
        $pdf -> Ln(20);
  
        $pdf->SetFont(VKU_FONT,'',24);
        $pdf->MultiCell(0, 10, swca($medium -> title) , 0, 'L', 0);   
        $pdf -> Ln(5); 
        $pdf -> SetRightMargin(185);
        $pdf->SetFont(VKU_FONT,'',15);
        $pdf->MultiCell(0, 7, $medium -> field_medium_beschreibung['und'][0]["value"] , 0, 'L', 0);  
    endif;
  }
  
  
  // Varianten
  // Checken if there is any Variante to be displayed
  if($medium -> vku_hide_varianten){
      continue;
  }
  
  $pdf->AddPage();
  $totalpages++;
  
  // Varianten hinzufügen
  $pdf -> SetRightMargin(25);
  $pdf -> Ln(10);
  
  $tax = taxonomy_term_load($medium->field_medium_typ['und'][0]['tid']);
  $abstand = 15;
  $pdf->SetFont(VKU_FONT,'',12);
  
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
     $pdf->SetFont(VKU_FONT,'',14);
     $pdf->Text(233, 45, "Animiertes Banner"); 
     $pdf->SetFont(VKU_FONT,'',12);
  }
  
  $pdf->SetFont(VKU_FONT,'',24);
  $pdf->MultiCell(0, 10, swca($medium -> title) , 0, 'L', 0); 
  $pdf->SetFont(VKU_FONT,'',12);
  //if($filetype == 'print') continue;
  
  foreach($medium->field_medium_varianten["und"] as $variante){
      
      $test = $x + $width;
      $y = 70; 
        
      if($test > 290 AND ($filetype == 'print')){
          
         $x = $normalabstand;
         $totalpages++;
         $pdf->AddPage();
         $pdf -> Ln(10);
         $y = 70;
  
         $pdf->SetFont(VKU_FONT,'',24);
         $pdf->MultiCell(0, 10, $medium -> title, 0, 'L', 0); 
         $pdf->SetFont(VKU_FONT,'',12);
      }
      elseif($test > 290 AND $filetype != 'print') {
         $y += 60;
         // Make a own Test, if space is enough
         $test2 = $y + $calc;
         
         if($test2 > 200){
            $x = $normalabstand;
            $y = 70; 
            $totalpages++;
            $pdf->AddPage();
            $pdf -> Ln(10);
            $pdf->SetFont(VKU_FONT,'',24);
            $pdf->MultiCell(0, 10, $medium -> title, 0, 'L', 0); 
            $pdf->SetFont(VKU_FONT,'',12);
         }    
           
         $x = $normalabstand;
      }
      
      
      
      
      // 
      if($filetype != 'print'){
        $frames = $variante['gif'];
        
        if($frames){
            if($hideonlinesize == 'yes'){
                $online_title = trim($tax -> field_medientyp_online_label["und"][0]["value"]) . " (". ($variante["title"]).")";
            }
            elseif($hideonlinesize == 'no-label') {
               $online_title = $variante["title"]; 
            }    
            
            else {
               $online_title = $tax -> name . " (". $variante["title"]. ")"; 
            }    
            
            
            
            $pdf -> Text($x, $y, ($online_title));
            
            
            //$pdf -> Text($x, 75 + $calc + 7, );
            $t = 1;
            $count_frames = count($frames);
            foreach($frames as $frame){
              $pdf->SetFont(VKU_FONT,'',10);
              $pdf -> SetFillColor(0,0,0);
              $pdf->Rect($x - 0.1, $y + 3, 14.7, 5.3, 'F'); 
              $pdf -> SetTextColor(255, 255, 255);
              $pdf -> Text($x + 1, $y + 7, ("Frame " . ($t)));
              $pdf -> SetTextColor(69, 67, 71);
               
              $pdf->SetFont(VKU_FONT,'',12);
              $pdf->Image($frame, $x, $y + 8, $width);  
             
              $calculate_height =  getimagesize($frame);
              $image_height = $calculate_height[1];
              $image_width = $calculate_height[0]; 
              
              if($t != $count_frames) 
                $pdf -> Image("sites/all/modules/lokalkoenig/vku/pages/repeat_000000_64.png", $x + $width - 4, $y + 4 - 0.5, 4);
              else {
                $pdf -> Image("sites/all/modules/lokalkoenig/vku/pages/refresh_000000_64.png", $x + $width - 4, $y + 4 - 0.5, 4);
              }
              
              $quotient = $image_width / $width;
              $calc  = (int)($image_height / $quotient); 
              $abstand2 = 3;
              $pdf->Rect($x, $y +8, $width, $calc, 'D'); 
              $x+=$width + $abstand2;
              $t++;
            }
            
            for($i = $t; $i <= 3; $i++){
              $x+=$width + $abstand2;
            }
            
           
            $x += $zwischenabstand;
        }
        
        continue;
      }
      
      if($variante["uri"])
      $url = max_res_img_test($variante["uri"], 'medium');
      $medium_desc = $tax -> name;
      
      if($tax -> description){
         $medium_desc = $tax -> description;
      }
      
       //continue;
      $pdf -> Text($x, 70, utf8_decode($medium_desc));
      $pdf->Image($url, $x, 75, $width);   
      
      $calculate_height =  getimagesize($url);
        //print_r($calculate_height);  
      
      
      $image_height = $calculate_height[1];
      $image_width = $calculate_height[0]; 
      
      $quotient = $image_width / $width;
      $calc  = (int)($image_height / $quotient); 
      
      $pdf -> Text($x, 75 + $calc + 7, ($variante["title"]));
      
      $x+=$width + $abstand;
      
      
  }
  
 
  
}


//$totalpages++;


?>