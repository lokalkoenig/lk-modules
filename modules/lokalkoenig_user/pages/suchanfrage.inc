<?php

function _lk_suchanfrage(){
  lk_set_icon('question-sign');

  return drupal_get_form('_lk_suchanfrage_form');
}


function _lk_suchanfrage_form_submit($form, $form_state){
global $base_url, $user;
  
  $destination = drupal_get_destination();
  $values = $form_state["values"];
  
  $content  = array();
  $content["Branche"] = $values["auswahl"];
  $content["Anlass"] = $values["anlass"];

  $options = array();
  
  foreach($values['ziel'] as $item){
       if($item){
          $tax = taxonomy_term_load($item);
          $options[] = $tax -> name;
       }
  }
  
  $content["Ziel"] = implode(", ", $options);
  $options = array();
  
  foreach($values['kampagnengroesse'] as $item){
       if($item){
          $tax = taxonomy_term_load($item);
          $options[] = $tax -> name;
       }
  }
  
  $content["Kampagnengroesse"] = implode(", ", $options);
  $content["Idee"] = $values["idee"];
  
  if($form["#searchempty"]) {
     $content["Leere Suche nach"] = $form["#searchempty"];
  
  }
  else $content["Link"] = $base_url . '/' .  $destination["destination"];
  
  $parsed = array();
  
  while(list($key, $val) = each($content)){
     $parsed[] = $key . ": " .  $val . "\n";
  }
  
  
  $text = implode("\n", $parsed);
  $subject = 'Individuelle Anfrage';
  
  // Send Message
  
  privatemsg_new_thread(array(user_load(11)), $subject, $text);
  drupal_set_message("Danke für Ihre Anfrage.");
  
  if($form["#searchempty"]) {
    drupal_goto("suche");
  }
  
}


function _lk_suchanfrage_form($form, $form_state, $search_empty = ''){
     
   $form["#searchempty"] = $search_empty;
  
   $form['info2'] = array(
      '#weight' => 1,
      '#markup' => '<p>'. variable_get('lk_search_formrequest', '') .'</p>'
   );
  
   $form['destination2'] = array(
      '#type' => 'hidden',
      '#value' => @$_GET["destination"]
   );
  
  // Kampagnengröße
 $form['branche'] = array(
  '#type' => 'fieldset', 
    '#title' => ('Einordnung'), 
    '#weight' => 5, 
    '#collapsible' => false, 
    '#collapsed' => FALSE,
  );
   
  $form['branche']['auswahl'] = array(
      '#type' => 'textarea',
      '#title' => ('Branche/Unternehmen z.B. Gaststätten, Optiker, Sanitätshaus'),
      '#required' => TRUE
    );
    
    $options = array();
    $ziel = taxonomy_get_tree(9);
    
    foreach($ziel as $tax){
      $options[$tax -> tid] = $tax -> name;
    }
   
    $form['branche']['anlass'] = array(
      '#type' => 'textarea',
      '#rows' => 5,
      '#required' => TRUE,
      '#title' => ('Anlass der Anzeige: <small>(z.B. Jubiläum, Imagewerbung, Rabattaktion, …)</small>'),
    ); 
    
   $form['branche']['ziel'] = array(
      '#type' => 'checkboxes',
      '#options' => $options,
      '#required' => TRUE,
      '#title' => ('Kommunikationsziel')
    );
 
    $form['kampangen'] = array(
    '#type' => 'fieldset', 
      '#title' => ('Kampagnengröße'), 
      '#weight' => 6, 
      '#required' => TRUE,
      '#collapsible' => false, 
      '#collapsed' => FALSE,
    );
  
    $ziel = taxonomy_get_tree(7);
    $options = array();
    foreach($ziel as $tax){
      $taxonomy = taxonomy_term_load($tax -> tid);
      if($taxonomy -> tid == 6423) continue;
      $options[$tax -> tid] = 'Kampagnengröße ' . $taxonomy -> name . "<br /><small>". $taxonomy -> description .'</small>';
    }
    
   $form['kampangen']['kampagnengroesse'] = array(
      '#type' => 'checkboxes',
      '#options' => $options,
      '#title' => t('Kampagnengröße'),
    );
    
    
    $form['info'] = array(
    '#type' => 'fieldset', 
      '#title' => ('Gestaltungsidee'), 
      '#weight' => 6, 
      '#collapsible' => false, 
      '#collapsed' => FALSE,
    );
    
    
     $form['info']['idee'] = array(
      '#type' => 'textarea',
      '#rows' => 5,
      '#title' => ('Gestaltungsidee für Text oder Bild'),
    );
  
  
   $form['submit'] = array(
      '#type' => 'submit',
      '#weight' => 7, 
      '#value' => ('Abschicken'),
    );
   
return $form;
}

?>

