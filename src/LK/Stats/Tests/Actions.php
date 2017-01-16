<?php

namespace LK\Stats\Tests;  

/**
 * Returns just from the Database
 *
 * @author Maikito
 */
class Actions extends \LK\Tests\TestCase {
  
  function build(){
    
    $dbq = db_query("SELECT * FROM lk_actions ORDER BY id DESC LIMIT 10");
    foreach($dbq as $all):
      
    $this->printLine($all -> id, '<pre>' . print_r($all, true) . "</pre>");
    endforeach;
  }
}
