<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function vku_editor_page_verlag_cb($account){
  
  drupal_set_title('Vorlagseigene Dokumente');
  
  drupal_add_library('vku_editor', 'vku_inlace_editor');
  drupal_add_js(drupal_get_path('module', 'vku_editor'). '/js/verlag_controller.js');
  
  $theme = theme('vku_editor_verlag',[
      'account' => $account
  ]);
  
return $theme;   
}

