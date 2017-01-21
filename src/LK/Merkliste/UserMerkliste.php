<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Merkliste;

/**
 * Description of UserMerkliste
 *
 * @author Maikito
 */
class UserMerkliste extends \LK\Merkliste\Manager\MerklistenManager {
 
  CONST URL = 'merkliste';
  protected $SESSION_VARS = [
      'ML_TAGS',
      'ML_COUNT',
      'ML_NODES',
  ];
  
  /**
   * Constructor
   * Sets the User-ID
   * 
   * @global type $user
   */
  function __construct() {
    global $user;
    $this->setUserId($user -> uid);
  }
  
  /**
   * Gets the URL
   * 
   * @return string
   */
  function getUrl($tid = NULL){
    
    if($tid){
      return \LK\Merkliste\UserMerkliste::URL . '/' . $tid;
    }
    
    return \LK\Merkliste\UserMerkliste::URL;
  }
  
  /**
   * Gets the Users Kampagnen
   * 
   * @return array
   */
  public static function getKampagnen(){
    $manager = new \LK\Merkliste\UserMerkliste();
    return $manager->getUserKampagnen();
  }

  /**
   * Gets back the Number of stored ML
   * 
   * @return int
   */
  public static function getCount(){
    $manager = new \LK\Merkliste\UserMerkliste();
    return $manager->getTermsCount();
  }
  
  /**
   * Gets back the Merklisten
   * 
   * @return array
   */
  public static function getMerklisten(){
    $manager = new \LK\Merkliste\UserMerkliste();
    return $manager->getTerms();
  }

  /**
   * On Performed Updates
   */
  function performedUpdate(){
    $this->removeSessions();
  }
  
 /**
   * Is triggered once the Kampagne gets int the ML
   * 
   * @param \LK\Merkliste\Manager\Entity $merkliste
   * @param int $nid
   */
  function newKampagneAdded($merkliste, $nid){
    $this->logVerlag("Kampagne zur Merkliste " . $merkliste ->getName() . " hinzugefügt.", ['nid' => $nid]);
  }
  
  /**
   * Creates a new Merkliste
   * 
   * @param string $name
   * @return int
   */
  protected function createMerkliste($name) {
    $this->logVerlag("Neue Merkliste erstellt: " . $name);    
    \LK\Stats::logUserMerklisteAdded($this -> uid);
    return parent::createMerkliste($name);
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
  
  function getUserKampagnen(){
    if(!isset($_SESSION['ML_NODES'])){
      $_SESSION['ML_NODES'] = parent::getUserKampagnen();
    }
    
    return $_SESSION['ML_NODES'];
    
  }
  
  function getTermsCount() {
    
    if(!isset($_SESSION['ML_COUNT'])){
      $_SESSION['ML_COUNT'] = parent::getTermsCount();
    }
    
    return $_SESSION['ML_COUNT'];
  }
  
  
  function getTerms() {
    if(!isset($_SESSION['ML_TAGS'])){
      $_SESSION['ML_TAGS'] = parent::getTerms();
    }
  return $_SESSION['ML_TAGS'];   
  }
}
