<?php

/**
 * Central API-Callback
 * 
 * @return JSON-Output
 */
function vku_editor_api_cb(){
  
  $GLOBALS['devel_shutdown'] = FALSE;
  
  $docs = new LK\VKU\Editor\Manager();
 
   // Upload-Handler
  if(isset($_GET['type']) 
    && is_string($_GET['type']) 
    && $_GET['type'] == "image"){
    
    new LK\VKU\Editor\UploadHandler();
    exit;
 }
 
 if(isset($_GET['preset']) && is_string($_GET['preset'])){
      $docs ->createNewPreset($_GET['preset']);
 }
  
 $docs ->sendError("No Action provided");  
}
