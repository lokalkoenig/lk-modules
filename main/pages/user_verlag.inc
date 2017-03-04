<?php

function lokalkoenig_user_verlag_admin($account){

  $verlag = \LK\get_user($account);
  drupal_set_title('Verlagseinstellung');

  drupal_add_js(['lokalkoenig_admin' => ['preview_url' => url('user/' . $verlag ->getUid() . '/testpdf')]], ['type' => 'setting']);
  $form = drupal_get_form('lokalkoenig_user_verlag_admin_settings_form', $verlag);

  return $form;
}

function lokalkoenig_user_verlag_admin_settings_form($form, &$form_state, \LK\Verlag $verlag){

  $form['#attached']['css'] = array(
    drupal_get_path('module', 'field_group') . '/horizontal-tabs/horizontal-tabs.css',
  );
  $form['#attached']['js'] = array(
    drupal_get_path('module', 'field_group') . '/horizontal-tabs/horizontal-tabs.js',
    drupal_get_path('module', 'lokalkoenig_admin') . '/js/test-pdf-link.js',
  );


  $form['settings'] = array(
    '#type' => 'vertical_tabs',
  );

  $form['vku_settings'] = array(
    '#type'  => 'fieldset',
    '#title' => ('VKU-Handhabung'),
    '#group' => 'settings',
  );

  $form['vku_settings']['testverlag'] = [
    '#type' => 'select',
    '#title' => 'Testverlag',
    '#default_value' => $verlag ->getVerlagSetting('testverlag', 0),
    '#options' => [1 => 'Ja (Testverlag)', 0 => "Nein (Normaler Verlag)"],
    '#description' => 'Alle Benutzer im Testverlag können keine Lizenzen erwerben.',
  ];

  $form['vku_settings']['anzeige_des_ma_protokolls'] = [
    '#type' => 'select',
    '#title' => 'Anzeige des MA-Protokolls',
    '#default_value' => $verlag ->getVerlagSetting('anzeige_des_ma_protokolls', 0),
    '#options' => [1 => 'Anzeigen', 0 => "Nicht anzeigen"],
    '#description' => 'Das Mitarbeiter-Protokoll gibt dem Verlag und dem Verkaufsleiter die Möglichkeit die Aktivitäten seiner Mitarbeiter zu beobachten.',
  ];

  $form['vku_settings']['sperrung_vku'] = [
    '#type' => 'textfield',
    '#title' => 'Sperrung VKU (Lizenzen/PLZ-Sperre)',
    '#default_value' => $verlag ->getVerlagSetting('sperrung_vku', 365),
    '#element_validate' => array('element_validate_integer_positive'),
    '#description' => 'in Tagen anzugeben. Standardwert ist ein Jahr also 365 Tage.',
  ];

  $form['vku_settings']['sperrung_vku_hinweis'] = [
    '#type' => 'textfield',
    '#title' => 'Hinweis VKU Verwendung',
    '#default_value' => $verlag ->getVerlagSetting('sperrung_vku_hinweis', 10),
    '#element_validate' => array('element_validate_integer_positive'),
    '#description' => 'Wie viele Tage soll der Hinweis angezeigt werden, dass die VKU von einem anderem Mitarbeiter verwendet wird. (Standard-Wert: 10)',
  ];

  $form['vku_settings']['sperrung_vku_pdf'] = [
    '#type' => 'textfield',
    '#title' => 'Kurzzeitsperre VKU',
    '#element_validate' => array('lokalkoenig_user_verlag_admin_validate_vku_hinweis'),
    '#default_value' => $verlag ->getVerlagSetting('sperrung_vku_pdf', 0),
    '#description' => 'Nachdem eine Verkaufsunterlage generiert wurde, werden die Kampagnen für die Ausgaben für X Tage für den entsprechenden Nutzer gesperrt. Die Eingabe 0 bedeutet, dass die Funktion für den Verlag deaktiviert ist. (Maximalwert: 14, für 14 Tage).',
  ];

  ///////////////////////////////////////////

  $form['vku2'] = array(
    '#type'  => 'fieldset',
    '#title' => ('VKU-Erweiterungen'),
    '#group' => 'settings',
  );

  $form['vku2']['vku_2'] = [
    '#type' => 'select',
    '#title' => 'VKU2.0-Editor freischalten',
    '#default_value' => $verlag ->getVerlagSetting('vku_2', 0),
    '#options' => [1 => 'Ja', 0 => "Nein"],
    '#description' => 'VKU 2.0 Editor für den Verlag freischalten.',
  ];

  $form['vku2']['vku_editor'] = [
    '#type' => 'select',
    '#title' => 'VKU2.0-Dokumente freischalten',
    '#default_value' => $verlag ->getVerlagSetting('vku_editor', 0),
    '#options' => [1 => 'Ja', 0 => "Nein"],
    '#description' => 'VKU 2.0 Dokumente für den Verlag freischalten.',
  ];

  $manager = new \LK\VKU\Editor\Manager();
  $documents = $manager->getPresetsAvailable();

  $array = [];
  while(list($key, $val) = each($documents)){
    $array[$key] = $val['title'];
  }

  $form['vku2']['vku_editor_dokumente'] = [
    '#type' => 'select',
    '#title' => 'VKU2.0-Dokumente freischalten',
    '#default_value' => $verlag ->getVerlagSetting('vku_editor_dokumente', []),
    '#options' => $array,
    '#multiple' => true,
    '#size' => count($array),
    '#description' => 'Wählen Sie die freigegebenen Editor-Vorlagen.',
  ];
 
  /////////////////////////////////////////////////////////////

  $form['vku'] = array(
    '#type'  => 'fieldset',
    '#title' => ('VKU-Export-Einstellungen'),
    '#group' => 'settings',
  );

  $form['vku']['vku_hintergrundfarbe'] = [
    '#type' => 'textfield',
    '#title' => 'Hintergrundfarbe',
    '#element_validate' => array('lokalkoenig_user_verlag_admin_validate_color'),
    '#default_value' => lokalkoenig_user_verlag_admin_sanitize_color($verlag ->getVerlagSetting('vku_hintergrundfarbe', "ffffff", 'jquery_colorpicker')),
    '#description' => 'Wird im Header und im Footer verwendet.',
    '#size' => 7,
    '#maxlength' => 7,
  ];

  $form['vku']['vku_hintergrundfarbe_titel'] = [
    '#type' => 'textfield',
    '#element_validate' => array('lokalkoenig_user_verlag_admin_validate_color'),
    '#title' => 'Hintergrundfarbe Titel-Seite',
    '#default_value' => lokalkoenig_user_verlag_admin_sanitize_color($verlag ->getVerlagSetting('vku_hintergrundfarbe_titel', "646464", 'jquery_colorpicker')),
    '#description' => 'Wird als Hintergrundfarbe auf der Titel-Seite verwendet.',
    '#size' => 7,
    '#maxlength' => 7,
  ];

   $form['vku']['vku_vordergrundfarbe_titel'] = [
    '#type' => 'textfield',
    '#element_validate' => array('lokalkoenig_user_verlag_admin_validate_color'),
    '#title' => 'Text-Farbe Titel-Seite',
    '#default_value' => lokalkoenig_user_verlag_admin_sanitize_color($verlag ->getVerlagSetting('vku_vordergrundfarbe_titel', "ffffff", 'jquery_colorpicker')),
    '#description' => 'Wird als Vordergrundfarbe auf der Titel-Seite verwendet.',
    '#size' => 7,
    '#maxlength' => 7,
  ];

  $form['vku']['verlag_font'] = [
    '#type' => 'select',
    '#title' => 'Verlag Schriftart',
    '#default_value' => $verlag ->getVerlagSetting('verlag_font', 'lato'),
    '#options' => ['lato' => 'Lato (Standard)', 'arial' => "Arial"],
    '#description' => 'Schriftart in der PDF',
  ];

  $form['vku']['verlag_logo_position'] = [
    '#type' => 'select',
    '#title' => 'Position des Logos',
    '#default_value' => $verlag ->getVerlagSetting('verlag_logo_position', 'left'),
    '#options' => ['left' => 'Links (Standard)', 'right' => "Rechts"],
    '#description' => 'Die Position des Logos in der Verkaufsunterlage',
  ];

  $form['vku']['verlag_kontakt_vorlage'] = [
    '#type' => 'select',
    '#title' => 'Kontaktvorlage',
    '#default_value' => $verlag ->getVerlagSetting('verlag_kontakt_vorlage', 'left'),
    '#options' => ['default' => 'Lokalkönig Vorlage (Standard)', 'version2' => "Version 2"],
    '#description' => 'Die Vorlage der Kontaktseite der Verkaufsunterlage.',
  ];

  $form['vku']['verlag_online_formate'] = [
    '#type' => 'select',
    '#title' => 'Online Formate',
    '#default_value' => $verlag ->getVerlagSetting('verlag_online_formate', 'no'),
    '#options' => ['no' => 'Größen anzeigen (Standard, z.B. 150x150 Pixel)', 'yes' => "Format Bezeichnungen anzeigen (z.B. Small Square)", 'no-label' => 'Keine Bezeichnungen anzeigen'],
    '#description' => 'Diese Einstellung ist nur für de Verkaufsunterlagen.',
  ];

  return \LK\User\Settings\Manager::toForm($form, $form_state, $verlag);
}


function lokalkoenig_user_verlag_admin_validate_vku_hinweis($element, &$form_state) {
  $value = $element['#value'];
  if ($value !== '' && (!is_numeric($value) || intval($value) != $value || $value <= -1 || $value > 14)) {
    form_error($element, "Bitte den Maximalwer beachten.");
  }
}

/**
 * Sanitize the color value
 *
 * @param string $color
 * @return string
 */
function lokalkoenig_user_verlag_admin_sanitize_color($color) {

  if($color[0] !== '#'){
    return '#' . $color;
  }

  return $color;
}


function lokalkoenig_user_verlag_admin_validate_color($element, &$form_state) {
  $value = $element['#value'];
  $colorCode = ltrim($value, '#');
  if (!ctype_xdigit($colorCode)) {
    form_error($element, "Der Farbcode ist kein Hexadezimal-Wert.");
  }
}
