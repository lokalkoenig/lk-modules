<?php


function  lokalkoenig_user_admin_paths() {
  $paths = array(
    //'user/*/edit' => false,  
    'user/*/edit/verlag' => false,
     'user/*/edit/mitarbeiter' => false,
     'user/*/edit/main' => false,
    'user/*/edit' => false,
     'user/*/neuigkeiten/*/edit' => false
  );
  
  return $paths;
}



function lokalkoenig_user_theme(){

 $themes = array ('lk_user_block_left' => array(
            'template' => 'templates/lkuserblockleft', // your template file called custompage.tpl.php
            'variables' => array(
              'account' => NULL
            ),
        ),
    );
  
 $themes["lk_user_block_left_anonym"] = array(
            'template' => 'templates/lkuserblockleft_ano', // your template file called custompage.tpl.php
            'variables' => array(),
        );
 
 
  $themes["lkmesglink"] = array(
            'template' => 'templates/lkmesglink', // your template file called custompage.tpl.php
            'variables' => array('account' => NULL),
        );
 
  $themes["lkteam_left"] = array(
            'template' => 'templates/lkteam_left', // your template file called custompage.tpl.php
            'variables' => array('team' => NULL),
        );
 
 
 $themes["lk_user_block_top"] = array(
            'template' => 'templates/lk_user_block_top', // your template file called custompage.tpl.php
            'variables' => array("form" => NULL, "account" => NULL),
        );
 
 
  $themes["mitarbeiterinfo"] = array(
            'template' => 'templates/mitarbeiterinfo', // your template file called custompage.tpl.php
            'variables' => array(
              'account' => NULL
            )
    );
 
  $themes["mitarbeiterinfo_vkl"] = array(
            'template' => 'templates/mitarbeiterinfo_vkl', // your template file called custompage.tpl.php
            'variables' => array(
              'account' => NULL
            )
    );
  
return $themes;
}

function lokalkoenig_user_menu(){
 $items['user/%user/kampagnen'] = array(
      'access callback' => 'lokalkoenig_user_check_user_access_is_agentur',
      'access arguments' => array(1),
      'file' => 'user_kampagnen.inc',
      'page callback' => 'lokalkoenig_user_kampagnen',
      'page arguments' => array(1),
      'title' => 'Meine Kampagnen',
      'type' => MENU_LOCAL_TASK);
 
 
 $items['user/%user/info'] = array(
      'access callback' => 'lk_is_moderator',
      'file' => 'pages/admin.inc',
      'page callback' => 'lokalkoenig_user_info',
      'page arguments' => array(1),
      'title' => 'Administrieren',
      'type' => MENU_LOCAL_TASK);
 
 
   $items['verlage/addverlag'] = array(
      'access callback' => 'lk_is_moderator',
      'file' => 'user_admin.inc',
      'page callback' => 'lokalkoenig_user_verlag_create',
      'page arguments' => array(1),
      'title' => 'Verlag anlegen',
      'type' => MENU_LOCAL_TASK);

   
      return $items;    
      
}