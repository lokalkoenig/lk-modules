<?php

namespace LK\VKU\Vorlage;
use LK\VKU\PageManager;



/**
 * Description of Vorlage
 *
 * @author Maikito
 */
class Vorlage extends PageManager {

  function __construct(\VKUCreator $vku) {
    parent::__construct($vku);
  }

  /**
   * Clones a VKU
   *
   * @param \VKUCreator $source
   * @param \VKUCreator $new_vku
   */
  function cloneVKUPages(\VKUCreator $source){
    // save the kampagnen
    // and add them add them after the title

    $kampagnen = $this->getKampagnenPages();
    
    $this ->removeAllPages();

    // Get source configuation
    $manager = new PageManager($source);
    $config = $manager->generatePageConfiguration($source);

    $category_order = 0;
    $page_order = 0;
    while(list($key, $val) = each($config)){

      $category = $this->_getCategoryData($val['cid']);
      $cid = $this->addCategory($category -> category, $category_order);
      $category_order++;

      if($val['container'] === FALSE){
        $page_data = (array)$this->_getPageData($val['id']);
        $this->clonePage($page_data, $cid, $page_order);
        $page_order++;
        
        // Add Kampagnen
        if($page_data['data_module'] === 'default' && $page_data['data_class'] === 'title'):
          foreach($kampagnen as $kampagne):
            $cid = $this->addCategory('other', $category_order);
            $category_order++;

            $this->clonePage($kampagne, $cid, $page_order);
            $page_order++;
          endforeach;
        endif;

        continue;
      }

      foreach ($val['children'] as $child){
         $page_data = (array)$this->_getPageData($child['id']);
         $this->clonePage($page_data, $cid, $page_order);
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

    $manager = new Vorlage($vku);
    $manager ->cloneVKUPages($source);

    return $vku;
  }


  /**
   * Gets the Nodes from a VKU
   *
   * @param \VKUCreator $vku
   * @return array
   */
  private function getKampagnenPages(){

    $vku = $this->getVKU();

    $kampagnen = [];
    $dbq = db_query("SELECT id FROM lk_vku_data WHERE vku_id=:vku_id AND data_class='kampagne' ORDER BY data_delta ASC", [':vku_id' => $vku ->getId()]);
    foreach($dbq as $all){
      $kampagnen[] = (array)$this->_getPageData($all -> id);
    }

    return $kampagnen;
  }
}
