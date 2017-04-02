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

  $kampagne = new \LK\Kampagne\Kampagne($node);

  $return = array();
  $return["error"] = 0;
  $return["nid"] = $node -> nid;
  $return["node"] = $node;

  if(!$kampagne->canPurchase()) {
    $return["error"] = 1;
    $return["msg"] = 'Die Kampagne ist für Sie nicht buchbar.';

    drupal_json_output($return);
    drupal_exit();
  }

  $options =  [
    'vku_status' => 'purchased',
    'vku_purchased_date' => time(),
    'vku_title' => $node -> title,
    'vku_untertitel' => 'Direkt bestellte Lizenz',
    'vku_generic' => 1,
    'vku_company' => '',
  ];

  $vku = \LK\VKU\VKUManager::createEmptyVKU($current, $options);
  $vku -> addKampagne($node -> nid);
  $vku->setStatus('purchased');

  $manager = new \LK\Kampagne\LizenzManager();
  $lizenz = $manager -> create($node -> nid, $vku);
  $lizenz -> generateZIP();
  
  $return["theme"] = theme('node_page_lizenz_purchased', ["lizenz" => $lizenz ->getTemplateData()]);
  
  drupal_json_output($return);
  drupal_exit();

}
