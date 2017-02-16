<?php
namespace LK\Solr\Data;
use LK\Admin\Interfaces\DataManager;

/**
 * Description of SearchData
 *
 * @author Maikito
 */
class SearchData extends DataManager {

  function removeUserData(\LK\User $acccount){

    $count = $this->count('lk_search_history', ['uid' => $acccount->getUid()]);
    if($count){
      db_delete('lk_search_history')->condition('uid', $acccount ->getUid())->execute();
      $this->logNotice($count . ' Suchanfragen gelöscht.');
    }
  }

  function getUserDataCount(\LK\User $acccount){
    return ['Suchanfragen' => $this->count('lk_search_history', ['uid' => $acccount->getUid()])];
  }
}
