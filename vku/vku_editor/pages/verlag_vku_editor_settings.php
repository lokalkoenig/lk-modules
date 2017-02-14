<?php

function vku_editor_page_verlag_settings($account){

  drupal_set_title('Einstellungen');

  $verlag = \LK\get_user($account);
  if(!$verlag ->getVerlagSetting('vku_editor', 0)){
    drupal_set_message('Dieses Feature ist im Moment nicht für Sie verfügbar.');
    drupal_goto('user');
  }

  $links = [
      ['url' => url('user/' . $verlag ->getUid() . '/vku_editor'), 'title' => "VKU-Dokumente"],
      ['url' => url('user/' . $verlag ->getUid() . '/vku_editor/settings'), 'title' => "Einstellungen", 'active' => TRUE],
  ];

  $form = drupal_get_form('vku_editor_page_verlag_settings_form', $verlag);
  $tabs = \LK\UI\Tabs::render($links);
  return $tabs . render($form);
}


/**
 * Settings-Form
 *
 * @param type $form
 * @param type $form_state
 * @param \LK\Verlag $verlag
 * @return type
 */
function vku_editor_page_verlag_settings_form($form, &$form_state, \LK\Verlag $verlag){

  $form['vku_editor_medien_collection_title'] = [
    '#type' => 'textfield',
    '#title' => 'Titel der Online-Medienkollektion',
    '#default_value' => $verlag ->getVerlagSetting('vku_editor_medien_collection_title', "Online-Medien"),
    '#description' => 'Dieser Titel wird beim Anlegen einer Medienkollektion als Vorgabe eingestellt, Maximal: 75 Zeichen',
    '#size' => 60,
    '#maxlength' => 75,
    '#required' => TRUE,
  ];

  $form['vku_editor_medien_collection_footnote'] = [
    '#type' => 'textfield',
    '#title' => 'Fußzeile',
    '#default_value' => $verlag ->getVerlagSetting('vku_editor_medien_collection_footnote', ""),
    '#description' => 'Vorgabe der Fußzeile der Online-Medienkollektion, Maximal: 120 Zeichen',
    '#size' => 60,
    '#maxlength' => 120,
    '#required' => FALSE,
  ];

  $form['#submit'][] = 'vku_editor_page_verlag_settings_form_submit';
 
  return \LK\User\Settings\Manager::toForm($form, $form_state, $verlag);
}

function vku_editor_page_verlag_settings_form_submit(&$form, &$form_state){
  $form_state['redirect'] = 'user/' . $form['#verlag'] ->getUid() . '/vku_editor';
}
