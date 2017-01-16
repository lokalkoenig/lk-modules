<?php


namespace LK\Kampagne;

/**
 * Description of Lizenz
 *
 * @author Maikito
 */
class LizenzManager {
 
  use \LK\Log\LogTrait;
  use \LK\Stats\Action;
  
  var $LOG_CATEGORY = "Lizenzen";
  const ADMIN_URL = "logbuch/verlag-lizenzen";
  
  /**
   * Creates a new Lizenz for the Node
   * 
   * @param Int $nid
   * @param \VKUCreator $vku
   * @return \LK\Lizenz
   */
  public function create($nid, \VKUCreator $vku){
    
    $vku_id = $vku -> getId();
    $vku_author = $vku -> getAuthor();
    $obj = \LK\get_user($vku_author);
    $ausgaben = $obj ->getCurrentAusgaben();
            
    $vid = $obj ->getVerlag();
    $team_id = $obj -> getTeam();
   
    $node = node_load($nid);
    $array = array();
    $array["vku_id"] = $vku_id;
    $array["nid"] = $nid;
    $array["lizenz_date"] = time();
    $array["lizenz_uid"] = $vku_author;
    $array["node_uid"] = $node -> uid;
          
    $array["lizenz_verlag_uid"] = $vid;
    $array["lizenz_paket"] =  $node->field_kamp_preisnivau['und'][0]['tid'];
    $array["lizenz_team"] =  $team_id;
    $array["lizenz_until"] = time() + (60 * 60 * 24 * 30);
    
    $lizenz_id = db_insert('lk_vku_lizenzen')->fields($array)->execute();
    
    \LK\Stats::countPurchasedVKU($vku);
    $this->setAction('lizenz', $lizenz_id);
            
    $days = self::getLizenzTime($obj);
    $dateplz = date('Y-m-d',strtotime(date("Y-m-d", time()) . " + ". $days ." day"));
    
    $lizenz = new \LK\Lizenz($lizenz_id);
    $lizenz ->createSperre($ausgaben, $dateplz);
    
    $msg = 'Lizenz erworben';
    $generic = $vku -> get("vku_generic");
    
    if($generic){
   	$msg = 'Lizenz direkt erworben';   		
    }
    
    $this ->logVerlag($msg, [
        'nid' => $nid,
        'lizenz' => $lizenz,
        'vku' => $vku,
    ]);
   
  return $lizenz;  
  }
  
  function getOverviewURL(){
    return self::ADMIN_URL;
  }
  
  /**
   * Get the maximum time as Lizenz will be given
   * 
   * @param \LK\User $account
   * @return int Days
   */
  public static function getLizenzTime(\LK\User $account){
    
    $verlag = $account ->getVerlagObject();
    if(!$verlag) {
        return 0;
    }
      
    $days = 365;
    
    $test = $verlag -> getVerlagSetting('sperrung_vku');
    if($test){
       return $test; 
    }
    
  return $days;  
  }
  
  
  /**
   * Loads a Lizenz
   * 
   * @param type $id
   * @return boolean|\LK\Lizenz
   */
  function loadLizenz($id){
    $lizenz = new \LK\Lizenz($id);
    
    if($lizenz ->is()){
      return $lizenz;
    }
  
  return false;  
  }
  
  /**
   * Gets the Lizenz from the Signature
   * 
   * @param type $sid
   * @return boolean|\LK\Lizenz
   */
  public function getLizenzFromSignature($sid){
    
    $explode = explode("-", $sid);
  
    $date = (int)$explode[0];
    $nid = (int)$explode[1];
    $uid = (int)$explode[2];
    $paket = (int)$explode[3];
  
    $dbq = db_query("SELECT * FROM 
      lk_vku_lizenzen 
      WHERE lizenz_date='". $date  ."' AND nid='". $nid ."' AND lizenz_uid='". $uid ."' AND lizenz_paket='". $paket ."'"); 

    $all = $dbq -> fetchObject();
    if(!$all){
      return false;
    }
  
    
    $lizenz = $this->loadLizenz($all -> id);
    
  return $lizenz;  
  }
  
  
  
  
}
