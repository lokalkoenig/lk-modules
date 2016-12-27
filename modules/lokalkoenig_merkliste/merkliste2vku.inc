<?php


function lokalkoenig_merkliste_ajax_callback_rename($tid){
  
  $term = taxonomy_term_load($tid);
  $newname = trim($_POST["name"]);
  
  if(!$term){
    drupal_goto(MERKLISTE_URI);
  }

  if(!$newname){
     drupal_set_message("Bitte vergeben Sie einen eindeutigen Merklisten-Titel."); 
     drupal_goto(MERKLISTE_URI . "/" . $tid);
  }      

  $view = views_get_view_result('merkliste3', 'page', $term -> tid);
  
  $array = array();
  foreach($view as $entity){
    $array[] = $entity -> id;
  }
  
  // Keine Entities mit dem Term
  if(count($array) == 0){
     drupal_goto(MERKLISTE_URI);
  }
  
  if($term -> name == $newname){
    drupal_set_message("Bitte vergeben Sie einen neuen Merklisten-Titel."); 
    drupal_goto(MERKLISTE_URI . "/" . $term -> tid);
  }
  
  // Rename
  
  // Search for User-Terms who are the Same
  $view = views_get_view_result('merkliste3', 'block_ml');
  $newtid = false;
  
  foreach($view as $item){
     if($item->taxonomy_term_data_field_data_field_merkliste_tags_name == $newname){
        $newtid = $item->taxonomy_term_data_field_data_field_merkliste_tags_tid;  
     }              
  }
  
  if(!$newtid){
    $newterm = new stdClass();
    $newterm->name = $newname;
    $newterm->vid = 13; 
    taxonomy_term_save($newterm);
    $newtid = $newterm -> tid;
   }
  
  // Going through the Entities
  foreach($array as $ent){
    $e = entity_load('merkliste', array($ent));
    $entity= $e[$ent];
    
    $new_tids = array();
    foreach($entity->field_merkliste_tags['und'] as $tids){
        if(!in_array($tids["tid"], array($term -> tid, $newtid))){
            $new_tids[] = $tids; 
        }
    }
    
     $new_tids[]["tid"] = $newtid; 
        
    if($new_tids){
      $entity->field_merkliste_tags['und'] = $new_tids;
      entity_save('merkliste', $entity);
    }
    else {
      entity_delete('merkliste', $ent);
    }
  }
  
  drupal_set_message("Die Merkliste <b>". $term -> name ."</b> wurde in <b>". $newname ."</b> umbenannt.");
  drupal_goto(MERKLISTE_URI . "/" . $newtid);
}

function lokalkoenig_merkliste_ajax_callback_delete($tid){
global $user;

  $term = taxonomy_term_load($tid);

  if(!$term){
    drupal_goto(MERKLISTE_URI);
  }
  
  $view = views_get_view_result('merkliste3', 'page', $term -> tid);
  
  $array = array();
  foreach($view as $entity){
    $array[] = $entity -> id;
  }
  
  // Keine Entities mit dem Term
  if(count($array) == 0){
     drupal_goto(MERKLISTE_URI);
  }
  
  foreach($array as $ent){
    $e = entity_load('merkliste', array($ent));
    $entity= $e[$ent];
    
    $new_tids = array();
    foreach($entity->field_merkliste_tags['und'] as $tids){
        if($tids["tid"] != $term -> tid){
           $new_tids[] = array('tid' => $tids["tid"]); 
        }
    }
    
    if($new_tids){
      $entity->field_merkliste_tags['und'] = $new_tids;
      entity_save('merkliste', $entity);
    }
    else {
      entity_delete('merkliste', $ent);
    }
  }
  
  drupal_set_message("Die Merkliste <b>". $term -> name ."</b> wurde gelöscht.");
  drupal_goto(MERKLISTE_URI);
}




?>