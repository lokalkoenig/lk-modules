<?php

// user/addverlag


function lokalkoenig_user_verlag_create(){
 drupal_set_title("Verlag anlegen");

 $form = drupal_get_form('lk_create_verlag');
 return render($form);
}

function lk_user_add_verlag_validate(&$form, &$form_state){
     
  $values = $form_state["values"];
  $email = $values["mail"];

  if(!valid_email_address($email)){
     form_set_error('mail', 'Das E-Mail-Adressen-Format ist nicht korrekt');
  }
  else {
    $dbq = db_query("SELECT count(*) as count FROM users WHERE mail='". $email ."'");
    $check = $dbq -> fetchObject();
    if($check -> count != 0){
      form_set_error('mail', 'Diese E-Mail-Adresse wird bereits im Portal verwendet.');
    }
  }
}


function lk_create_verlag_submit($form, &$form_state){
    
    $edit = array(
      'name' => $form_state['values']['name'],
      'pass' => user_password(),
      'mail' => $form_state['values']['mail'],
      'init' => $form_state['values']['mail'],
      'status' => 1,
      'access' => REQUEST_TIME,
    );
    
    $account = user_save(drupal_anonymous_user(), $edit);
    profile2_form_submit_build_profile($form, $form_state);
    profile2_form_submit_cleanup($form, $form_state);

    foreach ($form_state['profiles'] as $type => $profile) {
       $profile->uid = $account->uid;
       profile2_save($profile);
    }
    
    // Rolle Verlag zuweisen
    $query = db_insert('users_roles')->fields(array('uid', 'rid'));
    $query->values(array(
              'uid' => $account->uid,
              'rid' => 5,
            ));
     $query->execute();
     
     $msg = lk_note('verlag create', 'Der neue Verlag <em>'. $account -> name .'</em> wurde erstellt.');
     drupal_set_message($msg);
     drupal_goto("user/" . $account -> uid);
}


function lk_create_verlag($form, &$form_state){

  $form['#validate'][] = 'user_register_validate';
  $form['#validate'][] = 'lk_user_add_verlag_validate';
  $form["#submit"][] = 'lk_create_verlag_submit';
  $form['name'] = array(
        '#title' => 'Benutzername',
        '#description' => 'Der Benutzername ist für die Anmeldung wichtig und sollte bspw. so aussehen: Vorname Nachname',
        '#type' => 'textfield',
        '#required' => TRUE,
    ); 
 
   $form['mail'] = array(
        '#title' => 'E-Mail',
        '#description' => '',
        '#type' => 'textfield',
        '#required' => TRUE,
    );
 
    $form['pass'] = array(
      '#type' => 'password_confirm',
      '#size' => 25,
      '#description' => t('Provide a password for the new account in both fields.'),
      '#required' => TRUE,
    );
    
    $form_state['profiles']["main"] = profile2_create(array('type' => 'main'));
    $form_state['profiles']["verlag"] = profile2_create(array('type' => 'verlag')); 
    $form_state['profiles']["mitarbeiter"] = profile2_create(array('type' => 'mitarbeiter'));
    
    profile2_attach_form($form, $form_state);  
    $form['profile_mitarbeiter']['field_mitarbeiter_verlag']['#access'] = false;
     $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Save'));
  
   $form['divider'] = array(
        '#weight' => -2,
        '#markup' => '
        <p class="help-block">Der Verlagsaccount ist gleichzeitig auch ein Mitarbeiter-Account mit dem auch Kampagnen gebucht werden können.</p>
        
        '
    );
   $form["profile_verlag"]['#weight'] = -3; 

   $hide_fields = ['field_mitarbeiter_verkaufsleiter', 'field_telefonmitarbeiter_uids', 'field_telefonmitarbeiter', 'field_telefonmitarbeiter_ausgabe', 'field_ausgabe', 'field_plz_sperre', 'field_team'];

   foreach($hide_fields as $field){
    $form['profile_mitarbeiter'][$field]["#access"] = false;
   }

   $form['profile_verlag']['field_plz_sperre']['#access'] = FALSE;
   $form['profile_verlag']['field_verlag_marken_logos']['#access'] = FALSE;
   //$form['profile_verlag']['#groups']['group_verlag_plz']['#access'] = false;

   return $form;
}


?>