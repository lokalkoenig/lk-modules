<?php

function _vku_download_vku($account, $vku_id, $ppt = false){
global $user;
   
    $vku = new VKUCreator($vku_id);
    if(!$vku -> hasAccess()){  	
    	drupal_goto("user");
    }
    ob_clean();
    $status = $vku -> getStatus();
   
    $dir = 'sites/default/private/vku/';
    include_once(drupal_get_path("module", "transliteration") . '/transliteration.inc');
    
    if($ppt){
        $extension = 'pptx';
        $filename = $vku -> get("vku_ppt_filename");
        $mime = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
    }    
    else {
       $extension = 'pdf'; 
       $filename = $vku -> get("vku_ready_filename");
       $mime = "application/pdf"; 
    }  
    
     if(!$filename OR !in_array($status, array('downloaded', 'ready'))){
     	drupal_goto("user");
     }
    
    $vku -> setStatus('downloaded');
    $company = $vku -> get('vku_company');
    
    if(!$company){
        $file_name_out = "Ihre_Verkaufsunterlage." . $extension;
    }
    else {
      $file_name_out = "Ideen_fuer_". ucfirst(transliteration_clean_filename($company)) ."." . $extension;  
    }
    
    $vku ->logEvent('download', "VKU wurde heruntergeladen (". $file_name_out .")");
  
    header("Content-Type: ". $mime);
    header("Content-Disposition: attachment; filename=\"". $file_name_out ."\"");
    readfile($dir.$filename);
    drupal_exit();
}   

 
?>