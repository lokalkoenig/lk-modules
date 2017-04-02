<?php

namespace LK\VKU\Data;

use LK\VKU\Data\VKUDataManipulator;
use LK\VKU\Data\VKU;
use LK\Kampagne\SperrenManager;

/**
 * Description of VKUUserManager
 *
 * @author Maikito
 */
class VKUUserManager extends VKUDataManipulator {

  use \LK\Log\LogTrait;
  var $LOG_CATEGORY = 'VKUDataManager';
  var $vku;

  function __construct(VKU $vku) {
    $this->vku = $vku;
  }

  /**
   * Gets the VKU-ID
   *
   * @return int
   */
  function getId() {
    return $this->getVKU()->getId();
  }

  /**
   * Checks if a VKU needs to be converted
   */
  public function healthCheck() {
    $dbq = db_query("SELECT count(*) as count FROM lk_vku_data_categories WHERE vku_id='". $this-> getId() ."'");
    $test = $dbq -> fetchObject();

    if($test -> count == 0){
      $this->vku2Convert();
    }
  }
  
  /**
   * Adds the Default pages
   */
  static public function addDefaultPages(VKU $vku) {
    $manager = new VKUUserManager($vku);
    $manager->addDefaultVKUPages();
  }

  /**
   * Adds the default pages
   */
  public function addDefaultVKUPages() {

    if(\vku_is_update_user()){
      $cid = $this->addCategory('title', 1);
      $this->addPage(['data_class' => 'title'], $cid, 1);

      $this->addCategory('print', 2);
      $this->addCategory('online', 3);

      return ;
    }

    $this->addPage(['data_class' => 'title'], 0, 1);
    $this->addPage(['data_class' => 'tageszeitung'], 0, 10);
    $this->addPage(['data_class' => 'wochen'], 0, 11);
    $this->addPage(['data_class' => 'onlinewerbung'], 0, 12);
    $this->addPage(['data_class' => 'kplanung'], 0, 13);
    $this->addPage(['data_class' => 'kontakt'], 0, 100);
  }

  /**
   * VKU 2 Convert
   */
  public function vku2Convert() {
    // Add Standard Categories
    // title + print + online
    $vku = $this->getVKU();

    // Add the Basic onces
    $print_category = $this->addCategory('print', 1);
    $online_categroy = $this->addCategory('online', 2); //db_insert('lk_vku_data_categories')->fields(array('vku_id' => $vku_id, 'category' => 'online', 'sort_delta' => 2))->execute();

    // get Pages
    $pages = $this -> getPages();

     // Check if there is a title page
     $title = false;
     foreach($pages as $page){
      if($page["data_class"] === 'title'){
        $title = true;
        break;
      }
     }

     // Add Title page
     if($title === false){
      //$category = $this -> setDefaultCategory('title', -1);
      $this->addPage(['data_class' => 'title'], 0, -10);
     }

     $pages_new = $this -> getPages();
     $delta = 0;

     foreach($pages_new as $page){
      $cid = 0;

      if($page["data_class"] == 'kampagne'){
        $cid = $this ->addCategory('kampagne', $delta);
      }

      if(in_array($page["data_class"], array('title'))){
        $cid = $this ->addCategory('title', $delta);
      }

      if(in_array($page["data_class"], array('kontakt', 'kplanung'))){
        $cid = $this ->addCategory('other', $delta);
      }

      if(in_array($page["data_class"], array('wochen', 'tageszeitung'))){
        $cid = $print_category;
      }

      if(in_array($page["data_class"], array('onlinewerbung'))){
        $cid = $online_categroy;
      }

      if($cid){
        db_query('UPDATE lk_vku_data SET data_category=:cid WHERE id=:id', [':cid' => $cid, ':id' => $page["id"]]);
      }

      $delta++;
    }

    $this->logNotice("VKU wurde auf das neue Format Konvertiert: " . $vku->getTitle());
  }

  /**
   * Gets all the Pages of the VKU
   *
   * @return array
   */
  private function getPages() {

    $pages = [];
    $dbq = db_query('SELECT * FROM lk_vku_data WHERE vku_id=:id', [':id' => $this->getVKU()->getId()]);
    foreach($dbq as $all) {
      $pages[] = (array)$all;
    }

    return $pages;
  }

  /**
   * Adds a new Page
   *
   * @param array $insert
   * @param int $cid
   * @param int $delta
   * @return int
   */
  public function addPage($insert, $cid, $delta) {

    $insert['vku_id'] = $this->getVKU()->getId();
    $insert['data_category'] = $cid;
    $insert['data_created'] = time();
    
    $overwrites = [];
    $overwrites['data_active'] = 1;
    $overwrites['data_delta'] = $delta;
    $overwrites['data_module'] = 'default';
    $overwrites['data_entity_id'] = 0;

    $fields = array_merge($overwrites, $insert);
    $id = db_insert('lk_vku_data')->fields($fields)->execute();
    return $id;
  }

