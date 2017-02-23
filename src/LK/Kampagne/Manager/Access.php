<?php

namespace LK\Kampagne\Manager;


class Access {

  var $kampagne = NULL;
  var $account = NULL;

  var $moderator_days = 60;


  /**
   * Constructor
   *
   * @param \LK\Kampagne\Kampagne $kampagne
   * @param \LK\User $account
   */
  function __construct(\LK\Kampagne\Kampagne $kampagne, \LK\User $account){
    $this -> kampagne = $kampagne;
    $this -> account = $account;
  }
  
  /**
   * Has Access
   * 
   * @param \LK\Kampagne $kampagne
   * @return boolean
   */
  public static function has(\LK\Kampagne\Kampagne $kampagne){
    $account = \LK\current();

    if(!$account){
      return false;
    }

    $manager = self::getManager($kampagne, $account);
    return $manager ->hasPurchaseAccess();
  }

  /**
   * Gets the Access-Manager
   * 
   * @param \LK\Kampagne\Kampagne $kampagne
   * @param \LK\User $account
   * @return \LK\Kampagne\Manager\Access
   */
  public static function getManager(\LK\Kampagne\Kampagne $kampagne, \LK\User $account){
    $manager = new \LK\Kampagne\Manager\Access($kampagne, $account);

  return $manager;
  }

  /**
   * Creates a Manager from the given params
   *
   * @param int $nid
   * @param int $uid
   * 
   * @return \LK\Kampagne\Manager\Access
   */
  public static function createManager($nid, $uid = 0){

    if($uid) {$account = \LK\get_user($uid); } else {
      $account = \LK\current();}
  
    $node = node_load($nid);
    $kampagne = new \LK\Kampagne\Kampagne($node);

    return self::getManager($kampagne, $account);
  }



  /**
   * Gets back the Kampagne
   * 
   * @return \LK\Kampagne\Kampagne
   */
  function getKampagne(){
    return $this ->kampagne;
  }

  /**
   * Gets the Account
   *
   * @return \LK\User
   */
  function getAccount(){
    return $this->account;
  }

  /**
   * User can Purchase
   *
   * @return boolean
   */
  function hasPurchaseAccess(){

    $node = $this->getKampagne()->getNode();
    $nid = $node -> nid;

    if($node -> status === 0){
      return FALSE;
    }

    $ausgaben = $this->getAccount()->getCurrentAusgaben();
    if(!$ausgaben){
      return TRUE;
    }

    $dbq = db_query("SELECT count(*) as count FROM na_node_access_ausgaben_time WHERE nid='". $nid ."' AND aid IN (". implode(",", $ausgaben) .")");
    $all = $dbq -> fetchObject();

    if($all -> count > 0){
      return false;
    }

    return true;
  }

  /**
   * Gets back the Restriction-Information
   * @return array
   */
  function getPurchaseAccessInformation(){

    // User has Access
    if($this ->hasPurchaseAccess()){
      return ['access' => true];
    }

    $node = $this->getKampagne()->getNode();
    if($node -> status === 0){
      return ['access' => FALSE, "reason" => "Kampagene nicht mehr Online."];
    }
    
    $nid = $node->nid;
    $ausgaben = $this->getAccount()->getCurrentAusgaben();
    $dbq = db_query("select until as date_until from na_node_access_ausgaben_time WHERE nid='". $nid ."' AND aid IN (". implode(",", $ausgaben) .") ORDER BY until DESC LIMIT 1");
    $result = $dbq -> fetchObject();


    return ['access' => false,
            'time' => $result -> date_until,
            'reason' => "Die Kampagne ist ab dem ". date("d.m.Y", $result -> date_until) ." wieder verfügbar."];
  }


