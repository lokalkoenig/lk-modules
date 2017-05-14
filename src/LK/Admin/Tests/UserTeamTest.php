<?php

namespace LK\Admin\Tests;
use LK\Tests\TestCase;

/**
 * Description of UserTeamTest
 *
 * @author Maikito
 */
class UserTeamTest extends TestCase {
  
  
  function build() {
    $dbq = db_query('SELECT id FROM eck_team ORDER BY id ASC');
    while($all = $dbq->fetchObject()) {
      $team = \LK\get_team($all->id);

      if(!$team->getLeiter()) {
        $this -> printLine($team->getTitle(), "Team hat keinen Leiter");
        continue;
      }
      
      $leiter = \LK\get_user($team->getLeiter());
      $test_team = $leiter->getTeam();
      if($test_team != $team->getId()) {
        $this -> printLine($leiter, "User hat ein falsches Team: " . $team->getTitle());
      }

      
    }
  }
}
