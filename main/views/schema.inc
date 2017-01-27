<?php

function lokalkoenig_admin_views_data() {

  $data = [];
  
  /////////////////
  ///////// LK_LOG
  ////////////////
  $data['lk_log']['table']['group'] = ('Lokalkoenig');
 
  $data['lk_log']['table']['base'] = array(
    'field' => 'id',
    'title' => ('Lokalkoenig One Log'), 
    'help' => ('LK Log speichert die Aktionen der User in einer Tabelle'), 
    'weight' => -10,
  );
  
   $data['lk_log']['table']['join'] = array(
    'users' => array(
      'left_field' => 'uid', 
      'field' => 'uid',
    ),
  );

  $data['lk_log']['id'] = array(
    'title' => ('Log ID'), 
    'help' => ('Die ID des Log-Eintrages'),
    'field' => array(
      'handler' => '\\LK\\Log\\Reader', 
      'click sortable' => true,
    ), 
    'sort' => array(
      'handler' => 'views_handler_sort',
     )  
  );
  
  $data['lk_log']['verlag_uid'] = array(
    'title' => ('Verlag-ID'), 
    'help' => ('Some example content that references a User-ID.'),
    'relationship' => array(
      'base' => 'users',
      'field' => 'verlag_uid',
      'base field' => 'uid',
      'handler' => 'views_handler_relationship', 
      'label' => ('User ID'),
    ),
   );
    
   $data['lk_log']['uid'] = array(
    'title' => ('User-ID'), 
    'help' => ('Some example content that references a User-ID.'),
    'relationship' => array(
      'base' => 'users',
      'field' => 'uid',
      'handler' => 'views_handler_relationship', 
      'label' => ('User ID'),
    ),
  );
 
   
 
  
   
 
  $data['lk_log']['message'] = array(
    'title' => ('Aktions-Text'), 
    'help' => ('Aktion des Users'), 
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
  
  $data['lk_log']['category'] = array(
    'title' => ('Kategorie'), 
    'help' => ('Aktion des Users'), 
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
  
  $data['lk_log']['sub_category'] = array(
    'title' => ('Sub-Kategorie'), 
    'help' => ('Sekundäre Kategorie'), 
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
  
  $data['lk_log']['request_time'] = array(
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
    
  
  $data['lk_log']['node_nid'] = array(
    'title' => ('NID'), 
    'help' => ('Die NODE-ID des Eintrags'),
    'relationship' => array(
      'base' => 'node',
      'field' => 'node_nid',
      'handler' => 'views_handler_relationship', 
      'label' => ('NID'),
    ),
  );  
  
  
 $data['lk_log']['vku_id'] = array(
    'title' => ('VKU ID'), 
    'help' => ('Die ID des Log-Eintrages'),
    'field' => array(
      'handler' => 'views_handler_field', 
      'click sortable' => true,
    ), 
  );

  $data['lk_log']['team_id'] = array(
    'title' => ('Team ID'), 
    'help' => ('Die ID des Log-Eintrages'),
    'field' => array(
      'handler' => 'views_handler_field', 
      'click sortable' => true,
    ), 
    'relationship' => array(
      'base' => 'eck_team',
      'field' => 'team_id',
      'base field' => 'id',
      'handler' => 'views_handler_relationship', 
      'label' => ('Team ID'),
    ),
  );
    
  
  $data['lk_log']['lizenz_id'] = array(
    'title' => ('Lizenz ID'), 
    'help' => ('Die ID des Log-Eintrages'),
    'field' => array(
      'handler' => 'views_handler_field', 
      'click sortable' => true,
    ), 
  );
    
  $data['lk_log']['context'] = array(
    'title' => ('Kontext'), 
    'help' => ('Kontext der Aktion'), 
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
  
  /////////////////
  ///////// lk_vku_lizenzen
  ////////////////
  
  $data['lk_vku_lizenzen']['table']['group'] = ('Lokalkoenig');
 
  $data['lk_vku_lizenzen']['table']['base'] = array(
    'field' => 'id',
    'title' => ('Lokalkoenig Lizenzen'), 
    'help' => ('LK Lizenzen speichert die Lizenzen in der Datenbank'), 
    'weight' => -10,
  );
  
  $data['lk_vku_lizenzen']['id'] = array(
    'title' => ('ID'), 
    'help' => ('ID'),
    'field' => array(
      'handler' => '\\LK\\Log\\LizenzReader', 
      'click sortable' => true,
    ), 
    'sort' => array(
      'handler' => 'views_handler_sort',
     )  
  );
  
  $data['lk_vku_lizenzen']['lizenz_verlag_uid'] = array(
    'title' => ('Verlag-ID'), 
    'help' => ('Some example content that references a User-ID.'),
    'relationship' => array(
      'base' => 'users',
      'field' => 'lizenz_verlag_uid',
      'base field' => 'uid',
      'handler' => 'views_handler_relationship', 
      'label' => ('User ID'),
    ),
   );
  
  //	lizenz_team
  
  $data['lk_vku_lizenzen']['lizenz_date'] = array(
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
  
  $data['lk_vku_lizenzen']['lizenz_team'] = array(
    'title' => ('Team ID'), 
    'help' => ('Die ID des Log-Eintrages'),
    'field' => array(
      'handler' => 'views_handler_field', 
      'click sortable' => true,
    ), 
    'relationship' => array(
      'base' => 'eck_team',
      'field' => 'lizenz_team',
      'base field' => 'id',
      'handler' => 'views_handler_relationship', 
      'label' => ('Team ID'),
    ),
  );
  
  
  $data['lk_vku_lizenzen']['nid'] = array(
    'title' => ('NID'), 
    'help' => ('Die NODE-ID des Eintrags'),
    'relationship' => array(
      'base' => 'node',
      'field' => 'nid',
      'handler' => 'views_handler_relationship', 
      'label' => ('NID'),
    ),
  );  
   
  
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
  
  return $data;
}