  /**
   * Gets Restriction details
   *
   * @return array
   */
  function getVerlagUsageDetails(){

    $nid = $this->getKampagne()->getNid();

    $where = array();
    $where[] = "n.data_entity_id='". $nid ."'";
    $where[] = "n.data_class='kampagne'";

    // set the days we want to have past information
    $days = $this -> moderator_days;

    $verlag = $this->getAccount()->getVerlagObject();
    if($verlag){
      $days = $verlag -> getVerlagSetting("sperrung_vku_hinweis", 10);
      $where[] = "v.verlag_uid='". $verlag ->getUid() ."'";
      $where[] = "v.uid != '". $this->getAccount()->getUid() ."'";
    }

    $time = time() - (60 * 60 * 24 * $days);
    $items = array();

    $dbq = db_query("SELECT v.vku_id, v.uid, v.vku_changed as date_added, v.vku_title
    FROM lk_vku v
      LEFT JOIN lk_vku_data n
      ON (n.vku_id=v.vku_id)
    WHERE
      v.vku_status IN ('active', 'created', 'downloaded', 'ready')
      AND ". implode(" AND ", $where) ." AND  v.vku_changed >= '". $time ."'
      ORDER BY v.vku_changed DESC");

    foreach($dbq as $all){
      $all -> ausgaben = $this->_getShortVKUSperre($all -> vku_id);
      $items[] = $all;
    }

    return $items;
  }

  /**
   *  Gets back the VKU's with Short-Term-Sperre
   *
   * @param int $vku_id VKU-ID
   * @return array
   */
  private function _getShortVKUSperre($vku_id){
    $bereiche = array();

    $dbq = db_query("SELECT DISTINCT plz_ausgabe_id FROM lk_vku_plz_sperre_ausgaben WHERE vku_id='". $vku_id ."'");
    foreach($dbq as $all){
       $ausgabe = \LK\get_ausgabe($all -> plz_ausgabe_id);
       $bereiche[] = $ausgabe ->getTitleFormatted();
    }

    return $bereiche;
  }

  /**
   * Gets back information about a Verlag related Sperre
   *
   * @return boolean|array
   */
  public function getVerlagPLZSperreInfo(){

    $verlag = $this->getAccount()->getVerlagObject();
    if(!$verlag) {
      return false;
    }

    $nid = $this->getKampagne()->getNid();
    $dbq = db_query("SELECT * FROM lk_vku_plz_sperre WHERE nid='". $nid  ."' AND verlag_uid='". $verlag->getUid() ."'");
    $all = $dbq -> fetchObject();

    if(!$all){
      return false;
    }

    $sperre = (array)$all;
    $sperre["is_user"] = false;

    if($this->getAccount()->getUid() == $all -> uid){
      $sperre["is_user"] = true;
    }

    return $sperre;
  }

  /**
   * Gets the current Lizenz of available
   *
   * @return int Lizenz-ID
   */
  function getCurrentUserLizenz(){
    $nid = $this->getKampagne()->getNid();
    $uid = $this->getAccount()->getUid();

    $dbq = db_query("SELECT l.* FROM lk_vku vku, lk_vku_lizenzen l WHERE l.nid=:nid AND l.vku_id=vku.vku_id AND vku.vku_status='purchased' AND vku.uid=:uid", [':uid' => $uid, ':nid' => $nid]);
    $all = $dbq -> fetchObject();
    if(!$all){
      return false;
    }
    else {
      return $all -> id;
    }
  }

  /**
   * Gets the VKU-Usage
   *
   * @return int
   */
  public function getVKUUsageCount(){

    $where = array(1);
    $days = $this -> moderator_days;

    $verlag = $this->getAccount()->getVerlagObject();
    if($verlag){
      $days = $verlag -> getVerlagSetting("sperrung_vku_hinweis", 10);
      $where[] = "v.verlag_uid='". $verlag ->getUid() ."'";
      $where[] = "v.uid != '". $this->getAccount()->getUid() ."'";
    }

    $time = time() - (60*60*24*$days);
    $where[] = "v.vku_changed >= '". $time ."'";
    $where[] = "n.data_entity_id='". $this->getKampagne()->getNid()  ."'";
    $where[] = "n.data_class='kampagne'";
    $where[] = "v.vku_status IN ('". implode("','", ["active", "created", "downloaded", "ready"])  ."')";

    $dbq = db_query("SELECT
        count(*) as count
        FROM lk_vku_data n, lk_vku v
        WHERE
            n.vku_id=v.vku_id
          AND " . implode(" AND ", $where));

    $all = $dbq -> fetchObject();
    return $all -> count;
  }

  /**
   * Gets Usage-Stats for the Node
   *
   * @return boolean|Array
   */
  function getUsageAusgabePLZ(){

    $nid = $this->getKampagne()->getNid();
    $verlag = $this->getAccount()->getVerlagObject();
    if(!$verlag){
      return false;
    }

    $inverlag = [];
    $dbq = db_query("SELECT ausgaben_id FROM na_node_access_ausgaben WHERE verlag_uid='". $verlag ->getUid() ."' AND nid='". $nid ."'");
    foreach($dbq as $all){
        $ausgabe = \LK\get_ausgabe($all -> ausgaben_id);
        if($ausgabe){
            $inverlag[] = $ausgabe -> getShortTitle();
        }
    }

    $outverlag = [];
    $dbq2 = db_query("SELECT * FROM na_node_access_ausgaben WHERE verlag_uid != '". $verlag ->getUid() ."' AND nid='". $nid ."'");
    foreach($dbq2 as $all){
      $outverlag[] = $all -> plz_gebiet_aggregated;
    }

    if(!$inverlag && !$outverlag){
      return false;
    }
 
  return ['count' => (count($inverlag) + count($outverlag)), 'in' => $inverlag, "out" => $outverlag];
  }


  /**
   * Gets back public information about the
   * Kampagne
   *
   * @return html
   */
  public function getUsageInfoVerlag(){

    $sperre = $this->getVerlagPLZSperreInfo();
    $output = array();
    $pa = $this->hasPurchaseAccess();
    $usecount = $this->getVKUUsageCount();
    $account = $this->getAccount();

    // Show Lizenzen
    if(!$pa && !$sperre){
       $test = $this->getUsageAusgabePLZ();
       if($test){
        $output[] = \theme("lk_vku_lizenz_usage", $test + ['class' => 'well well-white', 'account' => $account]);
       }
    }
    elseif($usecount) {
      $details = $this->getVerlagUsageDetails();
      $output[] = theme("lk_vku_usage", array('class' => 'clearfix', "account" => $account, "entries" => $details));
    }


    if(!$output){
      $output[] = 'Keine Informationen verfügbar';
    }

    return implode("", $output);
  }
}

