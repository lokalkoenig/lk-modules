<?php

namespace LK\Merkliste\Manager;
use LK\Admin\Interfaces\DataManager;
use LK\Merkliste\AdminMerkliste;

/**
 * Description of UserManager
 *
 * @author Maikito
 */
class UserManager implements DataManager {
  
  function removeUserData(\LK\User $acccount){
    
    $merkliste = new AdminMerkliste($acccount ->getUid());
    $terms = $merkliste ->getTerms();
    
    while(list($key, $val) = each($terms)){
      $merkliste ->removeMerkliste($key);
    }
    
    return count($merkliste);  
  }
  
  function getUserDataCount(\LK\User $acccount){
    $merkliste = new AdminMerkliste($acccount ->getUid());
    return $merkliste ->getTermsCount();
  }
  
  function getKampagnenCount(\LK\Kampagne\Kampagne $kampagne){



    $dbq = db_query('SELECT count(*) as count FROM lk_lastviewed WHERE nid=:nid', [':nid' => $kampagne ->getNid()]);
    $all = $dbq -> fetchObject();
    return $all -> count;
  }
  
  function removeKampagnenData(\LK\Kampagne\Kampagne $kampagne){
    $num_deleted = db_delete('lk_lastviewed')
      ->condition('nid', $kampagne ->getNid())
      ->execute();
    return $num_deleted;  
  }

}
