<?php

namespace LK;

class Stats {
    
  public static function logUserMerklisteAdded($user_id){
    self::log('user', $user_id, 'merklisten');
  }
    
  public static function logUserAccessedKampagnen($user_id){
    self::log('user', $user_id, 'accessed_kampagnen');
  }

  public static function logUserSearches($user_id){
    self::log('user', $user_id, 'searches');
  }

  private static function __logVku(\VKUCreator $vku, $key){
    $vku_safe = new \VKUCreator($vku ->getId());
    $account = \LK\get_user($vku_safe ->getAuthor());

    self::log("user", $account ->getUid(), $key);
  }

  static function countVKU(\VKUCreator $vku){
    self::__logVku($vku, "created_vku");
  }
    
  static function countGeneratedVKU(\VKUCreator $vku){
    self::__logVku($vku, "generated_vku");
  }
    
  static function countPurchasedVKU(\VKUCreator $vku){
    self::__logVku($vku, "purchased_vku");
  }
   
  private static function __get_id($bundle, $user_id, $month_select = null){

    $month = $month_select;
    $where = array("stats_user_type='" .$bundle ."'");
    $where[] = "stats_bundle_id='". $user_id ."'";
    $where[] = "stats_date='". $month ."'";

    $dbq = db_query("SELECT id FROM lk_verlag_stats "
            . "WHERE " . implode(" AND ", $where));
    $test = $dbq -> fetchObject();

    if(!$test){
      // create record
      $verlag_uid = 0;
      $team_id = 0;

      if($bundle === 'user' || $bundle === 'user-weekly'){
        $account = \LK\get_user($user_id);
        if($account):
          $team_id = (int)$account ->getTeam();
          $verlag_uid = (int)$account ->getVerlag();
        endif;
      }

      if($bundle == 'team' || $bundle === 'team-weekly'){
        $team = \LK\get_team($user_id);

        if($team):
          $verlag_uid = (int)$team ->getVerlag();
        endif;
      }

      $fields = ["user_stats_verlag_uid" => $verlag_uid,
        "user_stats_team_id" => $team_id,
        "stats_user_type" => $bundle,
        "stats_bundle_id" => $user_id,
        "stats_date" => $month];

      $id = db_insert('lk_verlag_stats')->fields($fields)->execute();
    }
    else {
      $id = $test -> id;
    } 

    return $id;
  }
    
  public static function getLastEntry($bundle, $user_id, $month = null){

    if(!$month) {
      $month = date("Y-m");
    }

    $id = self::__get_id($bundle, $user_id, $month);

    if($id){
      $dbq = db_query("SELECT * FROM lk_verlag_stats WHERE id='". $id ."'");

      return $dbq -> fetchObject();
    }

    return false;    
  }
    
  public static function logOverall($key, $value){
    self::logSummary("lk", 0, $key, $value);
  }

  public static function logSummary($date, $bundle, $user_id, $key, $value) {
    $id = self::__get_id($bundle, $user_id, $date);
    db_query("UPDATE lk_verlag_stats SET " . $key . "='". $value ."' WHERE id='". $id ."'");
  }
  
  private static function log($bundle, $user_id, $key){

    $id = self::__get_id($bundle, $user_id, date("Y-m"));
    $id2 = self::__get_id($bundle . '-weekly', $user_id, date('Y') . "-KW-". date('W'));

    db_query("UPDATE lk_verlag_stats SET " . $key . "=" . $key . "+1 WHERE id='". $id ."'");
    db_query("UPDATE lk_verlag_stats SET " . $key . "=" . $key . "+1 WHERE id='". $id2 ."'");

    if($bundle === 'user') {
      $account = \LK\get_user($user_id);
      $verlag = $account->getVerlag();
      $team = $account->getTeam();

      if($verlag) {
        self::log('verlag', $verlag, $key);
      }

      if($team) {
        self::log('team', $team, $key);
      }
    }
  }
}
