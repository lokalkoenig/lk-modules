<?php

/**
 * Vku create Preview from Line-Item Link
 * Changed 2015-11-09, added Template Support
 * Changed 2017-01-21, added new Page-Manager Support
 * 
 * @path /vku/%/preview/%
 * @global type $user
 * @param type $vku_id
 * @param type $line_item
 */

function vku_show_line_item_preview($vku_id, $line_item){

  $vku = \LK\VKU\VKUManager::getVKU($vku_id, true);
  
  if(!$vku){
    die('Access denied');
  }
  
  $vku_status = $vku ->getStatus();
  if(!in_array($vku_status, array('active', 'template'))){
    die("Access denied");	  
  }
  
  $page = $vku -> getPage($line_item);
  if(!$page){
    die("Sie haben keinen Zugriff");
  }
  
  $pagemanager = new \LK\VKU\Export\Manager();
  $pagemanager ->generatePDF($vku, $line_item, true);
}
