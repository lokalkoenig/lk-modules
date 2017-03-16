<?php

namespace LK\Stats\Tests;

/**
 * Description of TimeTrack
 *
 * @author Maikito
 */
class TimeTrack extends \LK\Tests\TestCase {
 
  function build(){
    $dbq = db_query("SELECT * FROM lk_actions_time ORDER BY id DESC LIMIT 10");
    foreach($dbq as $all):
      $this->printLine($all -> id, '<pre>' . print_r($all, true) . "</pre>");
    endforeach;
  }
}
