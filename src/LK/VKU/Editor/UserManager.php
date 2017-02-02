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
    return $this -> _getUserDocument($this->getMaAccount(), $id);
  }

  public function getDocument($id){
    $verlag = $this->getAccount();
    $document = $this->getDocumentVerlag($verlag, $id);
    return $document;
  }


  public function getDocumentsPerCategory($category){
    $verlag = $this->getAccount();

    $documents = $this -> getDocumentsPerVerlag($verlag, $category, 1);
    $array = [];

    foreach($documents as $document){
      $array['vku_documents-' . $document['id']] = $document['document_title'] . '<br />'
              . '<small><span class="prodid"><span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;<span class="hidden">(</span>Editierbar<span class="hidden">es Dokument)</span></span></small>';
    }

    return $array;
  }
}
