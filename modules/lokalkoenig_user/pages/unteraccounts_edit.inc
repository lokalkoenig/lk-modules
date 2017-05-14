<?php
 
 function lk_user_disable_ma_submit($form, &$form_state){
     $account = $form["#account"];
     
     if($account -> status == 1) { 
       $msg = 'Der Account <em>' . $account -> name . '</em> wurde deaktiviert.';
       $newstatus = 0;   
     }
     else {
        $msg = 'Der Account <em>' . $account -> name . '</em> wurde aktiviert.';
       $newstatus = 1;
     }
     
     lk_note('user', $msg);
     
     $account2 = user_load($account -> uid);
     $account2 -> status = $newstatus; 
     user_save($account2);
     
    drupal_set_message($msg);
    drupal_goto('user/' . $form["#verlag"] -> uid . "/struktur");
 }
 
 
 function lk_user_disable_ma($form, &$form_state, $verlag, $account){
     
    $form["#account"] = $account;
    $form["#verlag"] = $verlag; 
    
    if($account -> status){
      drupal_set_title('Account ' . $account -> name . ' deaktivieren');
      $link = 'Account deaktivieren';
    }
    else {
      drupal_set_title('Account ' . $account -> name . ' aktivieren');
      $link = 'Account aktivieren';
    }
    
    $form['help'] = array('#markup' => '<div class="well">Deaktivierte Accounts können nicht mehr auf das Portal zugreifen; können aber jederzeit wieder aktiviert werden. Die Statistiken und die mit dem Account erworbenen Lizenzen sind davon nicht betroffen.</div>');
      
    $form['cancel'] = array(
    '#prefix' => '<div class="pull-right">',
    '#markup' => l("[Abbrechen]", 'user/' . $verlag -> uid . "/struktur"),
    '#suffix' => '</div>'
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#attributes' => array('class' => array('btn btn-danger')),
      '#value' => $link,
    );
   
    
    return $form;
 
  return array(); 
 }
 
 
 function lk_user_edit_ma_submit($form, &$form_state){
    $account = $form["#account"];
    $account_obj = \LK\get_user($account);
    $verlag = \LK\get_verlag_id($account);
    
    $edit = array();
    if($form_state['values']['pass']) {
      // Set up the edit array to pass to user_save()
      $edit = array('pass' => $form_state['values']['pass']);
     // Save the account with the new password.
      
      // Inform the user.
      $message = lk_note('pwd-change', "Das Passwort des Benutzers '". $account -> name ."' wurde erfolgreich geändert.");
      drupal_set_message($message);
    }
    
    // If name is changed
    if($form_state['values']['name'] != $account -> name) {
        $edit["name"] = $form_state['values']['name'];
    }
    
    // If E-Mail changed
    if($form_state['values']['mail'] != $account -> mail) {
         $edit["mail"] = $form_state['values']['mail'];
    }
    
    if($edit){
        user_save($form['#account'], $edit);
    }
    
    $message = lk_note('user-change', "Der Account von <em>". $account -> name ."</em> wurde geändert.");
    drupal_set_message($message);
    
    // From Profile-Page
    if(isset($_GET["profile"])){
       drupal_goto("user/" . $account -> uid);
    }
    
    // From Members-Page
    if(isset($_GET["members"])){
      $team = $account_obj ->getTeamObject();
      
      if($team){
        drupal_goto($team ->getUrl());
      }
    }
    
    // Redirect to Verlags-Page
    drupal_goto("user/" . $verlag . "/struktur");
}


