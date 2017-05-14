<?php

namespace LK\VKU\Editor;
use LK\Team;
use LK\VKU\Editor\Manager;

/**
 * Description of TeamManager
 *
 * @author Maikito
 */
class TeamManager extends Manager {

  var $team;
  var $LOG_CATEGORY = 'VKU Editor Team';
  var $document_mode = 'team';

  public function __construct(Team $team) {
    $this->team = $team;
    $this->setAccount(\LK\get_user($team->getVerlag()));

    parent::__construct();
  }

  public static function getFromHash($hash) {
    
  }


  /**
   * Gets a visibile hash
   *
   * @return string
   */
  public function getHash() {
    return $this->document_mode .'-'. $this->getTeam() . '-' . time();
  }

  /**
   * Gets the Document on Verlags-Level
   *
   * @param \LK\Verlag $verlag
   * @param int $id
   * @return \LK\VKU\Editor\Document Document
   */
  function getDocument($id){

    $dbq = db_query("SELECT * FROM " . Document::TABLE . " WHERE document_vorlage=1 AND uid=:uid AND id=:id AND team_id=:team_id",[
      ':uid' => $this->getAccount()->getUid(),
      ':team_id' => $this->getTeam()->getId(),
      ':id' => $id,
    ]);

    $data = $dbq -> fetchObject();
    if(!$data){
      return false;
    }

    $this -> document = new Document((array)$data);
    return $this -> document;
  }

  /**
   * @return LK\Team Team
   */
  private function getTeam() {
    return $this->team;
  }

  /**
   * Saves the Document
   *
   * @param array $data
   * @return Document
   */
  public function saveDocument($data) {
    $document = parent::saveDocument($data);
    $document->setTeam($this->getTeam()->getId())->save();

    return $document;
  }

  /**
   * @inheritDoc
   * @return array
   */
  public function getPresetsAvailable() {
    return [
      'OpenDokument' => [
      'title' => 'Freies Dokument',
      'category' => 'sonstiges',
      ],
    ];
  }

}
