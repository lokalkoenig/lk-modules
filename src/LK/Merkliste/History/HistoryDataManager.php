<?php

namespace LK\Merkliste\History;
use LK\Admin\Interfaces\DataManager;
/**
 * Description of Manager
 *
 * @author Maikito
 */
class HistoryDataManager extends DataManager {

  /**
   * Removes the User-Data for the given User
   *
   * @param \LK\User $acccount
   */
  function removeUserData(\LK\User $acccount){
    $num_deleted = db_delete('lk_lastviewed')
      ->condition('uid', $acccount ->getUid())
      ->execute();
    
    if($num_deleted){
      $this->logNotice("Lösche " . $num_deleted . " Angesehene Kampagnen");
    }
  }
  
  function getUserDataCount(\LK\User $acccount){
    $dbq = db_query('SELECT count(*) as count FROM lk_lastviewed WHERE uid=:uid', [':uid' => $acccount ->getUid()]);
    $all = $dbq -> fetchObject();

    return ['Angesehene Kampagnen' => $all -> count];
  }
  
  function getKampagnenCount(\LK\Kampagne\Kampagne $kampagne){
    $dbq = db_query('SELECT count(*) as count FROM lk_lastviewed WHERE nid=:nid', [':nid' => $kampagne ->getNid()]);
    $all = $dbq -> fetchObject();

    return ['History' => $all -> count];
  }
  
  function removeKampagnenData(\LK\Kampagne\Kampagne $kampagne){
    $num_deleted = db_delete('lk_lastviewed')
      ->condition('nid', $kampagne ->getNid())
      ->execute();

    return ['History' => $num_deleted];
  }
}
