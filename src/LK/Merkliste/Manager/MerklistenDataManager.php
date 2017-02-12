<?php

namespace LK\Merkliste\Manager;
use LK\Admin\Interfaces\DataManager;
use LK\Merkliste\AdminMerkliste;

/**
 * Description of UserManager
 *
 * @author Maikito
 */
class MerklistenDataManager extends DataManager {

  function removeUserData(\LK\User $acccount){
    $merkliste = new AdminMerkliste($acccount ->getUid());
    $terms = $merkliste ->getTerms();
    
    while(list($key, $val) = each($terms)){
      $merkliste ->removeMerkliste($key);
    }
  }
  
  function getUserDataCount(\LK\User $acccount){
    $merkliste = new AdminMerkliste($acccount ->getUid());
    return ['Merklisten' => $merkliste ->getTermsCount()];
  }
  
  function getKampagnenCount(\LK\Kampagne\Kampagne $kampagne){
    $merkliste = new AdminMerkliste();
    $count = $merkliste ->getGeneralKampagnenCount($kampagne ->getNid());
    return ['Merklisten' => $count];
  }

  function removeKampagnenData(\LK\Kampagne\Kampagne $kampagne){
    db_delete('lk_lastviewed')
      ->condition('nid', $kampagne ->getNid())
      ->execute();
  }
}
