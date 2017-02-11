<?php

namespace LK\VKU\Vorlage;
use LK\VKU\PageManager;



/**
 * Description of Vorlage
 *
 * @author Maikito
 */
class Vorlage extends PageManager {

  function __construct() {
    parent::__construct();
  }

  /**
   * Clones a VKU
   *
   * @param \VKUCreator $source
   * @param \VKUCreator $new_vku
   */
  function cloneVKUPages(\VKUCreator $source, \VKUCreator $new_vku){
    
    // save the kampagnen
    // and add them add them after the title

    $kampagnen = $this->getKampagnenPages($new_vku);
    
    $this ->removeAllPages($new_vku);
    $config = $this->generatePageConfiguration($source);

    $category_order = 0;
    $page_order = 0;
    while(list($key, $val) = each($config)){

      $category = $this->_getCategoryData($val['cid']);
      $cid = $this->addCategory($new_vku, $category -> category, $category_order);
      $category_order++;

      if($val['container'] === FALSE){
        $page_data = (array)$this->_getPageData($val['id']);
        $this->clonePage($new_vku, $page_data, $cid, $page_order);
        $page_order++;
        
        // Add Kampagnen
        if($page_data['data_module'] === 'default' && $page_data['data_class'] === 'title'):
          foreach($kampagnen as $kampagne):
            $cid = $this->addCategory($new_vku, 'other', $category_order);
            $category_order++;

            $this->clonePage($new_vku, $kampagne, $cid, $page_order);
            $page_order++;
          endforeach;
        endif;

        continue;
      }

      foreach ($val['children'] as $child){
         $page_data = (array)$this->_getPageData($child['id']);
         $this->clonePage($new_vku, $page_data, $cid, $page_order);
         $page_order++;
      }
    }
  }

  /**
   * Takes over a Vorlage
   *
   * @param \VKUCreator $vku
   * @param int $vku_id
   *
   * @return \VKUCreator
   */
  static public function takeOver(\VKUCreator $vku, $vku_id){

    $source = new \VKUCreator($vku_id);

    $manager = new Vorlage();
    $manager ->cloneVKUPages($source, $vku);

    return $vku;
  }


  /**
   * Gets the Nodes from a VKU
   *
   * @param \VKUCreator $vku
   * @return array
   */
  private function getKampagnenPages(\VKUCreator $vku){

    $kampagnen = [];
    $dbq = db_query("SELECT id FROM lk_vku_data WHERE vku_id=:vku_id AND data_class='kampagne' ORDER BY data_delta ASC", [':vku_id' => $vku ->getId()]);
    foreach($dbq as $all){
      $kampagnen[] = (array)$this->_getPageData($all -> id);
    }

    return $kampagnen;
  }
}