function lk_user_edit_ma($form, &$form_state){
    
    
    $account = $form_state['build_info']['args'][1];
    $verlag = $form_state['build_info']['args'][0];
    
    $verlag_obj = \LK\get_user($verlag);
    //dpm($form_state);
   
    $form["#account"] = $account;
    $form["#verlag"] = $verlag; 

  
  drupal_set_title("Profil von ". $account -> name . " bearbeiten");
  
  $form['name'] = array(
        '#title' => 'Benutzername',
        '#type' => 'textfield',
        '#required' => TRUE,
        '#default_value' => $account -> name,
        '#disabled' => false 
    ); 
  
   $form['mail'] = array(
        '#title' => 'E-Mail',
        '#description' => '',
        '#type' => 'textfield',
        '#required' => TRUE,
         '#disabled' => false,
         '#default_value' => $account -> mail, 
    );
  
   $form['divider0'] = array(
        '#markup' => '<p class="help-block">Nur bei erfolgter Passwort-Eingabe wird das Passwort geändert.</p>'
    );
  
     $form['pass'] = array(
        '#type' => 'password_confirm',
        '#size' => 25,
        '#title' => ('Neues Passwort vergeben'),
        '#required' => false
    );

   $prof = profile2_load_by_user(user_load($account -> uid));
   
   if(lk_is_mitarbeiter($account) AND isset($prof["verlag"])){
        unset($prof["verlag"]);
   }
   
   $form_state['profiles'] = array(
      'main' =>  $prof["main"],
      'mitarbeiter' => $prof["mitarbeiter"]
   ); 
    
   profile2_attach_form($form, $form_state);  
   
    $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Save'));
        
        
    $form['profile_mitarbeiter']["field_mitarbeiter_verlag"]["#access"] = false;
    $form['profile_mitarbeiter']["field_mitarbeiter_verkaufsleiter"]["#access"] = false;
    $form['profile_mitarbeiter']['field_telefonmitarbeiter_uids']['#access'] = false;
    $form['profile_mitarbeiter']['field_telefonmitarbeiter_ausgabe']['#access'] = false;
    $form['profile_mitarbeiter']['field_ausgabe']['#access'] = false;
    $form["#submit"][] = 'lk_user_edit_ma_submit';
    $form["#validate"][] = 'lk_user_edit_ma_validate';
    
    $account_obj = \LK\get_user($account);
  
     $form['profile_mitarbeiter']['field_telefonmitarbeiter']['#access'] = false;
     $form['profile_mitarbeiter']['field_plz_sperre']['#access'] = false;
     $form['profile_mitarbeiter']["field_team"]["#access"] = true;
     
    if($account_obj ->isTeamleiter()){
      $form['profile_mitarbeiter']["field_team"]["#access"] = false;
      $form['profile_mitarbeiter']['field_team']["und"]["#default_value"][0] = $account_obj -> getTeam();
    } 
  
    if(!$account_obj ->isTelefonmitarbeiter()){
        $form['profile_mitarbeiter']["field_ausgabe"]["#access"] = TRUE;
        $form['profile_mitarbeiter']['field_ausgabe']['und']['#required'] = TRUE;
     }
     
     if($account_obj ->isVerlagController() || $account_obj->isVerlag()){
        $form['profile_mitarbeiter']['field_team']['#access'] = FALSE;
     }
     
     $teams = $verlag_obj -> getTeams();
     $array = array();
    
     foreach ($teams as $team){
         $array[$team -> getId()] = $team -> getTitle();
     }
     
     $form['profile_mitarbeiter']['field_team']['und']['#options'] = $array; 
     
     $ausgaben = array();
     $load = $verlag_obj -> getAusgaben();
     foreach ($load as $ausgabe){
         $ausgaben[$ausgabe -> getId()] = $ausgabe -> getTitle();
     }
     
     $form['profile_mitarbeiter']['field_ausgabe']['und']['#options'] = $ausgaben;
     
     // Prevent empty
     $ma_ausgaben = array();
     $current = $account_obj ->getCurrentAusgaben();
     
     if($current):
        foreach($current as $item){
            $ma_ausgaben[] = $item; 
         }
     endif; 
     
     $form['profile_mitarbeiter']['field_ausgabe']["und"]["#default_value"] = $ma_ausgaben;
     $form['#process'][] = 'bootstrap_lk_form_horizontal';
     
  return $form;
}


function lk_user_edit_ma_validate(&$form, &$form_state){
    
    $account = $form["#account"];
    if($error = user_validate_name($form_state['values']['name'])) {
        form_set_error('name', $error);
    }
    elseif ((bool) db_select('users')->fields('users', array('uid'))->condition('uid', $account->uid, '<>')->condition('name', db_like($form_state['values']['name']), 'LIKE')->range(0, 1)->execute()->fetchField()) {
        form_set_error('name', t('The name %name is already taken.', array('%name' => $form_state['values']['name'])));
     }
    
    
    // Trim whitespace from mail, to prevent confusing 'e-mail not valid'
    // warnings often caused by cutting and pasting.
    $mail = trim($form_state['values']['mail']);
    form_set_value($form['mail'], $mail, $form_state);

    // Validate the e-mail address, and check if it is taken by an existing user.
    if ($error = user_validate_mail($form_state['values']['mail'])) {
      form_set_error('mail', $error);
    }
    elseif ((bool) db_select('users')->fields('users', array('uid'))->condition('uid', $account->uid, '<>')->condition('mail', db_like($form_state['values']['mail']), 'LIKE')->range(0, 1)->execute()->fetchField()) {
      // Format error message dependent on whether the user is logged in or not.
      form_set_error('mail', t('The e-mail address %email is already taken.', array('%email' => $form_state['values']['mail']))); 
    }
}

?>