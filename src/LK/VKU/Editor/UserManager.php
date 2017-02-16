<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\VKU\Editor;

/**
 * Description of UserManager
 *
 * @author Maikito
 */
class UserManager extends Manager {

  var $ma_account = null;

  function __construct(\LK\User $account) {
    parent::__construct();

    $this -> setMaAccount($account);
    $this->setDocumentMode('user');
  }

  /**
   * Gets the Verlag-Object
   *
   * @return \LK\Verlag
   */
  function getVerlag(){
    return $this ->getAccount();
  }

  /**
   * Gets the Editor Account
   *
   * @return \LK\User
   */
  function getEditorAccount(){
    return $this->getMaAccount();
  }

  /**
   * Returns the current Account
   *
   * @return \LK\User
   */
  function getMaAccount(){
      return $this -> ma_account;
  }

  /**
   * Sets the current Account and the Verlag
   *
   * @param \LK\User $account
   */
  function setMaAccount(\LK\User $account) {
    $this -> ma_account = $account;
    $verlag = $account ->getVerlagObject();
    
    if(!$verlag && $account ->isModerator()){
      $verlag = \LK\get_user(LK_TEST_VERLAG_UID);
    }

    parent::setAccount($verlag);
  }
  
  /**
   * Removes a MA-Document
   *
   * @param int $id
   * @return boolean
   */
  function removeDocument($id){
    $document = $this->getDocumentMitarbeiter($id);

    if($document){
      $document ->remove();
      return true;
    }

    return false;
  }


  /**
   * Saves the Document
   *
   * @param array $data
   * @return Document
   */
  function saveMitarbeiterDocument($data){

    $document = $this->getDocumentMitarbeiter($data['id']);
    if(!$document){
      $this->sendError("Fehler beim Speichern");
    }

    $document ->setLayout($data['layout']);
    $document ->setContent($data['content']);
    $document ->setFootnote($data['footnote']);
    $document ->setPageTitle($data['page_title']);
    $document ->save();
  
    $this->sendSuccess("Das Dokument <em>" . $data['page_title'] . "</em> wurde erfolgreich gespeichert.", ['page_title' => $data['page_title']]);
  }



  function loadEditDocument($id){
    $document = $this->getDocumentMitarbeiter($id);

    if($document){
      $settings = ['verlagsmodus' => 0, 'change_layout' => 0, 'change_input' => 0];

      if($document ->getPreset() === "OpenDokument"){
        //$settings['change_layout'] = 1;
        //$settings['change_input'] = 1;
      }

      if($document ->getPreset() === "Preisliste"){
        $settings['change_layout'] = 1;
      }

      $this->sendDocument($document, $settings);
    }
   
    $this->sendError("Das Dokument konnte nicht geladen werden. [". $id ."]");
  }


  /**
   * Clones a Document for individual usage
   *
   * @param \LK\VKU\Editor\Document $document
   * @return \LK\VKU\Editor\Document
   */
  function cloneDocument(Document $document){
    $data = $document -> getTemplateData();
    unset($data['id']);
    $data['uid'] = $this->getMaAccount()->getUid();
    $data['document_vorlage'] = 0;
    $data['status'] = 1;

    $newdocument = new Document($data);
    $newdocument->save();
    return $newdocument;
  }

  /**
   *
   * @param type $id
   * @return \LK\VKU\Editor\Document
   */
  public function getDocumentMitarbeiter($id){
    return $this -> _getUserDocument($this->getMaAccount(), $id, 0);
  }

  public function getDocument($id){

    // Online-Medien-Kollektion
    if($id === 'online'){
      // create a new Dokument

      $document = new Document();
      $document ->setUser($this->getMaAccount()->getUid());
      $document->setPreset('OnlineMediumCollection');
      $document->setLayout('layout-triple-online');
      $title = $this->getVerlag()->getVerlagSetting('vku_editor_medien_collection_title', 'Online-Medien');
      $document->setPageTitle($title)->setTitle($title);
      $footnote = $this->getVerlag()->getVerlagSetting('vku_editor_medien_collection_footnote', '');
      $document ->setFootnote($footnote);
      $document ->setCategory('online');
      $document ->setStatus(1);
      $document ->setContent([]);

      return $document;
    }


    $verlag = $this->getAccount();
    $document = $this->getDocumentVerlag($verlag, $id);
    return $document;
  }


  public function getDocumentsPerCategory($category){
    $verlag = $this->getAccount();

    $documents = $this -> getDocumentsPerVerlag($verlag, $category, 1);
    $has_online_medium = FALSE;
    $array = [];

    if($category === 'online'){
      $new_documents = [];

      foreach($documents as $document){
        if($document['document_preset'] === "OnlineMedium"){
          $has_online_medium = TRUE;
          continue;
        }
        
        $new_documents[] = $document;
      }
      
      $documents = $new_documents;
      if($has_online_medium):
        $array['vku_documents-online'] = '<span class="prodid" title="Dieses Dokument können Sie bearbeiten"><span class="glyphicon glyphicon-pencil"></span></span>&nbsp;&nbsp;Online-Medien';
      endif;
    }

    foreach($documents as $document){
      $array['vku_documents-' . $document['id']] = '<span class="prodid" title="Dieses Dokument können Sie bearbeiten"><span class="glyphicon glyphicon-pencil"></span></span>&nbsp;&nbsp;' . $document['document_title'];
    }

    return $array;
  }
}