  /**
   * Returns the VKU
   *
   * @return \LK\VKU\Data\VKU
   */
  function getVKU() {
    return $this->vku;
  }

  public function addKampagne($nid) {
    $cid = $this->addCategory('other', 5);
    
    $insert = [];
    $insert['data_module'] = 'node';
    $insert['data_class'] = 'kampagne';
    $insert['data_entity_id'] = $nid;
    $insert['data_serialized'] = null;
    
    $id = $this->addPage($insert, $cid, 5);

    return $cid .'-' . $id;
  }

   /**
   * Sets a Short PLZ-Sperre
   */
  public function setShortPlzSperre() {

    $vku = $this->getVKU();
    $account = $vku->getAuthorObject();
    $verlag = $account ->getVerlag();

    // Only for Users with Verlag
    if(!$account -> getVerlag() ){
      return ;
    }

    $verlag_user = \LK\get_user($verlag);
    $days = $verlag_user -> getVerlagSetting('sperrung_vku_pdf', 0);

    // If not Days or Days == 0 (deaktiviert)
    if(!$days){
      return ;
    }

    $ausgaben =  $account -> getCurrentAusgaben();
    if(!$ausgaben){
      return ;
    }

    $dateplz = date('Y-m-d', strtotime(date("Y-m-d", time()) . " + ". $days ." day"));

    $nodes = $vku -> getKampagnen();
    foreach($nodes as $nid){
      $node = node_load($nid);
      $manager = new SperrenManager();
      $sperre = $manager ->createSperre($node -> nid, $account -> uid, $ausgaben, $dateplz);
      $plz_id = $sperre ->getId();

      $array = [
        'vku_id' => $this -> getId(),
        'uid' => $account -> uid,
        'plz_sperre_id' => $plz_id,
        'verlag_uid' => $verlag_user -> getUid(),
        'nid' => $node -> nid,
        'plz_sperre_until' => strtotime($dateplz),
      ];

      db_insert('lk_vku_plz_sperre')->fields($array)->execute();

      // Log also the Ausgaben, based on the Users Ausgaben
      foreach($ausgaben as $ausgabe){
        $array2 = [
          'plz_ausgabe_id' => $ausgabe,
          'vku_id' => $this -> getId(),
          'plz_sperre_id' => $plz_id,
        ];

        db_insert('lk_vku_plz_sperre_ausgaben')->fields($array2)->execute();
      }

      lk_note("kurzsperre", 'Kampagne ' . $node -> title ." [". $node -> nid ."] wurde für " . $account -> getTitle() . " bis zum " . date("d.m.Y H:i:s"). ' gesperrt');
    }
  }

  /**
   * Has PLZ-Sperre
   *
   * @return boolean|Array
   */
  public function hasPlzSperre(){
    
    $vku = $this->getVKU();
    $dbq = db_query("SELECT * FROM lk_vku_plz_sperre WHERE vku_id='". $vku -> getId() ."'");
    $all = $dbq -> fetchObject();

    if(!$all){
      return FALSE;
    }

    // Get Ausgaben from PLZ-Sperre
    $plz_sperre_id = $all -> plz_sperre_id;
    $until = $all -> plz_sperre_until;

    $ausgaben = array();
    $ausgaben_ids = array();

    // Get Ausgaben form special Table
    $dbq2 = db_query("SELECT DISTINCT plz_ausgabe_id FROM lk_vku_plz_sperre_ausgaben WHERE plz_sperre_id='". $plz_sperre_id ."'");
    foreach($dbq2 as $all){
       $ausgabe = \LK\get_ausgabe($all -> plz_ausgabe_id);
       if($ausgabe){
        $ausgaben_ids[] = $ausgabe -> getId();
        $ausgaben[] = $ausgabe ->getTitleFormatted();
       }
    }

    return [
      'until' => $until,
      'url' => $vku -> url(),
      'ausgaben' => $ausgaben,
      'ausgaben_ids' => $ausgaben_ids,
      'message' => '<div class="row clearfix"><div class="col-xs-1 text-center"><span class="glyphicon glyphicon-exclamation-sign"></span></div><div class="col-xs-10">Die Kampagnen sind bis zum ' . date("d.m.Y", $until) . " für <br />die Ausgaben ". implode(" ", $ausgaben) ." für Sie vorgemerkt.</div></div>"
    ];
  }
  
  /**
   * Removes a PLZ-Sperre
   */
  public function removePLZSperren(){

    $vku = $this->getVKU();
    $manager = new \LK\Kampagne\SperrenManager();

    $dbq = db_query("SELECT plz_sperre_id FROM lk_vku_plz_sperre WHERE vku_id='". $vku -> getId() ."'");
    foreach($dbq as $all){
      $manager ->removeSperre($all -> plz_sperre_id);
    }
  }

}
