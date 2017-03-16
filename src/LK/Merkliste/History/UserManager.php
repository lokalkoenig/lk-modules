<?php
namespace LK\Merkliste\History;

/**
 * Description of UserManager
 *
 * @author Maikito
 */
class UserManager {

  use \LK\Log\LogTrait;
  use \LK\Stats\Action;

  var $account = null;

  /**
   * Sets the Account
   *
   * @param \LK\User $account
   */
  function setAccount(\LK\User $account){
    $this->account = $account;
  }

  /**
   * Unsetting the Session variable for the current user
   */
  private function performedAction(){
    unset($_SESSION['history_count']);
  }

  /**
   * Logs the node view
   *
   * @param \LK\Kampagne\Kampagne $kampagne
   */
  function logView(\LK\Kampagne\Kampagne $kampagne){
    $nid = $kampagne ->getNid();
    $uid = $this->account->getUid();

    // Track unique node view per day
    $time = strtotime(date('Y-m-d 00:00:01'));
    $dbq = db_query('SELECT count(*) as count FROM lk_lastviewed WHERE uid=:uid AND nid=:nid AND lastviewed_time>:last', [':uid' => $uid, ':nid' => $nid, ':last' => $time]);
    $res = $dbq -> fetchObject();
     
    if($res -> count == 0) {
      \LK\Stats::logUserAccessedKampagnen($uid);
    }
    
    db_query("DELETE FROM lk_lastviewed WHERE uid='". $uid ."' AND nid='". $nid ."'");
    db_query("INSERT INTO lk_lastviewed SET uid='". $uid ."', nid='". $nid ."', lastviewed_time='". time() ."'");
    $this->setAction('view-kampagne', $nid);

    // log stats


    $this->performedAction();
  }

  /**
   * Gets back the count
   *
   * @return int
   */
  function getCount(){

    if(!isset($_SESSION['history_count'])){
      $dbq = db_query('SELECT count(*) as count FROM lk_lastviewed WHERE uid=:uid', [':uid' => $this -> account ->getUid()]);
      $all = $dbq -> fetchObject();
      $_SESSION['history_count'] = $all->count;
    }

    return $_SESSION['history_count'];
  }

  /**
   * Deletes all the entries of the User
   *
   * @return int
   */
  function flush(){
    $num_deleted = db_delete('lk_lastviewed')
     ->condition('uid', $this -> acccount->getUid())
     ->execute();

    $this->logNotice('User leert Zuletzt angeschaut ('. $num_deleted .' Einträge)');
    $this->performedAction();
    
    return $num_deleted;
  }

  /**
   * Gets the Instance of the User-Manager
   *
   * @param \LK\User $account
   * @return \LK\Merkliste\History\UserManager
   */
  static public function getInstance(\LK\User $account){
    $manager = new \LK\Merkliste\History\UserManager();
    $manager->setAccount($account);
    return $manager;
  }
}
