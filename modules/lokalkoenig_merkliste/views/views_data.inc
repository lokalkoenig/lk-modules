<?php

function lokalkoenig_merkliste_views_data() {
  $data['lk_lastviewed']['table']['group'] = ('Last Viewed');
  $data['lk_lastviewed']['table']['base'] = array(
    'field' => 'id',
    'title' => ('Last Viewed'), 
    'help' => ('LK LaViewed speichert die Nodes in einer Tabelle'), 
    'weight' => -10,
  );

  $data['lk_lastviewed']['table']['join'] = array(
    'node' => array(
      'left_field' => 'nid', 
      'field' => 'nid',
    ),
    'users' => array(
      'left_field' => 'uid', 
      'field' => 'uid',
    ),
  );

  $data['lk_lastviewed']['lastviewed_id'] = array(
    'title' => ('LVID ID'), 
    'help' => ('Die ID des Merkliste-Eintrages'),
    'field' => array(
      'handler' => 'views_handler_field', 
      'click sortable' => true,
    ), 
  );


  $data['lk_lastviewed']['nid'] = array(
    'title' => ('NID'), 
    'help' => ('Die NODE-ID des Eintrags'),
    'relationship' => array(
      'base' => 'node',
      'field' => 'nid',
      'handler' => 'views_handler_relationship', 
      'label' => ('NID'),
    ),
  );
  
  
  $data['lk_lastviewed']['uid'] = array(
    'title' => ('User-ID'), 
    'help' => ('Some example content that references a User-ID.'),
    'relationship' => array(
      'base' => 'users',
      'field' => 'uid',
      'handler' => 'views_handler_relationship', 
      'label' => ('User ID'),
    ),
  );
  

  

  $data['lk_lastviewed']['lastviewed_time'] = array(
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
