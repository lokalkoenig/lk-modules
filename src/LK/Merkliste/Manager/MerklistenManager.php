<?php

namespace LK\Merkliste\Manager;

/**
 * Description of Merkliste
 *
 * @author Maikito
 */
abstract class MerklistenManager {
  
  use \LK\Log\LogTrait;
  use \LK\Stats\Action;
  
  var $LOG_CATEGORY = "Merkliste";
  var $uid = 0;
  
  function setUserId($uid){
    $this->uid = $uid;
  }
  
  /**
   * 
   */
  abstract function performedUpdate();
  
  
  /**
   * Gets the User-Terms
   * 
   * @global type $user
   * @return array
   */
  function getTerms(){
    $simple = [];
    $dbq = db_query("SELECT * FROM lk_merklisten_terms WHERE uid='". $this -> uid ."' ORDER BY kampagnen DESC");
    while($all = $dbq -> fetchObject()){
      $simple[$all -> merklisten_id] = $all -> term_name;
    }
    
    return $simple;  
  }
  
  /**
   * Gets ML-Nodes from the User
   * 
   * @return array
   */
  function getUserKampagnen(){
    $nodes = [];
    $dbq = db_query("SELECT DISTINCT nid FROM lk_merklisten WHERE uid='". $this -> uid ."' ORDER BY created DESC");
    while($all = $dbq -> fetchObject()){
       $nodes[] = $all -> nid; 
    }
    return $nodes;
  }
  
  
  /**
   * 
   * @param type $tid
   * @return array|boolean
   */
  function loadMerklistenTerm($tid){
    $dbq = db_query("SELECT * FROM lk_merklisten_terms WHERE merklisten_id='". (int)$tid ."' AND uid='". $this -> uid ."'");
    $all = $dbq->fetchObject();
    
    if(!$all){
      return false;
    }
    
    return $all;
  }
  
  /**
   * Creates a new Merkliste
   * 
   * @global $user
   * @param string $name
   * @return int
   */
  protected function createMerkliste($name){
    $save = [];
    $save['uid'] = $this->uid;
    $save['term_name'] = $name;
    $save['kampagnen'] = 0;
    $save['created'] = $save['changed'] = time();
    
    $id = db_insert('lk_merklisten_terms')->fields($save)->execute();
    $this->setAction('merkliste-create', $id);
    
    return $id;  
  }
  
  /**
   * Loads a complete Merkliste
   * 
   * @param int $tid
   * @return boolean
   */
  function loadMerkliste($tid){
    
    $all = $this->loadMerklistenTerm($tid);
    
    if(!$all){
      return false;
    }
    
    $merkliste = $all;
    $merkliste->nodes = [];
    $dbq = db_query("SELECT nid FROM lk_merklisten WHERE term_id='". $all -> merklisten_id ."' ORDER BY created DESC");
    while($all = $dbq -> fetchObject()){
      $merkliste->nodes[] = $all -> nid;
    }
  
    return new Entity($this, $merkliste);
  }
  
  /**
   * Adds a new Term with a Kampagne
   * 
   * @param string $term
   * @param int $nid
   * @return string
   */
  function addNewTerm($term, $nid){
    
    if(empty($term)){
      return false;
    }
    
    $test = $this ->getSimilarTerms($term);
    
    if(!$test){
      $id = $this ->createMerkliste($term);
      $this->addKampagne($id, $nid);
      
      return $id;
    }
    
    $this->addKampagne($test, $nid);
    
    return $test;
  }
  
  /**
   * Gets all Terms for the Kampagne
   * 
   * @param type $nid
   * @return array
   */
  function getTermsFromKampagne($nid){
    
    $terms = [];
    
    $dbq = db_query('SELECT term_id FROM lk_merklisten WHERE uid=:uid AND nid=:nid', [':nid' => $nid, ':uid' => $this->uid]);
    while($all = $dbq -> fetchObject()){
      $terms[] = $all -> term_id;
    }
    
    return $terms;
  }
  
