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
 
$themes["lk_user_change_ausgaben"] = [
  'template' => 'templates/lk_user_change_ausgaben', // your template file called custompage.tpl.php
  'variables' => [
      'ausgaben' => [],
      'link' => null,
  ],
];
  
 
 $themes["lk_user_block_left_anonym"] = array(
            'template' => 'templates/lkuserblockleft_ano', // your template file called custompage.tpl.php
            'variables' => array(),
        );
 
 
  $themes["lkmesglink"] = array(
            'template' => 'templates/lkmesglink', // your template file called custompage.tpl.php
            'variables' => array('account' => NULL),
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
  $themes["lokalkoenig_user_new_dashboard"] = array(
            'template' => 'inc/lokalkoenig_user_new_dashboard', // your template file called custompage.tpl.php
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

  return $items;         
}
