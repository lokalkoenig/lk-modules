<?php


/**
 * Creates an VKU and Download for a Kampagne
 * 
 * @global type $user
 * @param type $node
 */
function _vku_direct_generate($node){
    
  $current = LK\current();
  
  if($current ->isTestAccount()){
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
  $vku_id = $vku -> getId();
  
  $vku_update = new VKUCreator($vku_id); 
  
  $manager = new \LK\Kampagne\LizenzManager();
  $lizenz = $manager ->create($node -> nid, $vku_update);
  $lizenz -> generateZIP();
  
  $return["link"] = $vku_update -> url(); 
  $return["lizenz_dl_link"] = $lizenz->getDownloadLink(true); 
  $return["lizenz_dl_size"] = format_size($lizenz -> data -> lizenz_download_filesize);
  
  $return["theme"] = theme('node_page_lizenz_purchased', 
          [ 
            "lizenz" => $lizenz, 
            "filesize" => $return["lizenz_dl_size"],
            'url' => $return["lizenz_dl_link"],
          ]
  );
  
  drupal_json_output($return);
  drupal_exit();     
}

?>