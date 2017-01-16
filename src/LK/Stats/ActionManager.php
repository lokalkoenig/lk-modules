<?php

namespace LK\Stats;

/**
 * Description of ActionManager
 *
 * @author Maikito
 */
class ActionManager {
  use LK\Stats\Action;
  
  function __construct($action, $id = 0) {
    $this->setAction($action, $id);
  }  
}
