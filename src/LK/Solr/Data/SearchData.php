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
    db_delete('lk_search_history')->condition('uid', $acccount ->getUid())->execute();
  }

  function getUserDataCount(\LK\User $acccount){
    return ['Suchen' => $this->count('lk_search_history', ['uid' => $acccount->getUid()])];
  }
}
