<?php
namespace LK\Merkliste;

/**
 * Description of AdminMerkliste
 *
 * @author Maikito
 */
class AdminMerkliste extends Manager {
  
  function loadMerklistenTerm($tid){
  global $user;  
    
    $dbq = db_query("SELECT * FROM lk_merklisten_terms WHERE merklisten_id='". $tid ."'");
    $all = $dbq->fetchObject();
    
    if(!$all){
      return false;
    }
    
    return $all;
  }
  
  
  function getMerklistenByUser($uid){
    
    $merklisten = [];
    $dbq = db_query("SELECT * FROM lk_merklisten_terms WHERE uid='". $uid ."'");
    while($all = $dbq -> fetchObject()){
      $merklisten[] = $this->loadMerkliste($all -> merklisten_id);
    }
    
  return $merklisten;  
  }
  
  
  function removeMerklisteByUser($uid){
    $merklisten = $this->getMerklistenByUser($uid);
    foreach($merklisten as $item){
      $this ->removeMerkliste($item -> merklisten_id);
    }
  }
}
