<?php

function lokalkoenig_user_verlag_theme(){

 $themes = array ('verlaguser' => array(
            'template' => 'templates/verlaguser', // your template file called custompage.tpl.php
            'variables' => array(
              'accounts' => NULL
            ),
        ),
    );
  
  
  $themes["team_mitarbeiter"] = array(
            'template' => 'templates/team_mitarbeiter', // your template file called custompage.tpl.php
            'variables' => array(
              'accounts' => NULL,
              'leiter' => NULL,
              'team' => NULL,
              'form' => NULL
            )
    );
    
    
    $themes["lk_ausgaben_admin"] = array(
            'template' => 'templates/lk_ausgaben_admin', // your template file called custompage.tpl.php
            'variables' => array(
              'bereiche' => NULL
            )
    );
    
    
    
     $themes["bereichselect"] = array(
            'template' => 'templates/bereichselect', // your template file called custompage.tpl.php
            'variables' => array(
              'account' => NULL,
            )
    );
    
    
    $themes["verlagsstruktur"] = array(
            'template' => 'templates/verlagsstruktur', // your template file called custompage.tpl.php
            'variables' => array(
              'verlag' => NULL,
              'items' => NULL,
            )
    );
     
 
return $themes;
}


function lokalkoenig_user_verlag_menu(){

 $items['user/%user/addaccount'] = array(
      'access callback' => 'lokalkoenig_user_verlag_check_user_access_is_verlag',
      'access arguments' => array(1),
      'file' => 'pages/verlagsstruktur.inc',
      'page callback' => 'lokalkoenig_user_verlag_addaccount',
      'page arguments' => array(1),
      'title' => 'Account hinzufügen',
      'type' => MENU_CONTEXT_NONE);

  $items['team/%'] = array(
      'access callback' => 'user_is_logged_in',
      'file' => 'pages/team.inc',
      'page callback' => 'lokalkoenig_user_team_overview',
      'page arguments' => array(1),
      'title' => 'Team',
      'type' => MENU_CONTEXT_NONE);


   $items['user/%user/struktur'] = array(
      'access callback' => 'lokalkoenig_user_verlag_check_user_access_is_verlag',
      'access arguments' => array(1),
      'file' => 'pages/verlagsstruktur.inc',
      'page callback' => 'lokalkoenig_user_verlag_struktur',
      'page arguments' => array(1),
      'title' => 'Verlagsstruktur',
      'type' => MENU_CONTEXT_NONE);  
      
    $items['user/%user/ausgaben'] = array(
      'access callback' => 'lokalkoenig_user_verlag_check_user_access_is_verlag',
      'access arguments' => array(1),
      'file' => 'pages/verlagsstruktur.inc',
      'page callback' => 'lokalkoenig_user_verlag_ausgaben',
      'page arguments' => array(1),
      'title' => 'Ausgaben',
      'type' => MENU_CONTEXT_NONE);     
  
  $items['user/%user/usersearch/%'] = array(
      'access callback' => 'lokalkoenig_user_verlag_check_user_access_is_verlag',
      'access arguments' => array(1),
      'file' => 'pages/usersearch.inc',
      'page callback' => '_lokalkoenig_user_verlag_searchusers',
      'page arguments' => array(1),
      'title' => 'Unteraccounts',
      'type' => MENU_CONTEXT_NONE);
   
  
    $items['user/%user/setplz'] = array(
      'access callback' => 'lk_is_telefonmitarbeiter',
      'access arguments' => array(1),
      'file' => 'pages/telefonmitarbeiter.inc',
      'page callback' => 'drupal_get_form',
      'page arguments' => array('telefonmitarbeiter_set_plz', 1),
      'title' => 'Postleitzahlen festlegen',
      'type' => MENU_CONTEXT_NONE);  


    $items['dashboard'] = array(
      'access callback' => 'lk_is_moderator',
      'file' => 'inc/new_dashboard.inc',
      'page callback' => 'lokalkoenig_user_new_dashboard',
      'title' => 'Dashboard',
      'type' => MENU_LOCAL_TASK);

      return $items;    
} 


function lokalkoenig_user_menu_alter(&$items) {
   $items["user/%user"]["access callback"] = 'lk_check_user_profile_access'; 
   $items["user/%user"]["page callback"] = 'lk_user_profile_page';
}
