<?php
namespace LK\Merkliste;

/**
 * Description of AdminMerkliste
 *
 * @author Maikito
 */
class AdminMerkliste extends Manager\MerklistenManager {
  
  /**
   * Constructor
   * 
   * @param type $uid
   */
  function __construct($uid = 0) {
    parent::__construct($uid);
  }
  
  /**
   * Gets the ML-Node-Count
   * 
   * @param int $nid
   */
  function getGeneralKampagnenCount($nid){
    
    $dbq = db_query('SELECT count(*) as count FROM lk_merklisten WHERE nid=:nid', [':nid' => $nid]);
    $all = $dbq -> fetchObject();
    
    return $all -> count;
  }
 
  /**
   * Do nothing
   */
  function performedUpdate() { }
  
  /**
   * Removes all Merklisten from a User-Account
   */
  function removeMerklisten(){
    $merklisten = $this->getTerms();
    $this->logNotice("Lösche alle Merklisten von User [". $this->uid ."]");
    
    foreach($merklisten as $item){
      $this ->removeMerkliste($item -> merklisten_id);
    }
  }
}
