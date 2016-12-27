<?php

function _vku_ontheflygenerate($account, $vku_id){
global $user;
  
   $vku = new VKUCreator($vku_id);
   if(!$vku -> is('created')){
      die('Die VKU ist veraltet');
   }

   $vkustatus = $vku -> getStatus();
   $vkuauthor = $vku -> getAuthor();
  
  if($account -> uid != $vkuauthor AND !lk_is_moderator()){
    exit;
  }
  
  $result = _vku_generate_final_vku($vku);
  
  $return = array();
  $return["error"] = 0;
  
  if(!$result){
    $return["error"] = 1;
    $return["msg"] = 'Bei der Generierung gab es einen Fehler.';
    $return["link"] = url('user/'. $vkuauthor .'/vku');
    $vku ->logEvent('warning', 'Problem mit der PDF Generierung');
     
    if(isset($_GET["ajax"])){
      drupal_json_output($return);
      drupal_exit();
    }
    
    else {
      drupal_set_message($return["msg"]);
      drupal_goto($return["link"]);
      drupal_exit();
    }  
  }
  
  
  
  $vku = new VKUCreator($vku_id);
  $vkuauthor = $vku -> getAuthor();  
  $filesize = $vku -> getValue("vku_ready_filesize");
  
  $return["downloadlink"] = url('user/' . $vkuauthor . "/vku/" . $vku_id . "/download");
  $return["filesize"] = format_size($filesize);
  
  $vku ->logEvent('pdf', 'PDF generiert ('. $return["filesize"] .')');
  
  
  if(isset($_GET["ajax"])){
    drupal_json_output($return);
    drupal_exit();
  }  
  else {
    drupal_set_message("Der Download wurde erfolgreich erstellt.");
    drupal_goto($vku -> getUrl());
    drupal_exit();
  }
}

function _vku_generate_final_vku_v2(VKUCreator $vku){
    $pdf = vku_generate_get_pdf_object($vku); 
    $fn = $vku -> getId() . ".pdf";
    
    $pages = $vku -> getPages();
    
    
    while(list($key, $page) = each($pages)){
      if(!$page["data_active"]) {
          continue; 
      }
      
      $mod = $page["data_module"];  
      $func_name = 'vku_generate_pdf_' . $mod;

      if(function_exists($func_name)){
        $func_name($vku, $page, $pdf);
      }
    }
    
    $dir = "sites/default/private/vku/";
    $pdf->Output($dir . $fn, 'F');

    if(!file_exists("sites/default/private/vku/" . $fn)){
        return false;   
    }
    
    $vku -> set("vku_ready_filename", $fn);
    $vku -> set("vku_ready_time", time()); 
    $vku -> set("vku_ready_filesize", filesize("sites/default/private/vku/" . $fn)); 
    
    return true;
}    




 function _vku_generate_final_vku($vku){

     
  $author = $vku -> getAuthor();
  $id = $vku -> getId();

  
  $pdf = vku_generate_get_pdf_object($vku); 
	$fn = $id . ".pdf";

  $pages = $vku -> getPages();


  while(list($key, $page) = each($pages)){
      if(!$page["data_active"]) continue;

      $mod = $page["data_module"];  
      $func_name = 'vku_generate_pdf_' . $mod;

      if(function_exists($func_name)){
        $func_name($vku, $page, $pdf);
      }
  }

  //drupal_get_messages();
  $dir = "sites/default/private/vku/";
  $pdf->Output($dir . $fn, 'F');

	if(!file_exists("sites/default/private/vku/" . $fn)){
 		return false;   
	}

  $vku -> set("vku_ready_filename", $fn);

  verlag_log(1, 'Verkaufsunterlagen', 'Verkaufsunterlage zum Download bereit', array('uid' => $author, 'vku_id' => $id));
  
  $vku -> set("vku_ready_time", time()); 
  $vku -> set("vku_ready_filesize", filesize("sites/default/private/vku/" . $fn)); 
  $vku -> set("vku_status", 'ready'); 

    return true; 
  }
  

?>