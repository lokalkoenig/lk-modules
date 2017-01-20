<?php


function lokalkoenig_merkliste_ajax_callback_send($tid){
  
  $manager = new \LK\Merkliste\UserMerkliste();
  $merkliste = $manager->loadMerkliste($tid);
  
  if(!$merkliste){
    drupal_goto($manager->getUrl());
  }
  
  $to = array();
  $choosen_ma = (array)$_POST["ma"];
  
  $subject = trim(@$_POST["subject"]);
  $message = trim(@$_POST["message"]);
  
  if(!$subject){
    drupal_set_message("Der Betreff ist nicht eingegeben", 'error');
    drupal_goto($merkliste ->getUrl());  
  }
  
  $possibile = _generate_to_options(true);
  $possibile[] = 11;
   
  foreach($choosen_ma as $useruid){
    if(!in_array($useruid, $possibile)){
         drupal_set_message("Eine enerlaubte Auswahl wurde entdeckt.", 'error');
         drupal_goto($merkliste ->getUrl());
    }
    
    $to[] = $useruid; 
  }
  
  if(count($to) == 0){
     drupal_set_message("Sie haben keine Empfänger ausgewählt.", 'error');
     drupal_goto($merkliste ->getUrl());  
  }
  
  // Send to everybody
  foreach($to as $ma){
    privatemsg_new_thread(array(user_load($ma)), $subject, $message, array("nids" => $merkliste ->getKampagnen()));
  }  
  
  drupal_set_message("Die Merkliste wurde erfolgreich versendet.");
  drupal_goto($merkliste ->getUrl());    
}
