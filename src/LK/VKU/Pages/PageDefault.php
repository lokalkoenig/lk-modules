<?php

namespace LK\VKU\Pages;
use LK\VKU\Pages\Interfaces\PageInterface;

/**
 * Description of PageDefault
 *
 * @author Maikito
 */
class PageDefault extends PageInterface {

  /**
   * Gets the Page-Title
   *
   */
  public static function getPageTitle($key){
    $titles = [
      'wochen' => 'Medienargumentation Wochen-/Anzeigeblätter',
      'tageszeitung' => 'Medienargumentation Tageszeitungen',
      'onlinewerbung' => 'Online-Werbung (Display-Ads)',
      'kontakt' => 'Ihre Kontaktdaten',
      'kplanung' => 'Kampagnenplanung',
      'title' => 'Titelseite',
    ];

    if(!isset($titles[$key])){
      return FALSE;
    }

    return $titles[$key];
  }
  
  /**
   * Adds an Item to the VKU
   */
  function saveNewItem(array $item){
    return $item;
  }
  
  function updateItem($pid, array $item){ 
    // do nothing
  }

  function getImplementation($item, $page){

    $title = self::getPageTitle($page["data_class"]);

    if(!$title){
      return false; 
    }

    $vku = $this->getVKU();
    
    $item["preview"] = true;
    $item["delete"] = true;
    $item["id"] = $page["id"];
    $item["pages"] = 1;
    $item["cid"] = $page["data_category"];
    $item["orig-id"] = 'default-' . $page["data_class"];
    $item["title"] = $title;
    
    if($page["data_class"] === 'title'){
      $item["title"] = 'Titelseite: <span class="vku-title">' . $vku->get('vku_title') . '</span>';
      $item["delete"] = false;
      $item["deactivate"] = true;
      $item["container"] = false;
      $item["empty_shown"] = true;
      $item["active"] = $page['data_active'];
    }

    return $item;  
  }
  
  /**
   * Gets the possibile Pages
   * 
   * @param string $category
   * @return array
   */
  function getPossibilePages($category){
    
    $items = [];
    $show_documents = $this->getVKU()->getVerlagSetting('vku_standard_documents', []);
   
    if($category === 'print'){
      $items["tageszeitung"] = self::getPageTitle('tageszeitung');
      $items["wochen"] = self::getPageTitle('wochen');
    }
  
    if($category === 'online'){
      $items["onlinewerbung"] = self::getPageTitle('onlinewerbung');
    }
    
    if($category === 'sonstiges'){
      $items["kplanung"] = self::getPageTitle('kplanung');
      $items["kontakt"] = self::getPageTitle('kontakt');
    }

    $return_items = [];
    foreach($items as $key => $item) {
      if($show_documents === [] || in_array($key, $show_documents)) {
        $return_items['default-' . $key] = $item;
      }
    }

    return $return_items;
  }
  
  /**
   * Adds a PDF Page
   * 
   * @param array $page
   * @param \PDF $pdf
   */
  function getOutputPDF($page, \LK\PDF\LK_PDF $pdf){
    
    $static_pages = [
      'title' => '\\LK\\VKU\\Pages\\StaticPages\\Title',
      'contact' => '\\LK\\VKU\\Pages\\StaticPages\\Contact',
      'kontakt' => '\\LK\\VKU\\Pages\\StaticPages\\Contact',
      'onlinewerbung' => '\\LK\\VKU\\Pages\\StaticPages\\Online',
      'wochen' => '\\LK\\VKU\\Pages\\StaticPages\\Wochen',
      'tageszeitung' => '\\LK\\VKU\\Pages\\StaticPages\\Tageszeitungen',
      'kplanung' => '\\LK\\VKU\\Pages\\StaticPages\\Planung',
    ];

    $module = $page["data_class"];
    $vku = new \VKUCreator($page['vku_id']);
    
    if(isset($static_pages[$module])){
      $page = new $static_pages[$module]();
      $page -> render($pdf, $vku, $page);
    }
  }
  
  function getOutputPPT($page, \LK\PPT\LK_PPT_Creator $ppt) {
      $obj = new \LK\PPT\Pages\RenderDefault($ppt);
      $obj->render($page);
  }
}
