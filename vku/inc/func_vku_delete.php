<?php

function _vku_current_delete($id){
  global $user;
      
      $vku = new VKUCreator($id);
      if(!$vku -> is() OR !$vku -> hasAccess()){
      	 drupal_goto('user/' . $user -> uid . '/vku');
         drupal_exit();
      }
      
      // Check Status
      $status = $vku -> getStatus();
      if(in_array($status, array("purchased", "purchased_done"))){
          drupal_set_message("Die Verkaufsunterlage kann nicht gelöscht werden.");
          drupal_goto($vku ->url());
          drupal_exit();
      }
      
      $author = $vku -> getAuthor();
      
      if($status == 'deleted'){
          $msg = $vku ->logEvent("remove-vku", "Ihre Verkaufsunterlage wurde gelöscht.");
          $vku ->remove();
          drupal_set_message($msg);   
      }
      else {
          
          if($status == 'template'){
              $vku ->logEvent("remove-vku", "Vorlage wurde gelöscht.");
              $vku -> remove();
              drupal_goto('user/' . $author . '/vkusettings');
          }
          
          $vku -> setStatus('deleted');
          verlag_log(1, 'Verkaufsunterlagen', 'Verkaufsunterlage verworfen', array('vku_id' => $id));
          $vku ->logEvent("remove-vku", "Verkaufsunterlage wurde gelöscht.");
          drupal_set_message("Ihre Verkaufsunterlage wurde in den Papierkorb verschoben.");   
      }
      
      drupal_goto('user/' . $author . '/vku');
      drupal_exit();
  }
