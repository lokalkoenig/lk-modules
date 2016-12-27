<?php

function lokalkoenig_user_verlag_views_data() {
  
   $data['lk_search_history']['table']['group'] = ('LK Search History');

  $data['lk_search_history']['table']['base'] = array(
    'field' => 'id',
    'title' => ('LK Search History'), 
    'help' => ('ID'), 
    'weight' => -10,
  );

   $data['lk_search_history']['table']['join'] = array(
    'user' => array(
      'left_field' => 'uid', 
      'field' => 'uid',
    ),
  );

  $data['lk_search_history']['id'] = array(
    'title' => ('ID'), 
    'help' => ('Die ID des Log-Eintrages'),
    'field' => array(
      'handler' => 'views_handler_field', 
      'click sortable' => true,
    ), 
  );

  
  $data['lk_search_history']['uid'] = array(
    'title' => ('User-ID'), 
    'help' => ('Some example content that references a User-ID.'),
    'relationship' => array(
      'base' => 'users',
      'field' => 'uid',
      'handler' => 'views_handler_relationship', 
      'label' => ('User ID'),
    ),
  );
  
 
   $data['lk_search_history']['search_count'] = array(
    'title' => t('Search count'),
    'help' => t('Just a numeric field.'),
    'field' => array(
      'handler' => 'views_handler_field_numeric',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_numeric',
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
  );
  
    $data['lk_search_history']['created'] = array(
    'title' => ('Datum des Eintrags'), 
    'help' => ('Datum des Eintrags'), 
    'field' => array(
      'handler' => 'views_handler_field_date', 
      'click sortable' => TRUE,
    ), 
    'sort' => array(
      'handler' => 'views_handler_sort_date',
    ), 
    'filter' => array(
      'handler' => 'views_handler_filter_date',
    ),
  );
  
  
  $data['lk_search_history']['search_string'] = array(
    'title' => ('Suche'), 
    'field' => array(
      'handler' => 'views_handler_field', 
      'click sortable' => False,
    ), 
    'sort' => array(
      'handler' => 'views_handler_sort',
    ), 
    'filter' => array(
      'handler' => 'views_handler_filter_string',
    ), 
    'argument' => array(
      'handler' => 'views_handler_argument_string',
    ),
  );
  
  
  $data['lk_search_history']['search_text'] = array(
    'title' => ('Such-Link'), 
    'field' => array(
      'handler' => 'views_handler_field', 
      'click sortable' => False,
    ), 
    'sort' => array(
      'handler' => 'views_handler_sort',
    ), 
    'filter' => array(
      'handler' => 'views_handler_filter_string',
    ), 
    'argument' => array(
      'handler' => 'views_handler_argument_string',
    ),
  );
  
  
  
  
  
  
  
  
  
  
  $data['lk_verlag_log']['table']['group'] = ('LK Verlag Log');

  $data['lk_verlag_log']['table']['base'] = array(
    'field' => 'log_id',
    'title' => ('LK Verlag Log'), 
    'help' => ('LK Verlag Log speichert die Aktionen der User in einer Tabelle'), 
    'weight' => -10,
  );

   $data['lk_verlag_log']['table']['join'] = array(
    'node' => array(
      'left_field' => 'nid', 
      'field' => 'nid',
    ),
  );

  $data['lk_verlag_log']['log_id'] = array(
    'title' => ('Log ID'), 
    'help' => ('Die ID des Log-Eintrages'),
    'field' => array(
      'handler' => 'views_handler_field', 
      'click sortable' => true,
    ), 
  );

  
  $data['lk_verlag_log']['log_uid'] = array(
    'title' => ('User-ID'), 
    'help' => ('Some example content that references a User-ID.'),
    'relationship' => array(
      'base' => 'users',
      'field' => 'log_uid',
      'handler' => 'views_handler_relationship', 
      'label' => ('User ID'),
    ),
  );
  
   $data['lk_verlag_log']['log_verlag_uid'] = array(
    'title' => ('User-Verlag-ID'), 
    'help' => ('Some example content that references a User-ID.'),
    'relationship' => array(
      'base' => 'users',
      'field' => 'log_verlag_uid',
      'handler' => 'views_handler_relationship', 
      'label' => ('User Verlag ID'),
    ),
  );
  
  $data['lk_verlag_log']['log_verkaufsleiter_uid'] = array(
    'title' => ('User-Verkaufsleiter-ID'), 
    'help' => ('Some example content that references a User-ID.'),
    'relationship' => array(
      'base' => 'users',
      'field' => 'log_verkaufsleiter_uid',
      'handler' => 'views_handler_relationship', 
      'label' => ('User Verkaufsleiter ID'),
    ),
  );
 
 
 

 // Example numeric text field.
  $data['lk_verlag_log']['log_level'] = array(
    'title' => t('Log Level'),
    'help' => t('Just a numeric field.'),
    'field' => array(
      'handler' => 'views_handler_field_numeric',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_numeric',
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
  );
  
  
   // Example numeric text field.
  /**
  $data['lk_verlag_log']['nid'] = array(
    'title' => t('NID'),
    'help' => t('Just a numeric field.'),
    'field' => array(
      'handler' => 'views_handler_field_numeric',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_numeric',
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
  );
   */
  
  $data['lk_verlag_log']['nid'] = array(
    'title' => ('NID'), 
    'help' => ('Die NODE-ID des Eintrags'),
    'relationship' => array(
      'base' => 'node',
      'field' => 'nid',
      'handler' => 'views_handler_relationship', 
      'label' => ('NID'),
    ),
  );
  
  
  
    // Example numeric text field.
  $data['lk_verlag_log']['vku_id'] = array(
    'title' => t('VKU ID'),
    'help' => t('Just a numeric field.'),
    'field' => array(
      'handler' => 'views_handler_field_numeric',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_numeric',
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
  );
  
  
    // Example numeric text field.
  $data['lk_verlag_log']['log_ausgabe'] = array(
    'title' => t('Ausgabe ID'),
    'help' => t('Just a numeric field.'),
    'field' => array(
      'handler' => 'views_handler_field_numeric',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_numeric',
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
    'argument' => array(
      'handler' => 'views_handler_argument_numeric',
    ),
  );


    // Example numeric text field.
  $data['lk_verlag_log']['log_team'] = array(
    'title' => t('Team ID'),
    'help' => t('Just a numeric field.'),
    'field' => array(
      'handler' => 'views_handler_field_numeric',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_numeric',
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
    'argument' => array(
      'handler' => 'views_handler_argument_numeric',
    ),
  );
   
   

  $data['lk_verlag_log']['log_type'] = array(
    'title' => ('Log Typ'), 
    'help' => ('Kurzbeschreibung der Aktion'), 
    'field' => array(
      'handler' => 'views_handler_field', 
      'click sortable' => False,
    ), 
    'sort' => array(
      'handler' => 'views_handler_sort',
    ), 
    'filter' => array(
      'handler' => 'views_handler_filter_string',
    ), 
    'argument' => array(
      'handler' => 'views_handler_argument_string',
    ),
  );
  
  
  
   $data['lk_verlag_log']['log_message'] = array(
    'title' => ('Log Typ'), 
    'help' => ('Langbeschreibung der Aktion'), 
    'field' => array(
      'handler' => 'views_handler_field_markup',
      'format' => 'full_html', // filtered_html, plain_text etc.
    ),
  );


  $data['lk_verlag_log']['log_date'] = array(
    'title' => ('Datum des Eintrags'), 
    'help' => ('Datum des Eintrags'), 
    'field' => array(
      'handler' => 'views_handler_field_date', 
      'click sortable' => TRUE,
    ), 
    'sort' => array(
      'handler' => 'views_handler_sort_date',
    ), 
    'filter' => array(
      'handler' => 'views_handler_filter_date',
    ),
  );
  
  
  

  return $data;
}

?>