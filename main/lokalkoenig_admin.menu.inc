<?php

/**
 * 
 * @return string
 */
function lokalkoenig_admin_menu(){
     
  // Tests
  $items['backoffice'] = [
    'access callback' => 'lk_is_moderator',
    'page callback' => 'lokalkoenig_main_dashboard',
    'menu_name' => 'navigation',
    'file' => "pages/admin_dashboard.php",
    'title' => 'Home',
    'weight' => -20,
    'type' => MENU_NORMAL_ITEM,
  ];

  ////////////// STATS ////////////////
  $items['backoffice/settings'] = [
    'access callback' => 'lk_is_moderator',
    'page callback' => 'drupal_get_form',
    'page arguments' => ['lokalkoenig_admin_settings_page'],
    'file' => "pages/lkadmin_settings.inc",
    'title' => 'Portal-Einstellungen',
    'menu_name' => 'navigation',
    'type' => MENU_NORMAL_ITEM,
  ];

  $items['backoffice/stats'] = [
    'access callback' => 'lk_is_moderator',
    'page callback' => 'lokalkoenig_admin_show_stats',
    'title' => 'Statistiken',
    'file' => "pages/stats_overview.inc",
    'menu_name' => 'navigation',
    'type' => MENU_NORMAL_ITEM,
  ];
  
  $items['backoffice/stats/overview'] = [
    'title' => 'Übersicht',
    'type' => MENU_DEFAULT_LOCAL_TASK,
    'weight' => -10
  ];

  $items['backoffice/stats/pakete'] = [
    'title' => 'Paketgrößen',
    'page callback' => '\\LK\\Admin\\StatsController::pagePakete',
    'access arguments' => array('access administration menu'),
    'menu_name' => 'navigation',
    'type' => MENU_LOCAL_TASK,
  ];

  $items['backoffice/stats/usage'] = [
    'title' => 'Speichernutzung',
    'page callback' => '\\LK\\Admin\\StatsController::pageStats',
    'access arguments' => array('access administration menu'),
    'menu_name' => 'navigation',
    'type' => MENU_LOCAL_TASK,
  ];

  $items['backoffice/test'] = [
    'access callback' => 'lk_is_moderator',
    'page callback' => '\\LK\\Tests\\Controller\\TestController::run',
    'menu_name' => 'navigation',
    'title' => 'Tests',
    'type' => MENU_NORMAL_ITEM
  ];

  // Lizenz admin
  $items['backoffice/logbuch/editlizenz/%'] = array(
      'access callback' => 'lk_is_moderator',
      'page callback' => 'vku_vku_admin_edit',
      'page arguments' => array(3),
      'title' => 'Lizenz editieren',
      'file' => "pages/logbuch_editlizenz.inc",
      'type' => MENU_CONTEXT_PAGE);
  
 $items['user/%user/info'] = array(
      'access callback' => 'lk_is_moderator',
      'file' => 'pages/user_admin.inc',
      'page callback' => 'lokalkoenig_user_info',
      'page arguments' => array(1),
      'title' => 'Administrieren',
      'type' => MENU_LOCAL_TASK);

 $items['user/%user/testpdf'] = [
  'access callback' => 'lk_is_moderator',
  'file' => 'pages/verlag_test_pdf.inc',
  'page callback' => 'lokalkoenig_user_test_pdf',
  'page arguments' => array(1),
  'title' => 'Administrieren',
  'type' => MENU_CONTEXT_NONE,
 ];


 $items['user/%user/verlag'] = array(
      'access callback' => 'lokalkoenig_admin_menu_is_verlag',
      'access arguments' => [1],
      'file' => 'pages/user_verlag.inc',
      'page callback' => 'lokalkoenig_user_verlag_admin',
      'page arguments' => array(1),
      'title' => 'Verlagseinstellungen',
      'type' => MENU_LOCAL_TASK);

 
   $items['backoffice/users/addverlag'] = [
      'access callback' => 'lk_is_moderator',
      'file' => 'pages/user_verlag_create.inc',
      'page callback' => 'lokalkoenig_user_verlag_create',
      'title' => 'Verlag anlegen',
      'weight' => 100,
      'type' => MENU_LOCAL_TASK,
    ];

  $items['backoffice/abrechnung'] = [
    'access callback' => 'lk_is_moderator',
    'file' => 'pages/lkstats.inc',
    'file path' => drupal_get_path('module','lokalkoenig_user'),
    'page callback' => 'lokalkoenig_user_overallpage',
    'title' => 'Abrechnung',
    'menu_name' => 'navigation',
    'type' => MENU_NORMAL_ITEM,
  ];
 
    return $items;
}

function lokalkoenig_admin_menu_is_verlag($account){

  if(!lk_is_moderator()){
    return FALSE;
  }

  if(!lk_is_verlag($account)){
    return FALSE;
  }

  return TRUE;
}

