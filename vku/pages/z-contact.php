<?php

if(is_a($vku, 'stdClass')){
   global $user;
   $account = $user;
}
else {
    $account = user_load($vku -> getAuthor());
}
$prof = profile2_load_by_user($account);


$pdf -> SetMargins(0, 0);
$pdf -> SetTopMargin(30);

// MA-Page
$pdf->AddPage();

$pdf -> SetLeftMargin(25);
$pdf -> Ln(10);

$pdf->Image($module_dir .'/telefon.jpg', 180, 100, 100);   
$pdf->SetFont(VKU_FONT,'B',45);

$bild = null;

if(isset($prof['main']->field_profile_bild['und'][0]['uri']) && $prof['main']->field_profile_bild['und'][0]['uri']){
   $url = ($prof['main']->field_profile_bild['und'][0]['uri']);
   $bild = image_style_url('image-framed', $url); 
}   

$layout = $pdf -> verlag_contact_layout;

if($layout == 'default'):

    $pdf->MultiCell(0, 36, ('Ich bin gerne für Sie da!') , 0, 'L', 0);    
    $pdf -> Ln(5);
    $pdf->SetFont(VKU_FONT,'B',24);
    $pdf -> SetLeftMargin(100);
    $pdf->MultiCell(0, 10, ($prof['main']->field_profile_vorname['und'][0]['value'] . ' ' . $prof['main']->field_profile_name['und'][0]['value'] ) , 0, 'L', 0);    
    $pdf->SetFont(VKU_FONT,'',20);
    $pdf->MultiCell(0, 10, ($prof['main']->field_profile_title['und'][0]['value']) , 0, 'L', 0);    
    $pdf -> Ln(8);

    if($bild){
       $pdf->Image($bild,27,82, 55); 
    }

    $pdf->SetFont(VKU_FONT,'',16);
    $pdf->MultiCell(0, 8, 'Telefon: ' . ($prof['main']->field_profile_telefon['und'][0]['value']) , 0, 'L', 0);    

    if(isset($prof['main']->field_profile_telefon_mobil['und'][0]['value'])){
        $pdf->MultiCell(0, 8, 'Mobil: ' . ($prof['main']->field_profile_telefon_mobil['und'][0]['value']) , 0, 'L', 0);    
    }

    $pdf->MultiCell(0, 8, 'E-Mail: ' . ($account -> mail) , 0, 'L', 0);    
    $pdf -> Ln(10);
    $pdf->MultiCell(0, 8, ($prof['main']->field_profile_adresse['und'][0]['organisation_name']) , 0, 'L', 0);    
    $pdf->MultiCell(0, 8, ($prof['main']->field_profile_adresse['und'][0]['thoroughfare']) , 0, 'L', 0);    
    $pdf->MultiCell(0, 8, ($prof['main']->field_profile_adresse['und'][0]['postal_code'] . ' ' . $prof['main']->field_profile_adresse['und'][0]['locality']) , 0, 'L', 0);    
else :
    
  if($bild){
       $pdf->Image($bild,27,40, 40); 
       $pdf -> Ln(66);
    }
    
    
    $pdf->SetFont(VKU_FONT,'B',24);
    $pdf->MultiCell(0, 10, ($prof['main']->field_profile_vorname['und'][0]['value'] . ' ' . $prof['main']->field_profile_name['und'][0]['value'] ) , 0, 'L', 0);    
    $pdf->SetFont(VKU_FONT,'',20);
    $pdf->MultiCell(0, 10, ($prof['main']->field_profile_title['und'][0]['value']) , 0, 'L', 0);    
    
     $pdf->SetFont(VKU_FONT,'',16);
    $pdf->MultiCell(0, 8, 'Telefon: ' . ($prof['main']->field_profile_telefon['und'][0]['value']) , 0, 'L', 0);    

    if(isset($prof['main']->field_profile_telefon_mobil['und'][0]['value'])){
        $pdf->MultiCell(0, 8, 'Mobil: ' . ($prof['main']->field_profile_telefon_mobil['und'][0]['value']) , 0, 'L', 0);    
    }

    $pdf->MultiCell(0, 8, 'E-Mail: ' . ($account -> mail) , 0, 'L', 0);    
    $pdf->SetFont(VKU_FONT,'',14);
    $pdf -> Ln(10);
    $pdf->MultiCell(0, 8, ($prof['main']->field_profile_adresse['und'][0]['organisation_name']) , 0, 'L', 0);    
    $pdf->MultiCell(0, 8, $prof['main']->field_profile_adresse['und'][0]['thoroughfare'] .' | ' . ($prof['main']->field_profile_adresse['und'][0]['postal_code'] . ' ' . $prof['main']->field_profile_adresse['und'][0]['locality']) , 0, 'L', 0);    

    
    
endif;

?>