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

    if($account->isVerlag()){
      $ausgaben = $account->getVerlagObject()->getAusgaben();
      $return['Ausgaben'] = count($ausgaben);

      $ma = $account->getVerlagObject()->getAllUsers();
      if(count($ma) !== 0) {
        $manager ->disableUserCanChange();
      }

      $return['Mitarbeiter'] = count($ma);
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


    if(!$account->isMitarbeiter()) {
      return $return;
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

    if($acccount->isVerlag()) {
      $this->_removeVerlag($acccount);
    }
  }

  private function _removeAusgabe(\LK\Ausgabe $ausgabe) {
    $id = $ausgabe ->getId();
    $title = $ausgabe ->getTitle();

    entity_delete('ausgabe', $id);
    $this->logNotice("Lösche Ausgabe ". $title . "/" . $id);
  }

  private function _removeTeam(\LK\Team $team){
    $team_id = $team ->getId();
    $team_title = $team ->getTitle();

    db_query("DELETE FROM lk_verlag_stats WHERE stats_user_type IN ('team', 'team-weekly') AND stats_bundle_id=:team", [':team' => $team_id]);
    entity_delete('team', $team_id);

    $this->logNotice("Lösche Team ". $team_title . "/" . $team_id);
  }

  private function _removeVerlag(\LK\Verlag $acccount){

    $ausgaben = $acccount->getAusgaben();

    while(list($key, $ausgabe) = each($ausgaben)) {
      $this->_removeAusgabe($ausgabe);
    }

    db_query("DELETE FROM lk_verlag_stats WHERE stats_user_type IN ('verlag', 'verlag-weekly') AND stats_bundle_id=:uid", [':uid' => $acccount->getUid()]);
    db_query('DELETE FROM lk_log WHERE verlag_uid=:uid', [':uid' => $acccount->getUid()]);
    db_query('DELETE FROM lk_actions_time WHERE uid=:uid', [':uid' => $acccount->getUid()]);
  }

  private function _removeCommonUserData(\LK\User $acccount){

    $num_deleted = db_delete('lk_neuigkeiten_read')->condition('uid', $acccount ->getUid())->execute();
    if($num_deleted){
      $this->logNotice($num_deleted . ' Ansichten Blog');
    }

    // Check for created Neuigkiten, @TODO

    // Assign the Downloads to some User
    db_query("UPDATE lk_vku_lizenzen_downloads SET uid=0 WHERE uid=:uid", [':uid' => $acccount ->getUid()]);
    db_query("DELETE FROM lk_verlag_stats WHERE stats_bundle_id=:uid AND stats_user_type IN ('user', 'user-weekly')", [':uid' => $acccount ->getUid()]);
  }

  private function _removeMitarbeiter(\LK\User $acccount){
    $this->_removeCommonUserData($acccount);

    if($acccount ->isTeamleiter()){
      $team = $acccount ->getTeamObject();
      $this->_removeTeam($team);
    }
  }
}
