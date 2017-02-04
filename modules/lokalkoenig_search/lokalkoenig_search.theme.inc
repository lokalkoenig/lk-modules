<?php

/**
 * HOOK_theme
 *
 * @return array
 */
function lokalkoenig_search_theme(){
  
  $themes = [];
  $themes["lk_search_other"] = [
    'template' => 'templates/lk_search_other',
    'variables'=> [
      'total_items' => NULL,
      'tags_display' => null,
      'viewsout' => null,
      'url' => null,
    ],
  ];
  
  $themes["lk_search"] = [
    'template' => 'templates/lk_search',
    'variables'=> ['node' => NULL],
  ];

  $themes["lk_search_empty"] = [
    'template' => 'templates/lk_search_empty',
    'variables'=> [],
  ];

  $themes["lk_search_autocomplete"] = [
    'template' => 'templates/lk_search_autocomplete',
    'variables'=> [],
  ];
   
return $themes;
}            
