<?php


function lokalkoenig_merkliste_ajax_callback_send($tid){
  $term = taxonomy_term_load($tid);
    
  if(!$term){
    drupal_goto(MERKLISTE_URI);
  }

  $view = views_get_view_result('merkliste3', 'page', $term -> tid);
  
  $array = array();
  foreach($view as $entity){
     $array[] = $entity->field_field_merkliste_node[0]['raw']['nid'];
  }
  
  // Keine Entities mit dem Term
  if(count($array) == 0){
     drupal_goto(MERKLISTE_URI) ;
  }
  
  if(!$_POST["ma"])  $_POST = array();
  
  $to = array();
  $subject = trim(@$_POST["subject"]);
  $message = trim(@$_POST["message"]);
  
   if(!$subject){
     drupal_set_message("Der Betreff ist nicht eingegeben", 'error');
     drupal_goto(MERKLISTE_URI . "/" . $tid);  
  }
  
   $tos = _generate_to_options(true);
   $tos[] = 11;
   
   foreach($_POST["ma"] as $useruid){
    if(!in_array($useruid, $tos)){
         drupal_set_message("Eine enerlaubte Auswahl wurde entdeckt.", 'error');
         drupal_goto(MERKLISTE_URI . "/" . $tid);
    }
    
    $to[] = $useruid; 
   }
  
  if(count($to) == 0){
     drupal_set_message("Sie haben keine Empfänger ausgewählt.", 'error');
     drupal_goto(MERKLISTE_URI . "/" . $tid);  
  }
  
  // Send to everybody
  foreach($_POST["ma"] as $ma){
    privatemsg_new_thread(array(user_load($ma)), $subject, $message, array("nids" => $array));
  }  
  
  drupal_set_message("Die Merkliste wurde erfolgreich versendet.");
  drupal_goto(MERKLISTE_URI . "/" . $tid);    
}

?>