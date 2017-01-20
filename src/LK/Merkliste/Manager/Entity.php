<?php

namespace LK\Merkliste\Manager;

/**
 * Description of Entity
 *
 * @author Maikito
 */
class Entity {
  
  var $data = [];
  var $manager = null;
  
  function __construct(\LK\Merkliste\Manager\MerklistenManager $manager, $data) {
    $this -> data = $data; 
    $this -> manager = $manager;
  }
  
  function getUrl(){
    return url(\LK\Merkliste\UserMerkliste::URL . "/" . $this->getId());
  }
  
  function getData(){
    return $this->data;
  }
  
  function getKampagnenCount(){
    return count($this->getKampagnen());
  }
  
  function getId(){
    return $this-> data -> merklisten_id;
  }
  
  function getKampagnen(){
    return $this -> data -> nodes;
  }
  
  function getName(){
    return $this -> data -> term_name;
  }
}
