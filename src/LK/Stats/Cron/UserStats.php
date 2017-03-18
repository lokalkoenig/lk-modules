<?php

namespace LK\Stats\Cron;

/**
 * Description of UserStats
 *
 * @author Maikito
 */
class UserStats {

  public function __construct() { }

  /**
   * Logs the summary to the Stats-Module
   *
   * @param int $uid
   * @param string $uid_key
   * @param string $time_key
   * @param string $log_key
   * @param int $value
   */
  private function logSummary($uid, $uid_key, $time_key, $log_key, $value) {
    if($value) {
      \LK\Stats::logSummary($time_key, $uid_key, $uid, $log_key, $value);
    }
  }

  /**
   * Excecutes the Cronjob
   */
  public static function executeCron(){
    $month = date('Y-m');
    $kw = date('Y') . "-KW-". date('W');

    $stats = new UserStats();
    $stats->UserSessionsLog(date('Y-m-01'), date('Y-m-t'), $month, 'user');
    
    $date = new \DateTime();
    $date->modify('this week');
    $date_from_week = $date->format('Y-m-d');

    $date->modify('this week +6 days');
    $date_from_week_end = $date->format('Y-m-d');
    $stats->UserSessionsLog($date_from_week, $date_from_week_end, $kw, 'user-weekly');

    $stats->TeamStats('team-weekly', $kw, 'user-weekly');
    $stats->TeamStats('team', $month, 'user');

    $stats->VerlagStats('verlag-weekly', $kw, 'user-weekly');
    $stats->VerlagStats('verlag', $month, 'user');
    
    $stats->LKStats('lk', $month, 'verlag');
    $stats->LKStats('lk-weekly', $kw, 'verlag-weekly');
  }

  /**
   * Converts the User-Sessions to the Stats - Overall
   *
   * @param string $date_from
   * @param string $date_to
   * @param string $time_key
   */
  public function UserSessionsLog($date_from, $date_to, $time_key, $user_type_key){

    $uids = [];
    $dbq = db_query("SELECT DISTINCT uid FROM lk_actions_time WHERE action_date BETWEEN '". $date_from ."' AND '". $date_to ."'");
    while ($all = $dbq->fetchObject()) {
      $uids[] = $all->uid;
    }

    foreach($uids as $uid) {
      $dbq = db_query("SELECT count(*) as count, sum(action_hits) as count_hits, sum(action_time) as count_time FROM lk_actions_time WHERE action_date BETWEEN '". $date_from ."' AND '". $date_to ."' AND uid='". $uid ."'");
      $result = $dbq->fetchObject();

      $sessions = $result->count;
      $hits = $result->count_hits;
      $time = $result->count_time;

      $this->logSummary($uid, $user_type_key, $time_key, 'page_sessions', $sessions);
      $this->logSummary($uid, $user_type_key, $time_key, 'page_hits', $hits);
      $this->logSummary($uid, $user_type_key, $time_key, 'page_time', $time);
    }
  }

  /**
   * Logs the advanced Team-Stats
   *
   * @param type $log_key
   * @param type $time_key
   */
  public function TeamStats($log_key, $time_key, $user_type_key){
    $dbq = db_query("SELECT DISTINCT user_stats_team_id FROM lk_verlag_stats WHERE user_stats_team_id != 0 AND stats_date='". $time_key ."' AND stats_user_type='". $user_type_key."'");
    foreach($dbq as $all){
      $team_id = $all -> user_stats_team_id;
      $team = \LK\get_team($team_id);

      $dbq3 = db_query("SELECT count(*) as count FROM lk_verlag_stats WHERE user_stats_team_id='". $team_id ."' AND stats_date='". $time_key ."' AND stats_user_type='". $user_type_key."'");
      $result3 = $dbq3 -> fetchObject();
      $this->logSummary($team_id, $log_key, $time_key, 'active_users', (int)$result3 -> count);
      $this->logSummary($team_id, $log_key, $time_key, 'activated_users', $team->getUserActive_count());


      $dbq4 = db_query("SELECT sum(page_sessions) as page_sessions, sum(page_hits) as page_hits, sum(page_time) as page_time  FROM lk_verlag_stats WHERE user_stats_team_id='". $team_id ."' AND stats_date='". $time_key ."' AND stats_user_type='". $user_type_key."'");
      $result4 = $dbq4 -> fetchObject();

      $this->logSummary($team_id, $log_key, $time_key, 'page_sessions', (int)$result4 -> page_sessions);
      $this->logSummary($team_id, $log_key, $time_key, 'page_hits', (int)$result4 -> page_hits);
      $this->logSummary($team_id, $log_key, $time_key, 'page_time', (int)$result4 -> page_time);
    }
  }

  /**
   * Logs Verlag Stats
   *
   * @param type $log_key
   * @param type $time_key
   */
  public function VerlagStats($log_key, $time_key, $user_type_key) {
    $query = 'SELECT DISTINCT(ur.uid) FROM {users_roles} AS ur WHERE ur.rid IN (:rids)';
    $result = db_query($query, array(':rids' => array(5)));
    $uids = $result->fetchCol();
    foreach($uids as $uid):
      $account = user_load($uid);

      if(!$account -> status):
          continue;
      endif;

      $verlag = \LK\get_user($account->uid);

      $dbq3 = db_query("SELECT count(*) as count FROM lk_verlag_stats WHERE user_stats_verlag_uid='". $uid ."' AND stats_date='". $time_key ."' AND stats_user_type='". $user_type_key ."'");
      $result3 = $dbq3 -> fetchObject();
      $this->logSummary($uid, $log_key, $time_key, 'active_users', (int)$result3 -> count);
      $this->logSummary($uid, $log_key, $time_key, 'activated_users', $verlag -> getPeopleCount() + 1);

      $dbq4 = db_query("SELECT sum(page_sessions) as page_sessions, sum(page_hits) as page_hits, sum(page_time) as page_time  FROM lk_verlag_stats WHERE user_stats_verlag_uid='". $uid ."' AND stats_date='". $time_key ."' AND stats_user_type='". $user_type_key."'");
      $result4 = $dbq4 -> fetchObject();

      $this->logSummary($uid, $log_key, $time_key, 'page_sessions', (int)$result4 -> page_sessions);
      $this->logSummary($uid, $log_key, $time_key, 'page_hits', (int)$result4 -> page_hits);
      $this->logSummary($uid, $log_key, $time_key, 'page_time', (int)$result4 -> page_time);

    endforeach;
  }


  /**
   * Logs LK Overall Stats
   *
   * @param string $log_key
   * @param string $time_key
   */
  function LKStats($log_key, $time_key, $user_type_key) {

    $measurements = [
      'active_users',
      'created_vku',
      "generated_vku",
      "purchased_vku",
      "merklisten",
      "searches",
      "accessed_kampagnen",
      "activated_users",
      'page_sessions',
      'page_hits',
      'page_time',
    ];

    foreach($measurements as $item):
      $dbq = db_query("SELECT sum(". $item .") as count FROM lk_verlag_stats WHERE stats_bundle_id !='". LK_TEST_VERLAG_UID ."' AND stats_user_type='". $user_type_key ."' AND stats_date='". $time_key ."'");
      $result = $dbq -> fetchObject();

      $this->logSummary(0, $log_key, $time_key, $item, $result -> count);
    endforeach;
  }
  
}
