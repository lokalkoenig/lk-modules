<?php
namespace LK\Admin\Data;

/**
 * Description of UserData
 *
 * @author Maikito
 */
class UserData extends \LK\Admin\Interfaces\DataManager {

  use \LK\Log\LogTrait;
  var $LOG_CATEGORY = 'User Verwaltung';

  function getUserDataCount(\LK\User $account){
    
    $return = [];
    $manager = $this->getManager();

    if($account ->isAgentur() || $account ->isModerator()){
      $manager ->disableUserCanChange();
      return [];
    }

    if($account ->isVerlag() || $account ->isVerlagController()){
      $manager ->disableUserCanChange();
      return [];
    }

    // Lizenzen
    $count = $this->count('lk_vku_lizenzen', ['lizenz_uid' => $account->getUid()]);
    $return['Lizenzen'] = $count . " (als Mitarbeiter)";

    if($count){
      $return['Lizenzen Hinweis'] = "<small>Bestehende Lizenzen werden dem Verlag zugeordnet.</small>";;
    }

    $team = $account ->getTeamObject();
    $return['Team'] = l($team ->getTitle(), $team ->getUrl());
    $return['Team Leiter'] = \LK\u($team ->getLeiter());

    if($account ->isTeamleiter()){
      $members = $team ->getUser_count();
      if($members > 1){
        $return['Team Hinweis'] = "<small>Dieser Team-Leiter Account kann im Moment nicht verändert werden, da sich mehrere Accounts im Team befinden.</small>";
        $manager ->disableUserCanChange();
      }
    }
    

    return $return;
  }


  function removeUserData(\LK\User $acccount){
    if($acccount ->isMitarbeiter()){
      $this->_removeMitarbeiter($acccount);
    }
  }


  private function _removeTeam(\LK\Team $team){
    $team_id = $team ->getId();
    db_query("DELETE FROM lk_verlag_stats WHERE stats_user_type='team' AND stats_bundle_id=:team", [':team' => $team_id]);
    entity_delete('team', $team_id);

    $this->logNotice("Remove Team from Database");
  }

  private function _removeVerlag(){

    
  }

  private function _removeCommonUserData(\LK\User $acccount){

    db_delete('lk_neuigkeiten_read')->condition('uid', $acccount ->getUid())->execute();
  
    $verlag = $acccount ->getVerlag();
    if($verlag){
      db_query("UPDATE lk_vku_lizenzen_downloads SET uid=0 WHERE uid=:uid", [':uid' => $acccount ->getUid()]);
    }
  }

  private function _removeMitarbeiter(\LK\User $acccount){
    $this->_removeCommonUserData($acccount);

    if($acccount ->isTeamleiter()){
      $team = $acccount ->getTeamObject();
      $this->_removeTeam($team);
    }
  }
}
