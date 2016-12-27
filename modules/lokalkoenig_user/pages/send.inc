<?php


// Empfehlung Löschen
function _lokalkoenig_send_mid_delete($mid){
global $user;

  $error = NULL;

  //$mid = (int)$mid;
  $dbq = db_query("SELECT * FROM lk_recommendation WHERE uid='". $user -> uid ."' AND id='". $mid ."'");
  $all = $dbq -> fetchObject();
  
  if(!$all) {
      $error = 'Ein Fehler ist aufgetreten. Die Empfehlung konnte nicht gelöscht werden.';
  }
  
  db_query("DELETE FROM lk_recommendation WHERE uid='". $user -> uid ."' AND id='". $mid ."'"); 
  
  if($error){
    drupal_set_message($error, 'error');
  }
  else {
    drupal_set_message('Die Empfehlung wurde gelöscht.');
  }
  
  drupal_goto("user/" . $user -> uid . "/dashboard");
  drupal_exit();
}



function _lokalkoenig_node_send_send(){
  
  $return["error"] = 0;
  
  $mitarbeiter = (array)$_POST["ma"];
  
  
  if(!$_POST["nid"] OR !$node = node_load($_POST["nid"])){
     $return["error"] = 0;
     $return["msg"] = 'Die Kampagne scheint falsch zu sein';
     drupal_json_output($return);
     drupal_exit();
  }
  
  if($node -> status == 0){
     $return["error"] = 1;
     $return["msg"] = 'Die Kampagne scheint falsch zu sein';
     drupal_json_output($return);
     drupal_exit();
  }
  
   if(count($mitarbeiter) == 0){
      $return["error"] = 1;
      $return["msg"] = 'Keine Benutzer ausgewählt';
      drupal_json_output($return);
      drupal_exit();
   }
  
   $possibile = _generate_to_options(true);
  
  foreach($mitarbeiter as $ma){
    if($ma == 11) {
        continue;
    }
      
    if(!in_array($ma, $possibile)){
         $return["error"] = 1;
         $return["msg"] = 'Fehlerhafte Auswahl entdeckt (' . $ma . ")";
         drupal_json_output($return);
         drupal_exit();
    }
  }
  
  reset($mitarbeiter);
  
  foreach($mitarbeiter as $ma){
      privatemsg_new_thread(array(user_load($ma)), 'Kampagnenempfehlung "'. $node -> title .'"', $_POST["msg"], array("nid" => $_POST["nid"]));
  }  
  
  lk_note('send', "Sende Kampagne [". $node -> nid ."] an ". count($mitarbeiter) ." Mitarbeiter");
  
  $return["msg"] = 'Die Kampagne wurde versendet.';
  drupal_json_output($return);
  drupal_exit();
}



function form_node_recomend_lk($form, &$form_state, $node){

 $form["#node"] = $node;
 
 $form["to"] = array(
  '#type' => 'select',
  '#multiple' => 'multiple',
  '#title' => 'Senden an',
  '#size' => 1,
  '#options' => _generate_to_options()
 );
 
      
  $form["to"]['#attributes'] = array('data-selected-text-format' => 'count>3', 'class' => array('selectpicker'));
  $form["to"]['#attributes']['multiple'] = 'multiple';
 
 $form["subject"] = array(
    '#type' => 'textfield', 
    '#title' => 'Betreff',
    '#required' => true,
    '#value' => 'Kampagnen-Empfehlung: ' .  $node -> title);  
  
  $form["msg"] = array(
    '#type' => 'textarea', 
    '#title' => 'Nachricht',
    '#value' => '');  


  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => ('Versenden'),
  );

return $form;
}



?>