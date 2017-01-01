<?php

/**
 * Public Function for Download a Lizenz
 * 
 * @path download/SID
 * @global type $user
 * @param type $sid
 * @return type
 */
function _vku_only_download($sid){

  // explode SID
  drupal_set_title("Download");
  
  $manager = new \LK\Kampagne\LizenzManager();
  $lizenz = $manager ->getLizenzFromSignature($sid);
  
  if(!$lizenz) {
    $manager ->logError("Fehlerhafter Downloadlink");
    drupal_set_message('Der Downloadlink ist fehlerhaft. Bitte überprüfen Sie den Download-Link und versuchen Sie es erneut.', 'error');
    
    return theme("lizenz_download");
  }
  
  $test = $lizenz->canDownload();
  
  // Wenn kein Zugriff mehr vorhanden ist
  if(!$test["access"]){
      drupal_set_message($test["reason"],'warning');
      
      return theme("lizenz_download");
  }
  
  // Show Download link
  if(!isset($_GET["download"])){
    return theme("lizenz_download", 
            [
                'lizenz' => $lizenz ->getTemplateData(), 
                'link' => $lizenz->getDownloadLink(true)
            ]
    );
  }
  
  $download = new \LK\LizenzDownload($lizenz);
  $download ->downloadZip();
}