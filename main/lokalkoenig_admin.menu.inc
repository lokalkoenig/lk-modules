<?php

/**
 * 
 * @return string
 */
function lokalkoenig_admin_menu(){
 
    // add Menu
    // lkadmin/stats
     $items['lkadmin/overview'] = array(
      'access callback' => 'lk_is_moderator',
      'page callback' => 'lokalkoenig_admin_show_stats',
      'title' => 'Statistiken',
      'file' => "pages/stats_overview.inc",
      'type' => MENU_CONTEXT_NONE); 
     
     $items['lkadmin/test'] = array(
      'access callback' => 'lk_is_moderator',
      'page callback' => '\\LK\\Tests\\Controller\\TestController::run',
      'title' => 'Tests',
      'type' => MENU_CONTEXT_NONE); 
             
     $items['lkadmin/settings'] = array(
      'access callback' => 'lk_is_moderator',
      'page callback' => 'drupal_get_form',
      'page arguments' => array('lokalkoenig_admin_settings_page'),
      'file' => "pages/lkadmin_settings.inc",
      'title' => 'Tests',
      'type' => MENU_CONTEXT_NONE); 
    
     $items['lkadmin/stats'] = array(
        'title' => 'Lokalkoenig Statistiken',
       'page callback' => '\\LK\\Admin\\StatsController::pageStats',
        'access arguments' => array('access administration menu'),
        'type' => MENU_CONTEXT_NONE,
    );  
    
    $items['lkadmin/pakete'] = array(
        'title' => 'Paketgrößen',
         'page callback' => '\\LK\\Admin\\StatsController::pagePakete',
        'access arguments' => array('access administration menu'),
        'type' => MENU_CONTEXT_NONE,
    ); 
    
  // Lizenz admin
  $items['logbuch/editlizenz/%'] = array(
      'access callback' => lk_is_moderator(),
      'page callback' => 'vku_vku_admin_edit',
      'page arguments' => array(2),
      'title' => 'Lizenz editieren',
      'file' => "pages/logbuch_editlizenz.inc",
      'type' => MENU_CONTEXT_NONE); 
     
  // Lizenz admin
  $items['logbuch/editlizenz/%'] = array(
      'access callback' => lk_is_moderator(),
      'page callback' => 'vku_vku_admin_edit',
      'page arguments' => array(2),
      'title' => 'Lizenz editieren',
      'file' => "pages/logbuch_editlizenz.inc",
      'type' => MENU_CONTEXT_NONE); 
  
  
 $items['user/%user/info'] = array(
      'access callback' => 'lk_is_moderator',
      'file' => 'pages/user_admin.inc',
      'page callback' => 'lokalkoenig_user_info',
      'page arguments' => array(1),
      'title' => 'Administrieren',
      'type' => MENU_LOCAL_TASK);
 
 
   $items['verlage/addverlag'] = array(
      'access callback' => 'lk_is_moderator',
      'file' => 'pages/user_verlag_create.inc',
      'page callback' => 'lokalkoenig_user_verlag_create',
      'page arguments' => array(1),
      'title' => 'Verlag anlegen',
      'type' => MENU_LOCAL_TASK);
 
    return $items;
}
