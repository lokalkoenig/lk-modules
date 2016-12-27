<?php





function _vku_file_create_zip(&$lizenz){
   
   include_once('sites/all/modules/transliteration/transliteration.inc');
   
   $savedir = 'sites/default/private/downloads';
   $file_name = $lizenz -> id .'.zip';
   $zip = new ZipArchive(); 
   $vku = new VKUCreator($lizenz -> vku_id);
   
   // prevent
   if(file_exists($savedir . '/' . $file_name)){
      unlink($savedir . '/' . $file_name);
   }
   
   $node = node_load($lizenz -> nid);
   $zip->open($savedir . '/' . $file_name, ZIPARCHIVE::CREATE);
   
   $text = variable_get('lk_vku_info_text_downloadfile', '');
   
   $variables = array();
   $variables["[!node_title]"] = $node -> title;
   $variables["[!node_sid]"] = _lk_get_kampa_sid($node);
   $variables["[!node_link]"] = url("node/" . $node -> nid, array("absolute" => true));
   
   $term = taxonomy_term_load($lizenz -> lizenz_paket);
   $variables["[!node_paket]"] = $term -> name;
   $variables["[!lizenz_start]"] = date("d.m.Y H:i:s", $lizenz -> lizenz_date);
   
   $days =  (lk_get_lizenz_time(user_load($lizenz -> lizenz_uid)));  
   
   $newEndingDate = strtotime(date("Y-m-d H:i:s", time()) . " + ". $days ." day");
  
   $variables["[!lizenz_end]"] = date("d.m.Y H:i:s", $newEndingDate);
   
   $text = strtr($text, $variables); 
    
     
   // Adding an Info-File
   $zip->addFromString('info.txt', $text);
   
   foreach($node -> medien as $med){
      $url = file_create_url($med->field_medium_source['und'][0]['uri']);
      $url = str_replace($GLOBALS['base_url'] . "/system/files/", "", $url);
      $url = 'sites/default/private/' . $url;
      
      if(file_exists($url)){
        ($zip->addFile($url, $med -> id . "-" . $med->field_medium_source['und'][0]['filename'])); 
      }
   }
   
   $result = $zip->close();
   
   // Zip-Archive konnte erstellt werden
   if($result){
     $filename_public = date("Y-m-d"). '-' . $lizenz -> id;
     $company = $vku -> get('vku_company');
     if($company) $filename_public .= '-' . transliteration_clean_filename($company);
     $filename_public .= '.zip';

     $filesize = filesize($savedir . '/' . $file_name); 
     
     $lizenz -> lizenz_download_filename = $filename_public;
     $lizenz -> lizenz_download_filesize = $filesize;
     $lizenz -> lizenz_download_serverfilename = $file_name;   
     
     db_query("UPDATE lk_vku_lizenzen SET 
        lizenz_download_filename='". $filename_public ."',
        lizenz_download_serverfilename='". $file_name ."',
        lizenz_download_filesize='". $filesize ."'
        WHERE id='". $lizenz -> id  ."'"); 
   } 
   
return $result;   
}


function _vku_only_download($sid){
global $user;  
  // explode SID
  
  
  drupal_set_title("Download");
  
  $explode = explode("-", $sid);
  
  $date = (int)$explode[0];
  $nid = (int)$explode[1];
  $uid = (int)$explode[2];
  $paket = (int)$explode[3];
  
  $dbq = db_query("SELECT * FROM 
    lk_vku_lizenzen 
    WHERE lizenz_date='". $date  ."' AND nid='". $nid ."' AND lizenz_uid='". $uid ."' AND lizenz_paket='". $paket ."'"); 
  
  $lizenz = $dbq -> fetchObject();
  
  if(!$lizenz) {
    drupal_set_message('Der Downloadlink ist fehlerhaft. Bitte überprüfen Sie den Download-Link und versuchen Sie es erneut.', 'error');
    return theme("lizenz_download");
  }
  
  $vku = new VKUCreator($lizenz -> vku_id);
 
  $test = _vku_download_file_check_valid($vku, $lizenz);
  if(!$test["access"]){
      drupal_set_message($test["reason"],'warning');
      return theme("lizenz_download");
  }
      if(!isset($_GET["download"])){
    return theme("lizenz_download", array('lizenz' => $lizenz, 'link' => url("download/" . $sid, array("query" => array("download" => 1)))));
  
  }
  
  // Ready for Download
  _vku_download_file($user, $lizenz -> vku_id, $lizenz -> id);
}



function _vku_download_file($account, $vku_id, $lizenz_id){

  // Check-File-Access
  // Download über das Portal als angemeldeter Nutzer/Nutzerin
  $savedir = 'sites/default/private/downloads';

  $lizenz = getLizenz($lizenz_id); 
  $vku = new VKUCreator($vku_id);
  
  $test = _vku_download_file_check_valid($vku, $lizenz);
  
  if(!$test["access"]){
     drupal_set_message($test["reason"],'warning');
     drupal_goto($vku -> url());
     drupal_exit(); 
  }
  
  
  if(!$lizenz -> lizenz_download_serverfilename){
      if(!_vku_file_create_zip($lizenz)){
         drupal_set_message('Probleme beim Erstellen der ZIP-Datei.','warning');
         drupal_goto($vku -> url());
         drupal_exit(); 
      }
      else {
         // Der erste Download wird mitgeloggt 
         db_query("UPDATE lk_vku_lizenzen SET lizenz_download_date='". time() ."' WHERE id='". $lizenz -> id  ."'"); 
      }
  }
  
  lk_vku_download_log_download($lizenz, NULL);  

  ob_clean();
  ob_end_flush();
  header("Content-Type: application/zip");
  header("Content-Disposition: attachment; filename=\"". $lizenz -> lizenz_download_filename ."\"");
  readfile($savedir . '/' . $lizenz -> lizenz_download_serverfilename);
  drupal_exit();   
}


function lk_vku_download_log_download($lizenz, $public_download_sid = NULL){
global $user;

    // Zähler erhöhen
    db_query("UPDATE lk_vku_lizenzen SET lizenz_downloads = lizenz_downloads+1 WHERE id='". $lizenz -> id  ."'"); 
 
    $array = array('lizenz_id' => $lizenz -> id);
    $array["download_date"] = time();
    
    if($public_download_sid){
         $array["download_link"] = $public_download_sid;
         $array["uid"] = 0;
    }
    else {
       $array["uid"] = $user -> uid;
    }
    
    db_insert('lk_vku_lizenzen_downloads')->fields($array)->execute();  
}


?>