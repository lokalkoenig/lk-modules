<?php

function vku_views_data() {
  $data['lk_vku']['table']['group'] = ('Verkaufsunterlagen');

  $data['lk_vku']['table']['base'] = array(
    'field' => 'vku_id',
    'title' => ('VKU-ID'), 
    'help' => ('Verkaufsunterlagen'), 
    'weight' => -10,
  );

  
  // Example numeric text field.
  $data['lk_vku']['vku_id'] = array(
    'title' => ('VKU ID'),
    'help' => ('Just a numeric field.'),
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
  $data['lk_vku']['vku_ready_filesize'] = array(
    'title' => ('VKU PDF Größe'),
    'help' => ('Just a numeric field.'),
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


  $data['lk_vku']['vku_generic'] = array(
    'title' => t('Direktkauf'),
    'help' => t('Just an on/off field.'),
    'field' => array(
      'handler' => 'views_handler_field_boolean',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_boolean_operator',
      
      // Note that you can override the field-wide label:
      'label' => t('Published'),
      
      // This setting is used by the boolean filter handler, as possible option.
      'type' => 'yes-no',
      
      // use boolean_field = 1 instead of boolean_field <> 0 in WHERE statment.
      'use equal' => TRUE,
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
  );

  $data['lk_vku']['uid'] = array(
    'title' => ('User-ID'), 
    'relationship' => array(
      'base' => 'users',
      'field' => 'uid',
      'handler' => 'views_handler_relationship', 
      'label' => ('User ID'),
    ),
  );
  
  $data['lk_vku']['vku_created'] = array(
    'title' => ('Datum Erstellt'), 
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

   $data['lk_vku']['vku_changed'] = array(
    'title' => ('Datum changed'), 
    'help' => ('Datum changed'), 
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
  
  
  $data['lk_vku']['vku_purchased_date'] = array(
    'title' => ('Datum der Bestellung'), 
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
  
  
  $data['lk_vku']['vku_title'] = array(
    'title' => ('VKU Titel'), 
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
  
  $data['lk_vku']['vku_company'] = array(
    'title' => ('VKU Unternehmen'), 
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
  
  $data['lk_vku']['vku_untertitel'] = array(
    'title' => ('VKU Untertitel'), 
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
  
  
  
  $data['lk_vku']['vku_status'] = array(
    'title' => ('VKU Status'), 
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
  
  return $data;
}

?>