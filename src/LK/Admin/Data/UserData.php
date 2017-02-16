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
      $manager ->disableUserCanChange();
      $return['Lizenzen Hinweis'] = "<small>Bitte editieren Sie die Lizenzen. Todo-Lizenz-Listing.</small>";;
    }
    
    $return['Erstellte Blog-Einträge'] = $this->count('eck_neuigkeit', ['uid' => $account ->getUid()]);
    $return['Ansichten Blog-Einträge'] = $this->count('lk_neuigkeiten_read', ['uid' => $account ->getUid()]);


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
    $team_title = $team ->getTitle();

    db_query("DELETE FROM lk_verlag_stats WHERE stats_user_type='team' AND stats_bundle_id=:team", [':team' => $team_id]);
    entity_delete('team', $team_id);

    $this->logNotice("Lösche Team ". $team_title . "/" . $team_id);
  }

  private function _removeVerlag(){

    
  }

  private function _removeCommonUserData(\LK\User $acccount){

    $num_deleted = db_delete('lk_neuigkeiten_read')->condition('uid', $acccount ->getUid())->execute();
    if($num_deleted){
      $this->logNotice($num_deleted . ' Ansichten Blog');
    }

    // Check for created Neuigkiten, @TODO

    // Assign the Downloads to some User
    db_query("UPDATE lk_vku_lizenzen_downloads SET uid=0 WHERE uid=:uid", [':uid' => $acccount ->getUid()]);
  }

  private function _removeMitarbeiter(\LK\User $acccount){
    $this->_removeCommonUserData($acccount);

    if($acccount ->isTeamleiter()){
      $team = $acccount ->getTeamObject();
      $this->_removeTeam($team);
    }
  }
}
