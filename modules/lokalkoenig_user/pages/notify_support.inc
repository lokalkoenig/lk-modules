<?php

function notify_support_submit(){
  
  $subject = trim($_POST["subject"]);
  $text = trim($_POST["text"]);
  
  if($subject AND $text){
    privatemsg_new_thread(array(user_load(11)), $subject, $text);
    drupal_set_message("Danke für Ihre Anfrage. Wir werden uns bei Ihnen melden.");
  }
  else {
    drupal_set_message("Beim Senden der Nachricht ist ein Fehler aufgetreten. Die Nachricht wurde nicht versendet.", 'warning');
  }
    
  if(isset($_GET["page"])){
    drupal_goto($_GET["page"]);
  }
  else {
    drupal_goto("suche");
  }  
}


?>