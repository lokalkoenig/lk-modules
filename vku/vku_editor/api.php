<?php

/**
 * Central API-Callback
 * 
 * @return JSON-Output
 */
function vku_editor_api_cb(){
  
  $GLOBALS['devel_shutdown'] = FALSE;
  
  $manager = new LK\VKU\Editor\Manager();
 
   // Upload-Handler
  if(isset($_GET['type']) 
    && is_string($_GET['type']) 
    && $_GET['type'] == "image"){
    
    new LK\VKU\Editor\UploadHandler();
    exit;
 }
 
  $verlag = $manager -> getVerlagFromHash();
  if(!$verlag){
    $manager ->sendError("Sie haben keinen Zugriff auf diese Funktion.");
    exit;
  }
  
  // Verlag- Releated requests
  if(isset($_POST['action']) && $_POST['action'] === 'update-documents'){
   $manager ->sendJson(['documents' => \vku_editor_verlag_documents_themed($verlag)]);
  }
  
  // Load document
  if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'load-document' && isset($_REQUEST['id'])){
    $manager ->loadDocumentVerlag($verlag, $_REQUEST['id']);
  }
  
  if(isset($_GET['preset']) && is_string($_GET['preset'])){
      $manager ->createNewPreset($_GET['preset']);
  }

  // Preset-Action
  if(isset($_POST['action']) && is_string($_POST['action']) && $_POST['action'] == 'preset-action'){
    $preset = $manager ->loadPreset($_POST['values']['preset']);
    $preset -> performCallback($_POST);
  }

  if(isset($_POST['action']) && is_string($_POST['action']) && $_POST['action'] == 'remove-document'){
    $manager -> removeDocument((int)$_POST['id']);
  }
  
   if(isset($_POST['action']) && is_string($_POST['action']) && $_POST['action'] == 'toggle-state'){
    $manager -> toggleDocumentState((int)$_POST['id']);
  }
  
  if(isset($_POST['action']) && is_string($_POST['action']) && $_POST['action'] == 'save-document'){
    $data = $_POST;
    $manager -> saveContentData($data);
  }
 
 $manager ->sendError("No Action provided");  
}
