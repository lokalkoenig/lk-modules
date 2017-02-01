<?php

/**
 * HOOK_menu
 * 
 * @return array
 */
function lokalkoenig_merkliste_menu(){

  $items = [];
  $items['history/flush'] = [
    'access callback' => 'lk_vku_access',
    'page callback' => 'lokalkoenig_merkliste_flush_history',
    'file' => 'callbacks/history_flush.inc',
    'title' => 'Verlauf löschen',
    'type' => MENU_CONTEXT_NONE,
  ];
  
  $items['merkliste/save'] = [
    'access callback' => 'lk_vku_access',
    'page callback' => 'lokalkoenig_merkliste_ajax_callback',
    'file' => 'callbacks/merkliste_save.inc',
    'title' => 'Merkliste Ajax Callback',
    'type' => MENU_CONTEXT_NONE,
  ];
  
  $items['merkliste'] = [
    'access callback' => 'lk_vku_access',
    'page callback' => 'lokalkoenig_merkliste_show_entries',
    'title' => 'Merklisten',
    'file' => 'callbacks/merkliste.inc',
    'type' => MENU_CONTEXT_NONE,
  ];

  $items['merkliste/%'] = [
    'access callback' => 'lk_vku_access',
    'page callback' => 'lokalkoenig_merkliste_show_entries',
    'title' => 'Merklisten',
    'page arguments' => [1],
    'file' => 'callbacks/merkliste.inc',
    'type' => MENU_CONTEXT_NONE,
  ];

  $items['merkliste/convert/%'] = [
    'access callback' => 'lk_vku_access',
    'page callback' => 'lokalkoenig_merkliste_ajax_callback_vku',
    'title' => 'Merkliste Ajax Callback Add VKU',
    'page arguments' => [2],
    'file' => 'callbacks/merkliste_convert.inc',
    'type' => MENU_CONTEXT_NONE,
  ];
      
  $items['merkliste/delete/%'] = [
    'access callback' => 'lk_vku_access',
    'page callback' => 'lokalkoenig_merkliste_ajax_callback_delete',
    'title' => 'Merkliste Löschen',
    'page arguments' => [2],
    'file' => 'callbacks/merkliste_remove.inc',
    'type' => MENU_CONTEXT_NONE,
  ];
      
  $items['merkliste/rename/%'] = [
    'access callback' => 'lk_vku_access',
    'page callback' => 'lokalkoenig_merkliste_ajax_callback_rename',
    'title' => 'Merkliste Umbenennen',
    'page arguments' => [2],
    'file' => 'callbacks/merkliste_rename.inc', 
    'type' => MENU_CONTEXT_NONE,
  ];

  $items['merkliste/send/%'] = [
    'access callback' => 'lk_vku_access',
    'page arguments' => [2],
    'page callback' => 'lokalkoenig_merkliste_ajax_callback_send',
    'file' => 'callbacks/merkliste2message',
    'title' => 'Merkliste versenden',
    'file' => 'callbacks/merkliste2message.inc',
    'type' => MENU_CONTEXT_NONE,
   ];
 
  return $items;
}
