<?php

namespace LK\VKU\Data;
use LK\Admin\Interfaces\DataManager as DM;

/**
 * Description of DataManager
 *
 * @author Maikito
 */
class DataManager extends DM {

  function removeUserData(\LK\User $acccount){
    $manager = new \LK\VKU\PageManager();
    $dbq = db_query('SELECT id FROM lk_vku WHERE uid=:uid', [':uid' => $acccount ->getUid()]);
    foreach($dbq as $all){
      $manager ->removeVKU(\LK\VKU\VKUManager::getVKU($all -> id));
    }
  }

  function getUserDataCount(\LK\User $acccount){
    return ['VKU' => $this->count('lk_vku', ['uid' => $acccount ->getUid()])];
  }

  function getKampagnenCount(\LK\Kampagne\Kampagne $kampagne){
    $dbq = db_query("SELECT id FROM lk_vku_data WHERE data_entity_id=:nid AND data_module='node' AND data_class='kampagne'", [':nid' => $kampagne ->getNid()]);
    $all = $dbq -> fetchObject();

    return ['Verwaltete Kampagne in VKU' => $all -> count];
  }

  function removeKampagnenData(\LK\Kampagne\Kampagne $kampagne){
    $dbq = db_query("SELECT id FROM lk_vku_data WHERE data_entity_id=:nid AND data_module='node' AND data_class='kampagne'", [':nid' => $kampagne ->getNid()]);


    return ['Kampagne in VKU' => $all -> count];
  }
}
