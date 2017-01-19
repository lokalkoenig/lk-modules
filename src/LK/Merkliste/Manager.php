<?php

namespace LK\Merkliste;

/**
 * Description of Merkliste
 *
 * @author Maikito
 */
class Manager {
  
  var $uid = 0;
  
  protected $SESSION_VARS = [
      'ML_TAGS',
      'ML_COUNT',
  ];
  
  function __construct($uid = 0) {
    
    
    $this -> uid = 0;
  }
  
  /**
   * Removes all Sessions for the ML
   */
  protected function removeSessions(){
    
    $vars = $this->SESSION_VARS;
    foreach ($vars as $key){
      if(isset($_SESSION[$key])){
        unset($_SESSION[$key]);
      }  
    }
  }

  /**
   * Gets the User-Terms
   * 
   * @global type $user
   * @return array
   */
  function getTerms(){
    if(isset($_SESSION['ML_TAGS'])){
      return $_SESSION['ML_TAGS'];
    }
  
    $array = [];
    $simple = [];
    $dbq = db_query("SELECT * FROM lk_merklisten_terms WHERE uid='". $this -> uid ."' ORDER BY kampagnen DESC");
    while($all = $dbq -> fetchObject()){
      $array[$all -> merklisten_id] = $all;
      $simple[$all -> merklisten_id] = $all -> term_name;
    }
    
    $_SESSION['ML_TAGS'] = $array;
    
    return $array;  
  }
  
  function loadMerklistenTerm($tid){
  global $user;  
    
    $dbq = db_query("SELECT * FROM lk_merklisten_terms WHERE merklisten_id='". $tid ."' AND uid='". $this -> uid ."'");
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
  global $user;
    
    $save = [];
    $save['uid'] = $user->uid;
    $save['term_name'] = $name;
    $save['kampagnen'] = 0;
    $save['created'] = $save['changed'] = time();
    
    $id = db_insert('lk_merklisten_terms')->fields($save)->execute();
  
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
  
    return $merkliste;  
  }
  
  /**
   * Adds a new Term with a Kampagne
   * 
   * @param string $term
   * @param int $nid
   * @return string
   */
  function addNewTerm($term, $nid){
    
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
   * Gets similar Terms
   * 
   * @param type $term_name
   * @return boolean|int
   */
  private function getSimilarTerms($term_name){
    
    $sanitized = strtolower(trim($term_name));
    $other = $this -> getTerms();
    
    while(list($key, $val) = each($other)){
        $sanitized2 = strtolower(trim($val -> term_name));
        
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
      db_query('UPDATE lk_merklisten_terms SET term_name=:name WHERE merklisten_id=:id', [':name' => trim($newname), ':id' => $tid]);
      return $tid;
    }
    
    $merkliste = $this->loadMerkliste($tid);
    foreach($merkliste['nodes'] as $nid){
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
    
    $all = $this->loadMerkliste($tid);
    if(!$all){
      return false;
    }
    
    $id = $all['merklisten_id'];
    db_query('DELETE FROM lk_merklisten WHERE term_id=:tid', [':tid' => $id]);
    db_query('DELETE FROM lk_merklisten_terms WHERE merklisten_id=:tid', [':tid' => $id]);
    
    $this->removeSessions();
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
    
    if(!in_array($nid, $merkliste['nodes'])){
      return false;
    }
    
    if(count($merkliste['nodes']) == 1){
      $this -> removeMerkliste($tid);
      return true;
    }
    
   db_query('DELETE FROM lk_merklisten WHERE term_id=:tid AND nid=:nid', [':tid' => $id, ':nid' => $nid]);
   db_query('UPDATE lk_merklisten_terms SET kampagnen=kampagnen-1, changed=:changed WHERE merklisten_id=:mlid',[':mlid' => 'merklisten_id', ':changed' => time()]);
   $this->removeSessions();
    
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
    
    if(in_array($nid, $merkliste['nodes'])){
      return false;
    }
    
    $insert = [
      'uid' => $merkliste['uid'],
      'term_id' => $merkliste['merklisten_id'],
      'created' => time(),
      'nid' => $nid
    ];
    
    db_insert('lk_merklisten')->fields($insert)->execute();
    db_query('UPDATE lk_merklisten_terms SET kampagnen=kampagnen+1, changed=:changed WHERE merklisten_id=:mlid',[':mlid' => 'merklisten_id', ':changed' => time()]);
  }
  
  /**
   * Gets the User-Terms-Count
   * 
   * @param type $tid
   * @return int
   */
  function getTermsCount(){
    
    // Build a cache
    if(!isset($_SESSION['ML_COUNT'])){
      $dbq = db_query("SELECT count(*) as count FROM lk_merklisten_terms WHERE uid='". $this -> uid ."'");
      $all = $dbq -> fetchObject();
      $_SESSION['ML_COUNT'] = $all -> count;
    }
  
    return $_SESSION['ML_COUNT'];
  }
}
