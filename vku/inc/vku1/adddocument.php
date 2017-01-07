<?php

/**
 * Menu related Path
 * Adds a new Document  
 * 
 * @changed 2015-11-05 Add support for standard documents
 * 
 *  @vku_id
 *  @entity_id 
 */
function _vkuconn_add_docuement($vku_id, $entity_id){
global $user;
        
        $vku = new VKUCreator($vku_id);
	if(!$vku ->isActiveStatus()){
            drupal_goto($vku -> url());
	}

	$author = $vku -> getAuthor();
	if($user -> uid != $author){
            drupal_goto($vku -> url());
	}
        
        $documents = vku_get_standard_documents();
        
        if(isset($documents[$entity_id])){
            $doc = $documents[$entity_id];
            $id = $vku -> data ->  add('default', $doc["id"], $doc["weight"], 1);
            
            $message = $vku ->logEvent('add-document', "Das Dokument " . $doc["title"] . " wurde hinzugefügt");
            drupal_set_message($message);
            
            if($doc["id"] == 'title'){
               // Redirect to the Configuration Page
               drupal_goto("vku/". $vku_id ."/edit/" . $id); 
               drupal_exit();
            }
            
            drupal_goto($vku -> vku_url()); 
            drupal_exit();
        }

        
    drupal_goto($vku -> vku_url());
}	






?>