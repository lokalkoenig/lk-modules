<?php

/**
 * Central API-Callback
 * 
 * @return JSON-Output
 */
function vku_editor_api_cb(){
  $GLOBALS['devel_shutdown'] = FALSE;
 
   // Upload-Handler
  if(isset($_GET['type']) 
    && is_string($_GET['type']) 
    && $_GET['type'] == "image"){
    
    new LK\VKU\Editor\UploadHandler();
    exit;
 }

 $explode = explode('-', $_REQUEST['hash']);

 $action = '';
 if(isset($_REQUEST['action'])){
   $action = $_REQUEST['action'];
 }

 if($explode[0] === 'user'){
   $current = \LK\current();
   $manager = new LK\VKU\Editor\UserManager($current);

   if($explode[1] != $current ->getUid()){
     $manager ->sendError("Sie haben keinen Zugriff auf diese Funktion.");
     exit;
   }
   
  // Load document
  if($action === 'load-document' && isset($_REQUEST['id'])){
    $manager ->loadEditDocument($_REQUEST['id']);
  }
  
  // All
  if($action && $action === 'save-document'){
    $data = $_POST;
    $manager -> saveMitarbeiterDocument($data);
  }
 
  // save Document
  // Preset-Action
 }
 else {
  $manager = new LK\VKU\Editor\Manager();
  $verlag = $manager -> getVerlagFromHash();
  if(!$verlag){
    $manager ->sendError("Sie haben keinen Zugriff auf diese Funktion.");
    exit;
  }

  // Load document
  if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'load-document' && isset($_REQUEST['id'])){
    $manager ->loadDocumentVerlag($verlag, $_REQUEST['id']);
  }

  // Verlag- Releated requests
  if(isset($_POST['action']) && $_POST['action'] === 'update-documents'){
   $manager ->sendJson(['documents' => \vku_editor_verlag_documents_themed($verlag)]);
  }
  

  // Verlag
  if(isset($_GET['preset']) && is_string($_GET['preset'])){
    $manager ->createNewPreset($_GET['preset']);
  }

  // Preset-Action - Both
  if(isset($_POST['action']) && is_string($_POST['action']) && $_POST['action'] == 'preset-action'){
    $preset = $manager ->loadPreset($_POST['values']['preset']);
    $preset -> performCallback($_POST);
  }

  // Verlag
  if(isset($_POST['action']) && is_string($_POST['action']) && $_POST['action'] == 'remove-document'){
    $manager -> removeDocument((int)$_POST['id']);
  }

  // Verlag
  if(isset($_POST['action']) && is_string($_POST['action']) && $_POST['action'] == 'toggle-state'){
    $manager -> toggleDocumentState((int)$_POST['id']);
  }

  // All
  if(isset($_POST['action']) && is_string($_POST['action']) && $_POST['action'] == 'save-document'){
    $data = $_POST;
    $manager -> saveContentData($data);
  }
 }

 $manager ->sendError("No Action provided");  
}
