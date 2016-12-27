<?php

function _vku_download_vku($account, $vkuid){
  $vku = new VKUCreator($vkuid);

  if(!$vku -> is()){
    drupal_not_found();
    drupal_exit();
  }

   $vkustatus = $vku -> getStatus();
   $vkuauthor = $vku -> getAuthor();
  
  // Wenn der Pfad nicht übereinstimmt

  $vku_ready_filename = $vku -> get("vku_ready_filename"); 

  // Dann nicht ausliefern
  if($account -> uid != $vkuauthor OR !$vku_ready_filename){
    drupal_not_found();
    drupal_exit();
  }
  
  if(!in_array($vkustatus, array('downloaded', 'ready'))){
     drupal_not_found();
     drupal_exit();
  }
  
  include_once('sites/all/modules/transliteration/transliteration.inc');
    
  $vku -> setStatus('downloaded');  
  $vku ->logEvent('download', 'Download VKU');
  
  $company = $vku -> getValue('vku_company');
  
  if(!$company){
    $company = time();  
  }
  
  $dir = 'sites/default/private/vku/';
  header("Content-Type: application/pdf");
  header("Content-Disposition: attachment; filename=\"Ideen_fuer_". ucfirst(transliteration_clean_filename($company)) .".pdf\"");
  readfile($dir.$vku_ready_filename);
  drupal_exit();
}


?>