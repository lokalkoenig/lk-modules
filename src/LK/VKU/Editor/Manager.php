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
  var $document_mode = 'verlag';

  function __construct() {
    parent::__construct(1);
    
    EditorLoader::enable();
    $this->addPreset('OnlineMediumCollection', '\\LK\\VKU\\Editor\\Presets\\OnlineMediumCollection');
  }

  /**
   * Sets the Editor-Flag
   *
   * @param string $mode
   */
  function setDocumentMode($mode){
    $this->document_mode = $mode;
  }

  /**
   * Gets the Account for the Editor-Template
   *
   * @return \LK\User
   */
  function getEditorAccount(){
    return $this ->getAccount();
  }

  /**
   * Gets the Editor-Template
   *
   * @param array $variables
   * @return string
   */
  function getEditorTemplate($variables = []){

    $account = $this->getEditorAccount();

    $variables += [
      'pxeditid' => $this-> document_mode . '-' . $account->getUid() . '-' . time()
    ];

    $defaults = \LK\VKU\VKUManager::getVKU_RenderSettings($account);

    $style = 'ppt_logos';

    $variables['footer_logos'] = [];
    foreach ($defaults["logos_unten"] as $logo):
      	$variables['footer_logos'][] = \image_style_url('pxedit_footer_logo', $logo);
    endforeach;

    $variables['header_logo'] = \image_style_url($style, $defaults["logo_oben"]);
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
   * Gets the Documents per Preset and Verlag
   *
   * @param \LK\Verlag $verlag Verlag-Account
   * @param string $preset
   * @param int $status
   * @return array
   */
  function getDocumentsPerVerlagPreset($preset, $status = 1){

    $array = [];
    $dbq = db_query("SELECT * FROM " . Document::TABLE . " "
            . "WHERE document_vorlage=1 AND uid=:uid AND document_preset=:preset "
            . "AND status=:status ORDER BY document_title ASC",
    [
      ':uid' => $this ->getAccount()->getUid(),
      ':preset' => $preset,
      ':status' => $status,
    ]);

    while($data = $dbq -> fetchObject()){
      $document = new Document((array)$data);
      $template_data = $document ->getTemplateData();
      $array[] = $template_data;
    }

    return $array;
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
   * Sends back a Document to the JS
   *
   * @param Document $document
   * @param array $settings
   */
  function sendDocument(\LK\VKU\Editor\Document $document, $settings = []){

    $preset = $this->loadPreset($document -> getPreset());
    $values = $callback['values'] = $preset -> getDefaultValues();
    $callback['options'] = $preset -> getOptions();

    $callback['options']['category'] = $document ->getCategory();
    $callback['options']['status'] = $document ->getStatus();
    $callback['options']['action'] = 'load-document';
    $callback['options']['layout'] = $document->getLayout();
    $callback['options']['image_presets'] = $this->getImagePresets();
    $callback['options']['title'] = $document ->getTitle();
    $callback['options']['id'] = $document ->getId();
    $callback['options']['page_title'] = $document ->getPageTitle();
    $callback['options']['footnote_value'] = $document ->getFootnote();

    if(!$callback['options']['page_title']){
      $callback['options']['page_title'] = $callback['options']['title'];
    }

    $callback['inputs'] = $preset -> getManagedInputs();
    $callback['values']-> layout = $document -> getLayout();
    $callback['values']-> preset = $document -> getPreset();

    $saved_content = $document -> getContent();
    if($saved_content){
      $callback['values']-> content = $saved_content;
    }
  
    $callback['options']['sample_data'] = $values -> sample;

    while(list($key, $val) = each($settings)){
      $callback['options'][$key] = $val;
    }
    
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

    $this ->sendDocument($document);
  }


  protected function _getUserDocument(\LK\User $account, $id, $vorlage = 1){
    
    $dbq = db_query("SELECT * FROM " . Document::TABLE . " WHERE document_vorlage=:vorlage AND uid=:uid AND id=:id",[
        ':uid' => $account ->getUid(),
        ':id' => $id,
        ':vorlage' => $vorlage,
    ]);
   
    $data = $dbq -> fetchObject();
    if(!$data){
      return false;
    }

    $this -> document = new Document((array)$data);
    return $this -> document;
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
     $message = "Das Dokument <em>" . $document . "</em> wurde gelöscht.";
     $this->sendJson(['message' => $message, 'documents' => \vku_editor_verlag_documents_themed($this->getAccount())]);
   }

   $this ->sendError('Das Dokument <em>ID: '. $id .'</em> wurde nicht gefunden oder Sie haben keinen Zugriff.');
 }

 /**
  * Toggles the Document state
  *
  * @param int $id
  */
 function toggleDocumentState($id){
   $document = $this ->getDocumentVerlag($this->getAccount(), $id);

   if($document){
     $state = $document ->getStatus();

     if(!$state){
      $document ->setStatus(1)->save();
      $message = "Das Dokument <em>" . $document . "</em> wurde aktivert.";
     }
     else {
      $document ->setStatus(0)->save();
      $message = "Das Dokument <em>" . $document . "</em> wurde deaktiviert.";
     }

     $this->sendJson(['message' => $message, 'documents' => \vku_editor_verlag_documents_themed($this->getAccount())]);
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
    $document ->setPageTitle($data['page_title']);
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
           'title' => 'Online-Argumentation',
           'category' => 'online',
        ],

        'OnlineMedium' => [
           'title' => 'Online-Medien',
           'category' => 'online',
        ],

       'RegionalArgumentation' => [
           'title' => 'Regional-Argumentation',
           'category' => 'print',
        ],

       'Preisliste' => [
           'title' => 'Preiskalkulation',
           'category' => 'sonstiges',
        ],

        'OpenDokument' => [
           'title' => 'Freies Dokument',
           'category' => 'sonstiges',
        ],

       //'OnlineMediumCollection' => [
       //    'title' => 'Online Medien Kollektion',
       //    'category' => 'online',
       // ],
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
  function sendSuccess($message, $additional = []){
    $this->logNotice($message);
    
    $additional['message'] = $message;
    $this ->sendJson($additional);
    exit;
  }

  function getCategoriesAvailable(\LK\User $account){
    return [
      'print' => "Print",
      'online' => "Online",
      'sonstiges' => "Sonstiges",
    ];
  }

}

