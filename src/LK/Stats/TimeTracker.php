<?php

namespace LK\Stats;

/**
 * Description of TimeTracker
 *
 * @author Maikito
 */
class TimeTracker {
  use Action;

  const TIME_BETWEEN = 3600;
  private $account;

  /**
   * Tracks the time
   *
   * @param \LK\User $account
   */
  public function track(\LK\User $account){
    $this->account = $account;

    if(!isset($_SESSION['lk_timetrack_time']) || !isset($_SESSION['lk_timetrack_id'])) {
      $this->createNewTrack();
    }
    elseif(time() > ($_SESSION['lk_timetrack_last'] + self::TIME_BETWEEN)) {
      $this->createNewTrack();
    }
    else {
      $this->renewTimeTrack();
    }
  }

  private function renewTimeTrack() {
    $spent = time() - $_SESSION['lk_timetrack_time'];
    $_SESSION['lk_time_track_last'] = time();
    db_query('UPDATE lk_actions_time SET action_hits=action_hits+1, action_time=:time  WHERE id=:id', [':time' => $spent, ':id' => $_SESSION['lk_timetrack_id']]);
  }

  /**
   * Creates a new Tracking spot
   */
  private function createNewTrack(){
    $_SESSION['lk_timetrack_last'] = $_SESSION['lk_timetrack_time'] = time();
    $account = $this->getAccount();

    $insert = [
      'uid' => $account ->getUid(),
      'verlag_uid' => $account->getVerlag(),
      'team_uid' => $account->getTeam(),
      'action_date' => date('Y-m-d'),
      'action_hits' => 1,
      'action_time' => 1,
    ];
  
    $id = db_insert('lk_actions_time')
    ->fields($insert)
    ->execute();

    $_SESSION['lk_timetrack_id'] = $id;
    $this->setAction('session', $id);
  }

  /**
   * Gets the Account
   *
   * @return \LK\User
   */
  private function getAccount(){
    return $this->account;
  }

}
