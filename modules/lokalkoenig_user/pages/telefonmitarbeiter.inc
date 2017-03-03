<?php

function telefonmitarbeiter_set_plz_submit($form, $form_state){
  
  $account = \LK\get_user($form_state['build_info']['args'][0]);
  
  $titles = [];
  foreach($form_state["values"]["take"] as $val){
    if($val && $ausgabe = \LK\get_ausgabe($val)){
      $newvalues[] = $ausgabe->getId();
      $titles[] = $ausgabe->getTitle();
    }
  }
  
  $query = $_GET;
  unset($query["q"]);
  $account ->setAusgaben($newvalues);
  lk_log_verlag('Telefonmitarbeiter wählt neue Ausgaben ('. implode(", ", $titles) .') aus.');

  drupal_set_message('Ihre Ausgaben wurden erfolgreich eingestellt.'); 
  drupal_goto('suche', array("query" => $query));
}


function telefonmitarbeiter_set_plz($form, $form_state, $account){
  
   drupal_set_title("Wählen Sie Ihre Ausgaben");
    
   $path = drupal_get_path('module', 'lokalkoenig_user');
   drupal_add_css($path . '/css/set_plz.css');
   drupal_add_js($path . '/js/set_plz.js');
   
   $obj = \LK\get_user($account);
   $verlag = $obj -> getVerlag();
   
   if(!$verlag) {
     drupal_set_message("Fehlerhafter Zugriff");
     drupal_goto('user');
   }
   
   $verlag_obj = \LK\get_user($verlag);
   $ausgaben = $verlag_obj ->getAusgaben();
   
   // Checken ob User is a restrictred Team
   $team = $obj ->getTeamObject();
   if($team){
       $test = $team ->getAusgaben();
       
       if($test){
           $ausgaben = array();
           foreach($test as $ausgabe){
             $ausgaben[$ausgabe] = \LK\get_ausgabe($ausgabe);  
           }
       }
   }
   
   $sections = array();
   if($obj ->isVerlag() OR $obj ->isVerlagController()){
     // nothing 
   }
   
  foreach($ausgaben as $item){
     $id = $item -> getId(); 
     $sections[$id] = theme('bereichselect', array("account" => $item));
  }
  
  $selected = $obj ->getCurrentAusgaben();
  $default = array();
  foreach($selected as $aus){
      $default[$aus] = $aus;
  }
  
  $form['take'] = [
    '#type' => 'checkboxes',
    '#required' => true,
    '#options' => $sections,
    '#default_value' => $default,
    '#title' => "Wählen Sie bitte die Ausgaben aus, für die Sie Verkaufsunterlagen erstellen möchten.",
  ];
   
  $form['submit'] = [
    '#type' => 'submit',
    '#value' => 'Speichern',
   ];

  return $form;
}
