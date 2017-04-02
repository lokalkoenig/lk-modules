<?php

require_once __DIR__ .'/functions.php';

if(!defined('LK_TEST_VERLAG_UID')){
    define("LK_TEST_VERLAG_UID", 352);
}


class VKUCreator extends \LK\VKU\Data\VKU {

  /**
   * Gets a VKU from the Database
   *
   * @param int $id
   * @throws Exception
   */
  function __construct($id){
    $test_int = (int)$id;
   
    if(!$test_int) {
      throw new Exception('VKU ID: '. $id .' can not be empty');
    }

    $dbq = db_query('SELECT * FROM lk_vku WHERE vku_id=:vku_id', [':vku_id' => $test_int]);
    $all = $dbq->fetchObject();
    if(!$all) {
      throw new Exception('VKU ID: '. $id .' can not be empty');
    }

    parent::__construct((array)$all);
  }


  /**
   * @deprecated
   */
  function getPages(){
    
    $pages = [];

    $dbq = db_query('SELECT * FROM lk_vku_data WHERE vku_id=:id ORDER BY data_delta ASC', [':id' => $this->getId()]);
    foreach($dbq as $all) {
      $pages[$all->id] = (array)$all;
    }
    
    return $pages;
  }

  /**
   * @deprecated
   */
  function saveItemOrder($id, $order){
    db_query('UPDATE lk_vku_data SET data_delta=:delta WHERE id=:id', [':id' =>$id, ':delta' => $order]);
  }

  /**
   * @deprecated
   */
  function getPage($id){
    $dbq = db_query('SELECT * FROM lk_vku_data WHERE id=:id', [':id' => $id]);
    $all = $dbq->fetchObject();
  
    return (array)$all;
  }

  /**
   * @deprecated
   */
  function removePage($id){
    db_query("DELETE FROM lk_vku_data WHERE id='". $id ."'");
  }

  /**
   * @deprecated
   */
  function toggleItemStatus($id){

    $page = $this->getPage($id);
    $new_status = 1;

    if($page['data_active']) {
      $new_status = 0;
    }

    $this->setPageStatus($id, $new_status);

    return TRUE;
  }

  /**
   * @deprecated
   */
  function setPageOrder($pid, $delta){
    db_query("UPDATE lk_vku_data SET data_delta='". $delta ."' WHERE id='". $pid ."'");      
  }

  /**
   * @deprecated
   */
  function setPageStatus($pid, $status = true){
    db_query('UPDATE lk_vku_data SET data_active"=:new WHERE id=:id', [':id' => $pid, ':new' => $status]);
  }

  /**
   * @deprecated
   */
  function getPageId($class){
    $dbq = db_query('SELECT * FROM lk_vku_data WHERE vku_id=:id AND data_class=:dclass', [':id' => $this->getId(), ':dclass' => $class]);
    $all = $dbq->fetchObject();

    if(!$all) {
      return FALSE;
    }

    return (array)$all;
  }
 
 
  /**
   * @deprecated
   */
  function cloneVku(){
    $current = \LK\current();
    $verlag_uid = $current ->getVerlag();
      
    $array = array();
    $array["uid"] = $current ->getUid();
    $array["vku_created"] = time();
    $array["vku_status"] = 'active';
    $array["verlag_uid"] = $verlag_uid; 
    $array["vku_oldformat"] = 0; 
    $array["vku_title"] = $this -> get('vku_title');
    $array["vku_company"] = $this -> get('vku_company');
    $array["vku_untertitel"] = $this -> get('vku_untertitel');

    $vku_id = db_insert('lk_vku')->fields($array)->execute();
    $this->setAction('create-vku', $vku_id);

    $pages = $this->getPages();
 
    foreach($pages as $page) {
      $page['vku_id'] = $vku_id;
      unset($page['id']);
      db_insert('lk_vku_data')->fields($page)->execute();
    }

    return $vku_id; 
  }
}
