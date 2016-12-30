<?php

function lokalkoenig_admin_views_data() {
    
  /** LK Log Table */  
  $data['lk_log']['table']['group'] = ('Lokalkoenig One Log');
 
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
  
   
  return $data;
}