<?php

namespace LK\Stats\Cron;

/**
 * Description of UserStats
 *
 * @author Maikito
 */
class UserStats {

  public function __construct() { }

  private function logSummary($uid, $uid_key, $time_key, $log_key, $value) {
    if($value) {
      \LK\Stats::logSummary($time_key, $uid_key, $uid, $log_key, $value);
    }
 }

  public static function executeCron(){

    $month = date('Y-m');
    $kw = date('Y') . "-KW-". date('W');

    $stats = new UserStats();
    $stats->TeamStats('team-weekly', $kw);
    $stats->TeamStats('team', $month);

    $stats->VerlagStats('verlag-weekly', $kw);
    $stats->VerlagStats('verlag', $month);
    
    $stats->LKStats('lk', $month);
    $stats->LKStats('lk-weekly', $kw);
  }

  /**
   * Logs the advanced Team-Stats
   *
   * @param type $log_key
   * @param type $time_key
   */
  public function TeamStats($log_key, $time_key){
    $dbq = db_query("SELECT DISTINCT user_stats_team_id FROM lk_verlag_stats WHERE user_stats_team_id != 0 AND stats_date='". $time_key ."'");
    foreach($dbq as $all){
      $team_id = $all -> user_stats_team_id;
      $team = \LK\get_team($team_id);

      $dbq3 = db_query("SELECT count(*) as count FROM lk_verlag_stats WHERE user_stats_team_id='". $team_id ."' AND stats_date='". $time_key ."'");
      $result3 = $dbq3 -> fetchObject();
      $this->logSummary($team_id, $log_key, $time_key, 'active_users', (int)$result3 -> count);
      $this->logSummary($team_id, $log_key, $time_key, 'activated_users', $team->getUserActive_count());
    }
  }

  /**
   * Logs Verlag Stats
   *
   * @param type $log_key
   * @param type $time_key
   */
  public function VerlagStats($log_key, $time_key) {
    $query = 'SELECT DISTINCT(ur.uid) FROM {users_roles} AS ur WHERE ur.rid IN (:rids)';
    $result = db_query($query, array(':rids' => array(5)));
    $uids = $result->fetchCol();
    foreach($uids as $uid):
      $account = user_load($uid);

      if(!$account -> status):
          continue;
      endif;

      $verlag = \LK\get_user($account->uid);

      $dbq3 = db_query("SELECT count(*) as count FROM lk_verlag_stats WHERE user_stats_verlag_uid='". $uid ."' AND stats_date='". $time_key ."' AND stats_user_type='user'");
      $result3 = $dbq3 -> fetchObject();
      $this->logSummary($uid, $log_key, $time_key, 'active_users', (int)$result3 -> count);
      $this->logSummary($uid, $log_key, $time_key, 'activated_users', $verlag -> getPeopleCount() + 1);
    endforeach;
  }


  /**
   * Logs LK Overall stats
   *
   * @param type $log_key
   * @param type $time_key
   */
  function LKStats($log_key, $time_key) {

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
      $dbq = db_query("SELECT sum(". $item .") as count FROM lk_verlag_stats WHERE stats_bundle_id !='". LK_TEST_VERLAG_UID ."' AND stats_user_type='verlag' AND stats_date='". $time_key ."'");
      $result = $dbq -> fetchObject();

      $this->logSummary(0, $log_key, $time_key, $item, $result -> count);
    endforeach;
  }
  
}
