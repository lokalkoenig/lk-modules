<?php

function _vku_delete_data($vku_id){
global $user;

	$vku = new VKUCreator($vku_id);

	if(!$vku -> is() OR !$vku ->hasAccess()){
            drupal_goto("vku");
	}

	$author = $vku -> getAuthor();
        
        if(!$vku -> isActiveStatus()){
            drupal_goto($vku -> url());
        }
	
        $msg = $vku ->logEvent("remove", "Die Verkaufsunterlage (". $vku_id . ") wurde gelöscht.");
        drupal_set_message($msg);
        $vku ->setStatus('deleted');
    
	drupal_goto('user/' . $author . "/vku");
}

?>