<?php

namespace LK\VKU\Data;

/**
 * Description of VKUDataManipulator
 *
 * @author Maikito
 */
abstract class VKUDataManipulator {
  
  /**
   * Removes the current Page
   *
   * @param \VKUCreator $vku
   * @param type $module
   * @param type $id
   * @return boolean
   */
  function removePage(\VKUCreator $vku, $id){
    $data = $this->_getPageData($id);

    $obj = $this->getModule($data -> data_module);
    if($obj){
      $obj ->removeItem($vku, $id, (array)$data);
      $this->_removePage($vku, $id);
      return false;
    }

    $this->_removePage($vku, $id);
  }

  /**
   * Sets the Page-Status
   *
   * @param int $id
   * @param boolean $status
   */
  protected function setPageStatus($id, $status){
    db_query("UPDATE lk_vku_data SET data_active=:status WHERE id=:id", [':status' => $status, ':id' => $id]);
  }

  /**
   * Saves the Delta-Position
   *
   * @param type $id
   * @param type $delta
   */
  protected function setPageOrder($id, $delta){
    db_query("UPDATE lk_vku_data SET data_delta=:delta WHERE id=:id", [':delta' => $delta, ':id' => $id]);
  }

  /**
   * Update the sort Order of the Category
   *
   * @param type $cid
   * @param type $delta
   */
  function setCategoryOrder($cid, $delta){
    db_query("UPDATE lk_vku_data_categories SET sort_delta=:delta WHERE id=:cid", [':cid' => $cid, ':delta' => $delta]);
  }

  /**
   * Gets the Category-Data
   *
   * @param int $cid
   * @return type
   */
  protected function _getCategoryData($cid){
    $dbq = db_query("SELECT * FROM lk_vku_data_categories WHERE id=:cid", [':cid' => $cid]);
    $data = $dbq -> fetchObject();

    return $data;
  }

  /**
   * Removes a Category
   *
   * @param int $cid
   */
  protected function _removeCategory($cid){
    db_query("DELETE FROM lk_vku_data_categories WHERE id=:cid", [':cid' => $cid]);
  }


  /**
   * Gets the Page-Data
   *
   * @param type $id
   * @return \stdClass
   */
  protected function _getPageData($id){
    $dbq = db_query('SELECT * FROM lk_vku_data WHERE id=:id', [':id' => $id]);
    return $dbq -> fetchObject();
  }

  /**
   * Removes a Page from a VKU
   *
   * @param \VKUCreator $vku
   * @param type $id
   * @return boolean
   */
  protected function _removePage(\VKUCreator $vku, $id){

    $page = $this ->_getPageData($id);
    if(!$page){
      return FALSE;
    }

    // data_category can be 0
    if($page -> data_category){
      $category = $this->_getCategoryData($page -> data_category);

      if($category && !in_array($category -> category, ['online', 'print'])){
        $this->_removeCategory($category -> id);
      }
    }

    db_query("DELETE FROM lk_vku_data WHERE id=:id", [':id' => $id]);
    return TRUE;
  }

  /**
   * Adds an Container-Category
   *
   * @since 2016-07-17
   *
   * @param String $type
   * @param Integer $delta
   * @return Integer
   */
  protected function addCategory(\VKUCreator $vku, $type, $delta){
    $vku_id = $vku -> getId();
    $category = db_insert('lk_vku_data_categories')->fields(array('vku_id' => $vku_id, 'category' => $type, 'sort_delta' => $delta))->execute();

    return $category;
  }


  /**
   *
   * @param \VKUCreator $vku
   * @param type $id
   * @param type $item
   * @return boolean
   */
  function updatePage(\VKUCreator $vku, $id, $item){

    $data = $this->_getPageData($id);
    dpm($data);
    $obj = $this->getModule($data -> data_module);
    if(!$obj){
      return false;
    }

    return $obj ->updateItem($vku, $id, $item);
  }


  /**
   * Clones a Page
   *
   * @param \VKUCreator $vku
   * @param array $data
   * @param int $cid
   * @param int $delta
   * @return int
   */
  protected function clonePage(\VKUCreator $vku, $data, $cid, $delta){

    if(isset($data['id'])){
      unset($data['id']);
    }

    $data['vku_id'] = $vku ->getId();
    $data['data_created'] = time();
    $data['data_delta'] = $delta;
    $data['data_category'] = $cid;

    $obj = $this->getModule($data['data_module']);

    if(!$obj){
      return false;
    }

    $insert = $obj ->renewItem($vku, $data);
    return db_insert('lk_vku_data')->fields($insert)->execute();
  }

  /**
   * Adds a new Page
   *
   * @param \VKUCreator $vku
   * @param int $cid
   * @param string $module
   * @param id $id
   * @param array $children
   * @return int
   */
  protected function addNewPage(\VKUCreator $vku, $cid, $module, $id, $children = []){

    $insert = [];
    $insert['vku_id'] = $vku ->getId();
    $insert['data_module'] = $module;
    $insert['data_class'] = $id;
    $insert['data_created'] = time();
    $insert['data_entity_id'] = 0;
    $insert['data_serialized'] = null;
    $insert['data_delta'] = 0;
    $insert['data_active'] = 1;
    $insert['data_category'] = $cid;

    $obj = $this->getModule($module);

    if(!$obj){
      $this->logError('Module ' . $module . " not found!");
      return false;
    }

    $data = $obj ->saveNewItem($insert);
    return db_insert('lk_vku_data')->fields($data)->execute();
  }

  /**
   * Removes all the Pages from the VKU
   * Used to remove (Delete) and clear them (Template)
   *
   * @param \VKUCreator $vku
   */
  public function removeAllPages(\VKUCreator $vku){
    // Make a VKU2-Check to get a propper Configuration for removing
    $vku -> vku2Check();

    $config = $this->generatePageConfiguration($vku);
    while(list($key, $val) = each($config)){
      // A container
      if($val['container']){
        foreach($val['children'] as $child){
          $this->removePage($vku, $child['id']);
        }
        $this->_removeCategory($key);
      }
      else {
        $this->removePage($vku, $val['id']);
      }
    }
  }

  /**
   * Removes a VKU and gives the Modules a Chance to clean UP
   *
   * @param \VKUCreator $vku
   * @return boolean
   */
  public function removeVKU(\VKUCreator $vku){
    $this ->removeAllPages($vku);

    // Remove the last Evidence
    $vku ->remove();
    return true;
  }
}
