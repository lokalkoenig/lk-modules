<?php

function lokalkoenig_user_verlag_addnew($uid){
   drupal_set_title('Neuen Mitarbeiter anlegen');
   
   $verlag = \LK\get_user($uid);
   $teams = $verlag -> getTeams();
   
   // Neues Team mit VKL anlegen
   if(isset($_GET["vkl"])){
        $form =  (drupal_get_form('lk_user_add_ma', $uid, 0));
        return render($form); 
   }
   
   // MA in Team stecken
   else {
      $team_id = (int)$_GET["team"]; 
      if(isset($teams[$team_id])){
          $form =  drupal_get_form('lk_user_add_ma', $uid, $team_id);
          return render($form); 
      }
   }
   
   drupal_goto('user/' . $uid . "/struktur");
}

function lk_user_add_ma_validate(&$form, &$form_state){
     
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

function _lk_generate_unique_username($user_name){
   
    $try = 0;
    while(true){
      $user_name_take = $user_name;
      
      if($try != 0) { 
          $user_name_take = $user_name . ' ' . $try;
      }
      
      $userexists = db_select('users', 'u')
       ->fields('u', array('name'))
        ->condition('u.name', $user_name_take, '=')
        ->execute()
        ->fetchAssoc();

     if($userexists){
        $try++;
        continue;
     }
     else {
        $user_name = $user_name_take;
        break;
     }
    }     

return $user_name_take;    
}


function lk_user_add_vuegr_ma_submit($form, $form_state){
     // Verlag-ID
    
    $verlag = $form["#verlag"];
    $form_state['values']["profile_mitarbeiter"]["field_mitarbeiter_verlag"]["und"][0]["uid"] = $verlag;
    
    $values =  $form_state['values'];
    $user_name =  trim($values['profile_main']['field_profile_vorname']['und'][0]['value']) . ' ' . trim($values['profile_main']['field_profile_name']['und'][0]['value']);
    
    $pwd = user_password();
    $user_name = _lk_generate_unique_username($user_name);
    
    $mail = $form_state['values']['mail'];
    
    $edit = array(
          'name' => $user_name,
          'pass' => $pwd,
          'mail' => $mail,
          'init' => $mail,
          'status' => 1,
          'access' => REQUEST_TIME,
          'roles' => array(8 => 'VGMA')
    );
    
    $account = user_save(drupal_anonymous_user(), $edit);
    profile2_form_submit_build_profile($form, $form_state);
    profile2_form_submit_cleanup($form, $form_state);
    
    foreach ($form_state['profiles'] as $type => $profile) {
       $profile->uid = $account->uid;
       profile2_save($profile);
    }
     
     cache_clear_all();
     
     $redirect = "user/" . $verlag . "/struktur";
     $account = _lk_user($account, true);
     profile2_save($account -> profile["mitarbeiter"], 'mitarbeiter');  
     
     lk_note('user', 'Account von ' . $account -> name . " (Pwd: ". $pwd ."/Email: ". $account -> mail .") wurde erstellt.");
      
     drupal_set_message("<div class=\"well\"><strong>Der Neue Benutzer wurde erstellt:</strong><br />
     Benutzer: <em>". $account -> name ."</em><br />
     Passwort: <code>". $pwd . "</code><br />
     E-Mail: " . $account -> mail . '</div>');
     
     drupal_goto($redirect);
     drupal_exit(); 
    
}


function lk_user_add_ma_submit($form, &$form_state){
    
    // Verlag-ID
    $values =  $form_state['values'];
    $user_name =  trim($values['profile_main']['field_profile_vorname']['und'][0]['value']) . ' ' . trim($values['profile_main']['field_profile_name']['und'][0]['value']);
    $pwd = user_password();
    $user_name = _lk_generate_unique_username($user_name);
    $mail = $form_state['values']['mail'];
    
    $edit = array(
          'name' => $user_name,
          'pass' => $pwd,
          'mail' => $mail,
          'init' => $mail,
          'status' => 1,
          'access' => REQUEST_TIME,
          'roles' => array(6 => 'MA')
    );
    
    $account = user_save(drupal_anonymous_user(), $edit);
     
    profile2_form_submit_build_profile($form, $form_state);
    profile2_form_submit_cleanup($form, $form_state);

    foreach ($form_state['profiles'] as $type => $profile) {
        $profile->uid = $account->uid;
        profile2_save($profile);
    }
     cache_clear_all();
     $account2 = user_load($account->uid);
     $redirect = "user/" . $form["#verlag"] . "/struktur";
     
     // create a new Team
     if($form["#team"] == 0){
        $teamname = $values["team_title"];
        // Create new Team-Entity
         $entity_type = 'team';
         $entity = entity_create($entity_type, array('type' => 'team'));
         $wrapper = entity_metadata_wrapper($entity_type, $entity);
         $entity -> title = $teamname;
         $entity -> created = time();
         $entity -> changed = time();
         $entity->field_verlag["und"][0]["uid"] = $form["#verlag"];
         $entity->field_team_verkaufsleiter["und"][0]["uid"] = $account -> uid;
         $entity->save();
         
         lk_note('user', "Neues Team <strong>" .  $teamname . "</strong> angelegt.");
         $team_id =  $entity -> id;
         $url = url("team/" . $team_id . "/edit");
         drupal_set_message('<div class="well">Das neue Team wurde erstellt. Bitte passen Sie nun die Einstellungen für das Team an.</div>');
         $redirect = 'team/' . $team_id . "/edit";
      } 
      else {
         $team_id = $form["#team"]; 
      }
        
      // User in das Team stecken
      $ua = user_load($account -> uid);
      $profile = profile2_load_by_user($ua, 'mitarbeiter');
      $profile -> field_team["und"][0]["target_id"] = $team_id;
      $profile -> field_mitarbeiter_verlag["und"][0]["uid"] = $form["#verlag"];
      
      profile2_save($profile);  
      cache_clear_all();
      
      lk_note('user', 'Account von ' . $account -> name . " (Pwd: ". $pwd ."/Email: ". $account -> mail .") wurde erstellt.");
      
     drupal_set_message("<div class=\"well\"><strong>Der Neue Benutzer wurde erstellt:</strong><br />
     Benutzer: <em>". $account -> name ."</em><br />
     Passwort: <code>". $pwd ."</code><br />
     E-Mail: " . $account -> mail . '</div>');
     
     drupal_goto($redirect);
}


function lk_user_add_vuegr_ma($form, &$form_state, $verlag){
    $form["#verlag"] = $verlag; 
   
     $form['#validate'][] = 'user_register_validate';
     $form['#validate'][] = 'lk_user_add_ma_validate';
    
  
       $form['help'] = array(
      '#markup' => '<div class="well well-white">
      <p><strong>Verkaufsübergreifender Mitarbeiter anlegen</strong></p>
      <ul>
        <li>Benutzername und Passwort werden generiert und nach dem Erstellen angezeigt.</li>
        <li>Der verkaufsübergreifender Mitarbeiter gehört keinem Team an.</li>
      </ul>
      </div>');
       
      $form['divider'] = array(
        '#markup' => '<hr />'
    );
    
    $form['mail'] = array(
        '#title' => 'E-Mail',
        '#description' => '',
        '#type' => 'textfield',
        '#required' => TRUE,
    );   
    
    $form['#submit'][] = 'lk_user_add_vuegr_ma_submit';
    $form_state['profiles']["main"] = profile2_create(array('type' => 'main'));
    $form_state['profiles']["mitarbeiter"] = profile2_create(array('type' => 'mitarbeiter'));
    profile2_attach_form($form, $form_state);  
    _lk_user_hide_profile_fields($form);
    
    
    
    
    
    $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Save'));
    
    
    return $form;
}



/**
 * Add a new MA
 * 
 * @param type $form
 * @param type $form_state
 * @param type $verlag
 * @param type $ausgabe
 * @return string
 */
function lk_user_add_ma($form, &$form_state, $verlag, $team){

    $form["#team"] = $team;
    $form["#verlag"] = $verlag;
  
    $form['#validate'][] = 'user_register_validate';
    $form['#validate'][] = 'lk_user_add_ma_validate';
    
    if($team == 0){
       drupal_set_title("Neuen Verkaufsleiter anlegen");
      
       $form['help'] = array(
      '#markup' => '<div class="well well-white">
      <p><strong>Neuen Verkaufsleiter anlegen</strong></p>
      <ul>
        <li>Benutzername und Passwort werden automatisch generiert und nach dem Erstellen angezeigt.</li>
        <li>Zusätzlich zu dem MA-Account legen Sie Team-Informationen fest.</li>
      </ul>
      </div>'
    );
     
     $form['team'] = array('#type' => 'fieldset', '#title' => "Team");
     $form['team']["team_info"] = array(
      '#markup' => '<div class="help-block">Legen Sie zuerst einen Team-Namen fest. 
      <em>Weitere Team-Informationen können Sie im Anschluss definieren.</em></div>'
     );
     
     $form['team']["team_title"] = array(
        '#required' => TRUE,
        '#title' => "Name des Teams",
        '#type' => 'textfield'
     );
    }
    else {
      drupal_set_title("Neuen Mitarbeiter anlegen");
            $form['help'] = array(
           '#markup' => '<div class="well well-white">
           <p><strong>Neuen Mitarbeiter anlegen</strong></p>
           <ul>
             <li>Benutzername und Passwort werden generiert und nach dem Erstellen angezeigt.</li>
             <li>Die Address-Informationen werden automatisch vom Verkaufsleiter übernommen, können jedoch angepasst werden.</li>
             </ul>
           </div>'
         );
    }
    
    $basis = null;
    $ausgaben = array();

    if($team){
        $team_obj = \LK\get_team($team);
        $leiter = \LK\get_user($team_obj ->getLeiter());
        $basis = _lk_user($leiter);
        $ausgaben = $team_obj -> getAusgaben();
        
        if(!$basis){
            drupal_goto("user/" . $verlag . "/struktur");
        }
   
        drupal_set_title('Neuen Mitarbeiten in Team "'. $team_obj -> getTitle() .'" anlegen');
    }
   
    
    $form['#submit'][] = 'lk_user_add_ma_submit';
   
    $form['mail'] = array(
        '#title' => 'E-Mail',
        '#description' => '',
        '#type' => 'textfield',
        '#required' => TRUE,
    );
    
    $form['divider'] = array(
        '#markup' => '<hr />'
    );
    
    $form_state['profiles']["main"] = profile2_create(array('type' => 'main'));
    $form_state['profiles']["mitarbeiter"] = profile2_create(array('type' => 'mitarbeiter'));
    profile2_attach_form($form, $form_state);  
   
    
    if($basis){
       $basis -> profile = profile2_load_by_user($basis);   
       
       // Copy the Profile Adresse from the Verkaufsleiter
       while(list($key, $val) = each($basis -> profile['main']->field_profile_adresse['und'][0])){
           $form['profile_main']['field_profile_adresse']['und'][0]['#address'][$key] = $val;
       }
    }   
   
    $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Save'));

    _lk_user_hide_profile_fields($form);
    
    // Ausgaben-Einschränkung, wenn kein Telefon-Team vorliegt
    if($team){
       if(!$team_obj ->isTelephone()){
          $form['profile_mitarbeiter']['field_ausgabe']['#access'] = true; 
          $form['profile_mitarbeiter']['field_ausgabe']['und']['#required'] = true;
          $form['profile_mitarbeiter']['field_ausgabe']['und']['#description'] = 'Bitte wählen Sie mind. eine verfügbare Ausgabe aus.';
       }
    }
   
    
    if($team == 0){
        
        
    }
   
  return $form;
}

function _lk_user_hide_profile_fields(&$form){
    $form['profile_mitarbeiter']['field_plz_sperre']['#access'] = false;     
    $form['profile_mitarbeiter']["field_mitarbeiter_verlag"]["#access"] = false;
    $form['profile_mitarbeiter']["field_mitarbeiter_verkaufsleiter"]["#access"] = false;
    $form['profile_mitarbeiter']['field_telefonmitarbeiter_uids']['#access'] = false;
    $form['profile_mitarbeiter']['field_telefonmitarbeiter_ausgabe']['#access'] = false;
    $form['profile_mitarbeiter']['field_telefonmitarbeiter']['#access'] = false;
    $form['profile_mitarbeiter']['field_ausgabe']['#access'] = false;
    $form['profile_mitarbeiter']['field_team']['#access'] = false;  
}




?>