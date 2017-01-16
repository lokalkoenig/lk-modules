<?php

namespace LK\VKU\Pages;
use LK\VKU\PageInterface;

/**
 * Description of PageDefault
 *
 * @author Maikito
 */
class PageDefault extends PageInterface {
  
  var $titles = [
      'wochen' => 'Medienargumentation Wochen-/Anzeigeblätter',
      'tageszeitung' => 'Medienargumentation Tageszeitungen',
      'onlinewerbung' => 'Online-Werbung (Display-Ads)',
      'kontakt' => 'Ihre Kontaktdaten',
      'kplanung' => 'Kampagnenplanung',
      'title' => 'Titelseite',
  ];
  
  function getImplementation(\VKUCreator $vku, $item, $page){
 
    if(!isset($this -> titles[$page["data_class"]])){
      return false; 
    }
    
    $item["preview"] = true;
    $item["delete"] = true;
    $item["id"] = $page["id"];
    $item["pages"] = 1;
    $item["cid"] = $page["data_category"];
    $item["orig-id"] = 'default-' . $page["data_class"];
    $item["title"] = $this -> titles[$page["data_class"]];
    
    if($page["data_class"] === 'title'){
      $item["title"] = 'Titelseite: <span class="vku-title">' . $vku->get('vku_title') . '</span>';
      $item["delete"] = false;
      $item["deactivate"] = true;
      $item["container"] = false;
      $item["empty_shown"] = true;
    }
    
    return $item;  
  }
  
  function getOutputPDF(){
    
  }
  
  function getOutputPPT(){
    
  }
}
