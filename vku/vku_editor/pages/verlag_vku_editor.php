<?php

/* 
 * @file
 * Callback-Page for the user/%/vku_editor
 */

/**
 * 
 * @param sdtClass $account
 * @return array
 */
function vku_editor_page_verlag_cb($account){
  
  drupal_set_title('Vorlagen für Verkaufsunterlagen');
  lk_set_icon('file');
  
  $verlag = \LK\get_user($account);
  if(!$verlag ->getVerlagSetting('vku_editor', 0)){
    drupal_set_message('Dieses Feature ist im Moment nicht für Sie verfügbar.');  
    drupal_goto('user');
  }

  $manager = new LK\VKU\Editor\Manager();
  $manager -> setAccount($verlag);
  $presets = $manager->getPresetsAvailable();
  $documents = vku_editor_verlag_documents_themed($verlag);
  
  drupal_add_library('vku_editor', 'vku_inlace_editor');
  drupal_add_js(drupal_get_path('module', 'vku_editor'). '/js/verlag_controller.js');
  
  $theme = theme('vku_editor_verlag',[
      'account' => $account,
      'presets' => $presets,
      'documents' => $documents,
  ]);
  
  return $theme;   
}

