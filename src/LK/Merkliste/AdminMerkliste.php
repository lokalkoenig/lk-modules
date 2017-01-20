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
  function __construct($uid) {
    parent::__construct($uid);
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
    
    foreach($merklisten as $item){
      $this ->removeMerkliste($item -> merklisten_id);
    }
  }
}
