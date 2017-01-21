<?php
/**
 * 
 * Generates a Test-Document, based on the Verlags-Values.
 * @version 1.0 / 2015-11-03 
 * 
 * @param type $user
 * @return type
 * 
 */

function lokalkoenig_user_verlag_testpdf($user){
   $account = \LK\get_user($user);
   
   if(!$account || !$account ->isVerlag()){
     drupal_goto('<front>');
   }
   
   $module_dir = 'sites/all/modules/lokalkoenig/vku/pages/';
   $pdf = \LK\PDF\PDF_Loader::getPDF($user -> uid);
   
    //?hg_color=fa35ab&hg_color_titel=1ab51f&vg_color_titel=ffffff
    if(!empty($_GET["hg_color"])){
       $pdf -> setVKUColor("bg_color", $_GET["hg_color"]);
    }
  
    if(!empty($_GET["hg_color"])){
       $pdf -> setVKUColor("bg_color", $_GET["hg_color"]);
    }
    
    if(!empty($_GET["hg_color_titel"])){
       $pdf -> setVKUColor("title_bg_color", $_GET["hg_color_titel"]);
    }
    
    if(!empty($_GET["vg_color_titel"])){
       $pdf -> setVKUColor("title_vg_color", $_GET["vg_color_titel"]);
    }
    
    $pdf -> SetMargins(0);
    $pdf -> SetTopMargin(30);
    $pdf->AliasNbPages();
    $pdf -> SetTextColor(69, 67, 71);
    
    $vku = new stdClass;
    
    require($module_dir .'a-cover.php');
    require($module_dir .'z-contact.php');
    
    drupal_get_messages();
    header("Content-Type: application/pdf");
    $pdf -> Output();
    return ;
}




?>