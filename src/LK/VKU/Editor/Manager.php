<?php

namespace LK\VKU\Editor;
use LK\VKU\Editor\Document;

class Manager extends \LK\PXEdit\DyanmicLayout {
  
  use \LK\Log\LogTrait;
  
  var $account;
  
  /**
   * Gets the Editor-Template
   * 
   * @param array $variables
   * @return string
   */
  function getEditorTemplate($variables = []){
  

    $variables['footer_logos'] = array(
      "/sites/default/files/styles/verlags-logos-klein/public/verlagslogo/ft.png",
      '/sites/default/files/styles/verlags-logos-klein/public/verlagslogo/br.png?itok=cu0U17XP',
      '/sites/default/files/styles/verlags-logos-klein/public/verlagslogo/ct.png?itok=F6FMOtnd',
      '/sites/default/files/styles/verlags-logos-klein/public/verlagslogo/sa.png?itok=tQSG6389',
      '/sites/default/files/styles/verlags-logos-klein/public/verlagslogo/kt.png?itok=aghT9tC9',
      '/sites/default/files/styles/verlags-logos-klein/public/verlagslogo/franken-aktuell.png?itok=Ts6hLCyY'
    );

    $variables['header_logo'] = "/sites/default/files/styles/verlags-logos-klein/public/mgo_logo_quer.png?itok=C0S5IL9q";
    $variables['callback'] = url('vku_editor');
    
    return parent::getEditorTemplate($variables); 
  }
  
  /**
   * Gets the current Account
   * 
   * @return \LK\User
   */
  function getAccount(){
    return $this -> account;
  }
  
  /**
   * Sets the internal Account
   * @param \LK\User $account
   */
  function setAccount(\LK\User $account){
    $this->account = $account;
  }
  
  
  /**
   * Gets the Documents per Category and Verlag
   * 
   * @param \LK\Verlag $verlag Verlag-Account
   * @param string $category
   * @return array
   */
  function getDocumentsPerVerlag(\LK\Verlag $verlag, $category){
    
    return [];  
  }
  
  /**
   * 
   * @param Document
   */
  function saveContentData($data){
    try {
      $document = $this ->saveDocument($data);
    } catch (\Exception $ex) {
       $this->sendError('Fehler beim Speichern');
    }
            
    $this ->sendJson([
      'message' => 'Das Dokument ' . $document ->getTitle() . ' ['. $document ->getId()  .'] wurde erfolgreich gespeichert.'
    ]);
 }
  
  /**
   * Saves the Document
   * 
   * @param array $data
   * @return Document
   */
  function saveDocument($data){
    
    $document = new Document();
    $document ->setUser($this->getAccount()->getUid());
    $document ->setLayout($data['layout']);
    $document ->setCategory($data['category']);
    $document ->setPreset($data['preset']);
    $document ->setContent($data['content']);
    $document ->setTitle($data['title']);
    $document ->setStatus((int)$data['status']);
    $document ->setFootnote($data['footnote']);
    $document ->save();
  
  return $document;  
  }
  
  
  /**
   * Returns a Verlag from as Hash
   * 
   * @todo Access-Check
   * @return boolean|\LK\Verlag
   */
  function getVerlagFromHash(){
    
    if(!isset($_REQUEST['hash'])){
        return false;
    }
    
    $explode = explode('-', $_REQUEST['hash']);
    if(!isset($explode[1])){
      return false;
    }
    
    $uid = (int)$explode[1];
    
    //$current = \LK\current();
    $account = \LK\get_user($uid);
    
    if(!$account || !$account ->isVerlag()){
      return false;
    }
    
    $this->setAccount($account);
    
    return $account;
  }
  
  
  /**
   * Gets the available Presets per Account
   * 
   * @param \LK\Verlag $account Account
   * @return array
   */
  function getPresetsAvailable(\LK\Verlag $account){
    return [
       'OnlineArgumentation' => [
           'title' => 'Online Argumentation', 
           'category' => 'online',
           'desc' => 'Erstellen Sie ...', 
        ],
       
        'OnlineMedium' => [
           'title' => 'Online Medium', 
           'category' => 'online',
           'desc' => 'Erstellen Sie ...', 
        ],
        
       'RegionalArgumentation' => [
           'title' => 'Regional Argumentation', 
           'category' => 'print',
           'desc' => 'Erstellen Sie ...', 
        ],
        
       'Preisliste' => [
           'title' => 'Preisliste', 
           'category' => 'sonstiges',
           'desc' => 'Erstellen Sie ...',
        ],
        
        'OpenDokument' => [
           'title' => 'Freies Dokument', 
           'category' => 'sonstiges',
           'desc' => 'Erstellen Sie ...',
        ],
        
       'OnlineMediumCollection' => [
           'title' => 'Online Medien Kollektion', 
           'category' => 'online',
           'desc' => 'Erstellen Sie ...',
        ],
    ];
  }
  
  
  function getCategoriesAvailable(\LK\User $account){
  
    return [
      'print' => "Print",
      'online' => "Online",
      'sonstiges' => "Sonstiges",
    ];  
  }
    
}