  /**
   * Gets similar Terms
   * 
   * @param type $term_name
   * @return boolean|int
   */
  private function getSimilarTerms($term_name){
    
    $sanitized = strtolower(trim($term_name));
    $other = $this -> getTerms();
    
    while(list($key, $val) = each($other)){
        $sanitized2 = strtolower(trim($val));
        
        if($sanitized2 === $sanitized){
          return $key;
        }
    }
  
    return false;
   }
  
  
  /**
   * Renames a Merkliste
   * 
   * @param int $tid
   * @param string $newname
   * @return int
   */
  function renameMerkliste($tid, $newname){
    
    if(empty($newname)){
      return false;
    }
    
    $test = $this ->getSimilarTerms($newname);
    if(!$test){
      db_query('UPDATE lk_merklisten_terms SET term_name=:name WHERE merklisten_id=:id', [':name' => trim($newname), ':id' => (int)$tid]);
      $this->performedUpdate();
    
      return $tid;
    }
    
    $merkliste = $this->loadMerkliste($tid);
    $nodes = $merkliste->getKampagnen();
    
    foreach($nodes as $nid){
      $this ->addKampagne($test, $nid);
    }
    
    $this ->removeMerkliste($tid);
    return $test; 
  }
  
  
  /**
   * Removes a Merkliste
   * 
   * @param int $tid
   * @return boolean
   */
  function removeMerkliste($tid){
    
    $merkliste = $this->loadMerkliste($tid);
    if(!$merkliste){
      return false;
    }
    
    $id = $merkliste->getId();
    db_query('DELETE FROM lk_merklisten WHERE term_id=:tid', [':tid' => $id]);
    db_query('DELETE FROM lk_merklisten_terms WHERE merklisten_id=:tid', [':tid' => $id]);
    $this->setAction('merkliste-remove', $id);
    
    $this->performedUpdate();
    return true;
  }
  
  /**
   * Removes a Node from a Merkliste
   * 
   * @param int $tid
   * @param int $nid
   * @return boolean
   */
  function removeKampagne($tid, $nid){
    
    $merkliste = $this->loadMerkliste($tid);
    if(!$merkliste){
      return false;
    }
    
    $kampagnen = $merkliste->getKampagnen();
    $id = $merkliste->getId();
    
    if(!in_array($nid, $kampagnen)){
      return false;
    }
    
    if(count($kampagnen) == 1){
      $this -> removeMerkliste($id);
      return true;
    }
    
   db_query('DELETE FROM lk_merklisten WHERE term_id=:tid AND nid=:nid', [':tid' => $id, ':nid' => $nid]);
   db_query('UPDATE lk_merklisten_terms SET kampagnen=kampagnen-1, changed=:changed WHERE merklisten_id=:mlid',[':mlid' => 'merklisten_id', ':changed' => time()]);
   
   $this->performedUpdate();
     
  return true;  
  }
  
  
  /**
   * Adds a Kampagne
   * 
   * @param int $tid
   * @param int $nid
   * @return boolean
   */
  function addKampagne($tid, $nid){
    $merkliste = $this->loadMerkliste($tid);
    if(!$merkliste){
      return false;
    }
    
    $kampagnen = $merkliste ->getKampagnen();
    
    if(in_array($nid, $kampagnen)){
      return false;
    }
    
    $insert = [
      'uid' => $this -> uid,
      'term_id' => $merkliste ->getId(),
      'created' => time(),
      'nid' => $nid
    ];
    
    
    $id = db_insert('lk_merklisten')->fields($insert)->execute();
    $this->setAction('merkliste-add-kampagne', $id);
    
    db_query('UPDATE lk_merklisten_terms SET kampagnen=kampagnen+1, changed=:changed WHERE merklisten_id=:mlid',[':mlid' => 'merklisten_id', ':changed' => time()]);
    
    $this->newKampagneAdded($merkliste, $nid);
    $this->performedUpdate();
  }
  
  
  /**
   * Is triggered once the Kampagne gets int the ML
   * 
   * @param \LK\Merkliste\Manager\Entity $merkliste
   * @param int $nid
   */
  function newKampagneAdded($merkliste, $nid){ }
  
  /**
   * Gets the User-Terms-Count
   * 
   * @param type $tid
   * @return int
   */
  function getTermsCount(){
    $dbq = db_query("SELECT count(*) as count FROM lk_merklisten_terms WHERE uid='". $this -> uid ."'");
    $all = $dbq -> fetchObject();
    
    return $all -> count;  
  }
}
