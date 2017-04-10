<?php

/**
 *
 *
 * @param \stdClass $account
 * @param int $vku_id
 * @param boolean $ppt
 */
function _vku_download_vku($account, $vku_id, $ppt = FALSE){

  $vku = \LK\VKU\VKUManager::getVKU($vku_id, TRUE);
  if(!$vku){
    drupal_goto("user");
  }

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

   if(!$filename OR !in_array($status, array('downloaded', 'ready', 'deleted'))){
    drupal_goto("user");
   }

   if(!$vku ->is('deleted')) {
    $vku -> setStatus('downloaded');
   }
  
   $company = $vku -> get('vku_company');

  if(!$company){
    $file_name_out = "Ihre_Verkaufsunterlage." . $extension;
  }
  else {
    $file_name_out = "Ideen_fuer_". ucfirst(transliteration_clean_filename($company)) ."." . $extension;
  }

  $vku ->logEvent('Download', "VKU wurde heruntergeladen (". $file_name_out .")");

  ob_clean();
  header('Content-Description: File Transfer');
  header("Content-Type: ". $mime);
  header('Expires: 0');
  header('Cache-Control: must-revalidate');
  header('Pragma: public');
  header('Content-Length: ' . filesize($dir.$filename));
  header('Content-Transfer-Encoding: binary');
  header("Content-Disposition: attachment; filename=\"". $file_name_out ."\"");
  readfile($dir.$filename);

  exit();
}   
