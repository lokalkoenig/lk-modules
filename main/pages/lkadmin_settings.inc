<?php

function lokalkoenig_admin_settings_page(){

  $form = array();
  $form['#attached']['css'] = array(
    drupal_get_path('module', 'field_group') . '/horizontal-tabs/horizontal-tabs.css',
  );
  $form['#attached']['js'] = array(
    drupal_get_path('module', 'field_group') . '/horizontal-tabs/horizontal-tabs.js',
  ); 
  $form['settings'] = array(
     '#type' => 'vertical_tabs',
  );
  
  $form['merkliste'] = array(
    '#type'  => 'fieldset',
    '#title' => ('Merkliste'),
    '#group' => 'settings',
  );
  
   $form['merkliste']["lk_merkliste_info"] = array(
      '#title' => 'Hilfe zur Merkliste',
      '#type' => 'textarea',
      '#default_value' => variable_get('lk_merkliste_info', '')
    );
   
  /////////// SEARCH 
  $form['search'] = array(
    '#type'  => 'fieldset',
    '#title' => ('Suche'),
    '#group' => 'settings',
   );
   
   $form['search']["lk_search_help"] = array(
      '#title' => 'Hilfe zur Suche bei den Kampagnen',
      '#type' => 'textarea',
      '#default_value' => variable_get('lk_search_help', '')
    );
    
    $form['search']["lk_search_noresult"] = array(
      '#title' => 'Suche, wenn keine Ergebnisse, E-Mail-Vorlage',
      '#type' => 'textarea',
      '#default_value' => variable_get('lk_search_noresult', '')
    );
    
    $form['search']["lk_search_noresult_text"] = array(
      '#title' => 'Suche, wenn keine Ergebnisse, Text links',
      '#type' => 'textarea',
      '#default_value' => variable_get('lk_search_noresult_text', '')
    );

    $form['search']["lk_search_formrequest"] = array(
      '#title' => 'Text in der Suchanfrage',
      '#type' => 'textarea',
      '#default_value' => variable_get('lk_search_formrequest', '')
    );

    
    
    ///// Kampagnen
    
    $form['kampagnen'] = array(
    '#type'  => 'fieldset',
    '#title' => ('Kampagnen'),
    '#group' => 'settings',
   );
    
    $form['kampagnen']["mark"] = array('#markup' => '<h2>E-Mails bei Kampagnen-Status-Änderungen</h2>
  <ul>
    <li>!kampa_title - Titel der Kampagne</li>
    <li>!kampa_link - Link zum Titel</li>
    <li>!message_to - User-Name wo die Message hingeht</li>
    <li>!message_from - User-Name des Admins der sended</li>
  </ul>
  ');
    
    
  $possibilities = array();
  $possibilities["new"] = array(
    'title' => 'Editiermodus',
    'class' => 'primary',
    'desc' => 'Die Agentur kann die Kampagne editieren.'
  );
   
  $possibilities["canceled"] = array(
    'title' => 'Anzeige mangelhaft',
    'class' => 'warning',
    'desc' => 'Die Agentur kann die Editierung fortsetzen und wird auf Mängel hingewiesen. Die Agentur kann daraufhin die Kampagne wieder editieren.'
  );
  
  
  $possibilities["proof"] = array(
    'title' => 'Anzeige eingereicht',
    'class' => 'warning',
    'desc' => 'Die Kampagne soll überprüft werden und kann nicht von der Agentur verändert werden.'
  );
  
   $possibilities["deleted"] = array(
    'title' => 'Kampagne verwerfen',
    'class' => 'danger',
    'desc' => 'Die Kampagne wird abgelehnt (optional kann die Kampagne auch wieder zum Editieren freigeschalten werden mit einer Begründung).'
  );
  
    $possibilities["published"] = array(
    'title' => 'Kampagne freischalten',
    'class' => 'success',
    'desc' => 'Die Kampagne abschließend für die Verlage freigegeben. Die Agentur kann die Kampagne dann nicht mehr bearbeiten.'
  );
  
  while(list($key, $val) = each($possibilities)){
    $form['kampagnen']["lk_kampa_messaging_subj_" . $key] = array(
      '#title' => 'Betreff E-Mail ' . $val["title"],
      '#type' => 'textfield',
      '#default_value' => variable_get('lk_kampa_messaging_subj_' . $key, '')
    );
    
    
     $form['kampagnen']["lk_kampa_messaging_text_" . $key] = array(
      '#title' => 'Text E-Mail ' . $val["title"],
      '#type' => 'textarea',
      '#default_value' => variable_get('lk_kampa_messaging_text_' . $key, '')
    );
  }
  
  /////////////// VKU
  $form['vku'] = array(
    '#type'  => 'fieldset',
    '#title' => ('VKU'),
    '#group' => 'settings',
   );
  
   $form['vku']["lk_vku_info"] = array(
      '#title' => 'Hilfe zur Verkaufsunterlage Oben',
      '#type' => 'textarea',
      '#default_value' => variable_get('lk_vku_info', '')
    );

    $form['vku']["lk_vku_add_max"] = array(
      '#title' => 'Maximale Anzahl von Kampagnen die einer VKU zugeorndet werden können',
      '#type' => 'textfield',
      '#default_value' => variable_get('lk_vku_add_max', '5')
    );
   
   
    $form['vku']["info1"]["#markup"] = '<hr /><h4>Mail nachdem eine VKU erfolgreich generiert wurde</h4>';
   
   
    $form['vku']["lk_vku_ready_email_subject"] = array(
      '#title' => 'Betreff',
      '#type' => 'textfield',
      '#default_value' => variable_get('lk_vku_ready_email_subject', '')
    );
    
   $form['vku']["lk_vku_ready_email"] = array(
      '#title' => 'Nachricht',
      '#type' => 'textarea',
      '#default_value' => variable_get('lk_vku_ready_email', '')
    );
 
    
    
    $form['vku']["info"]["#markup"] = '<hr /><h4>Download der Lizenzen</h4>';
    
     $form['vku']["lk_vku_max_download"] = array(
      '#title' => 'Maximale Anzahl der Downloads einer Lizenz',
      '#type' => 'textfield',
      '#default_value' => variable_get('lk_vku_max_download', 10)
    );
    
    
    $form['vku']["lk_vku_max_download_time"] = array(
      '#title' => 'Maximale Anzahl von Tagen, nachdem man nach dem Bestellen der Lizenz die Downloads tätigen kann',
      '#type' => 'textfield',
      '#default_value' => variable_get('lk_vku_max_download_time', 30)
    );
    
    
    $form['vku']["lk_vku_info_text_downloadfile"] = array(
      '#title' => 'Infotext-Datei-Text, dem einen ZIP mit hinzugefügt wird',
      '#type' => 'textarea',
      '#default_value' => variable_get('lk_vku_info_text_downloadfile', '')
    );
  
  
  return system_settings_form($form);
}


?>