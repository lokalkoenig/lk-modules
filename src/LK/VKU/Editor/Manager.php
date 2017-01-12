<?php

namespace LK\VKU\Editor;
use LK\VKU\Editor\Document;

/**
 * Manage the things all around the Verlag
 */
class Manager extends \LK\PXEdit\DyanmicLayout {
  
  use \LK\Log\LogTrait;
  
  var $account;
  var $LOG_CATEGORY = 'VKU Editor Verlag';
  var $document;
  
  /**
   * Gets the Editor-Template
   * 
   * @param array $variables
   * @return string
   */
  function getEditorTemplate($variables = []){
    
    $variables = [
      'pxeditid' => 'verlag-' . $this->account->getUid() . '-' . time() 
    ];
    
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
   * @param int $status
   * @return array
   */
  function getDocumentsPerVerlag(\LK\Verlag $verlag, $category, $status = 1){
    
    $array = [];
    $preset = $this -> getPresetsAvailable($verlag);
    
    $dbq = db_query("SELECT * FROM " . Document::TABLE . " WHERE document_vorlage=1 AND uid=:uid AND document_category=:category AND status=:status ORDER BY document_title ASC",[
        ':uid' => $verlag ->getUid(),
        ':category' => $category,
        ':status' => $status,
    ]);
    
    while($data = $dbq -> fetchObject()){
      $document = new Document((array)$data);
      $template_data = $document ->getTemplateData();
      $template_data['preset_title'] = $preset[$template_data['document_preset']]['title'];
      
      $array[] = $template_data;
    }
    
    return $array;  
  }
  
  /**
   * Loads an Document as Verlag
   * 
   * @param \LK\Verlag $verlag
   * @param int $id
   */
  function loadDocumentVerlag(\LK\Verlag $verlag, $id){
    
    $document = $this->getDocumentVerlag($verlag, $id);
    if(!$document){
      $this->sendError('Das Dokument wurde nicht mehr gefunden.');
    }
    
    $preset = $this->loadPreset($document -> getPreset());
    
    $callback['values'] = $preset -> getDefaultValues();
    $callback['options'] = $preset -> getOptions();
    $callback['options']['category'] = $document ->getCategory();  
    $callback['options']['status'] = $document ->getStatus();  
    $callback['options']['action'] = 'load-document';
    $callback['options']['image_presets'] = $this->getImagePresets();  
    $callback['options']['title'] = $document ->getTitle();
    $callback['options']['id'] = $document ->getId();
    $callback['inputs'] = $preset -> getManagedInputs();
    $callback['values']-> preset = $document -> getPreset();  
    $callback['values']-> content = $document -> getContent();
    
    $html = array();
    $layouts = $preset -> getAvailableLayouts();
        
    foreach ($layouts as $layout){
      $layout = $this->getLayout($layout);
      $html[] = (string)$layout;
    }

    $callback['image_presets'] = $this -> getImagePresetsParsed();
    $callback['layouts'] = implode('', $html);
    
    $this ->sendJson($callback);
  }
  
  /**
   * Gets the Document on Verlags-Level
   * 
   * @param \LK\Verlag $verlag
   * @param int $id
   * @return \LK\VKU\Editor\Document Document
   */
  function getDocumentVerlag(\LK\Verlag $verlag, $id){
    
    $dbq = db_query("SELECT * FROM " . Document::TABLE . " WHERE document_vorlage=1 AND uid=:uid AND id=:id",[
        ':uid' => $verlag ->getUid(),
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
   * 
   * @param Document
   */
  function saveContentData($data){
    try {
      $document = $this ->saveDocument($data);
    } catch (\Exception $ex) {
       $this->sendError('Fehler beim Speichern');
    }
            
    $this ->sendSuccess('Das Dokument <em>' . $document . '</em> wurde erfolgreich gespeichert.');
 }
 
 function removeDocument($id){
   $document = $this ->getDocumentVerlag($this->getAccount(), $id);
   
   if($document){
     $document ->remove();
     $this ->sendSuccess("Das Dokument <em>" . $document . "</em> wurde gelöscht.");
   }
   
   $this ->sendError('Das Dokument <em>ID: '. $id .'</em> wurde nicht gefunden oder Sie haben keinen Zugriff.');
 }
  
 /**
 * Saves the Document
   * 
   * @param array $data
   * @return Document
   */
  function saveDocument($data){
    
    if(isset($data['id'])){
      $document = $this->getDocumentVerlag($this->getAccount(), $data['id']);
      if(!$document){
        $this->sendError("Fehler beim Speichern");
      }  
    }
    else {
      $document = new Document();
      $document ->setVorlage(1);
      $document ->setUser($this->getAccount()->getUid());
    }
    
    
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
  function getPresetsAvailable(){
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
  
  /**
   * Sends back an Error
   * 
   * @param type $message
   */
  function sendError($message) {
    $this ->logError($message);
    parent::sendError($message);
  }
  
  /**
   * Sends back a Success-Request with message
   * 
   * @param type $message
   */
  function sendSuccess($message){
    $this->logNotice($message);
    $this ->sendJson(['message' => $message]);
  }
  
  function getCategoriesAvailable(\LK\User $account){
    return [
      'print' => "Print",
      'online' => "Online",
      'sonstiges' => "Sonstiges",
    ];  
  }
    
}

