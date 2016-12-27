<?php

function _vku_direct_generate($node){
global $user;
	
  // Testverlag  		
  if(lk_is_in_testverlag($user)){
      $return["error"] = 1;
      $return["msg"] = 'Im Testmodus düfen Sie keine Kampagnen lizenzieren.';
      drupal_json_output($return);
      drupal_exit();   
  }



  $return = array();
  $return["error"] = 0;
  $return["nid"] = $node -> nid;
  $return["node"] = $node;
  
  
  if(!$node -> plzaccess){
    $return["error"] = 1;
    $return["msg"] = 'Die Kampagne ist für Sie nicht buchbar.';
    
    drupal_json_output($return);
    drupal_exit();   
  }

  $vku = new VKUCreator('new',
  	array(
  		'vku_status' => 'purchased',
  		'vku_purchased_date' => time(),
  		'vku_title' => $node -> title,
  		'vku_status' => 'purchased',
  		'vku_untertitel' => 'Direkt bestellte Lizenz',
  		'vku_generic' => 1,
  		'vku_company' => ''
  	));
 
  $vku -> addKampagne($node -> nid);
  $vku ->logEvent("direct-vku", "Eine Lizenz wurde direkt erstellt.");
  $vku_id = $vku -> getId();
  $vku = new VKUCreator($vku_id);
  
  $lizenz = createLizenz($node -> nid, $vku);
  $lizenz_id = $lizenz -> id;
  
  // Letting the ZIP beeing created
  include_once("sites/all/modules/lokalkoenig/vku/download.inc");
  _vku_file_create_zip($lizenz);

  $return["link"] = $vku -> url(); 
  $return["lizenz_dl_link"] = _lk_generate_download_link($lizenz_id); 
  $return["lizenz_dl_size"] = format_size($lizenz -> lizenz_download_filesize);
  
  $return["theme"] = theme('node_page_lizenz_purchased', array("lizenz" => $lizenz, 'url' => $return["lizenz_dl_link"]));
  
  drupal_json_output($return);
  drupal_exit();     
}

?>