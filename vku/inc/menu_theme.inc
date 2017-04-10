<?php


function vku_theme(){

 $themes = array ('vku' => array(
            'template' => 'templates/vku', // your template file called custompage.tpl.php
            'variables' => array(
              'vku' => NULL,
              'nodes' => array(),
              'settingsform' => NULL,
              'settings' => NULL,
              'submitform' => NULL
            ),
        ),
  );
 
  $themes["vku2"] = array(
            'template' => 'templates/vku2', // your template file called custompage.tpl.php
            'variables' => array(
              'nodes' => array(),
              'vku' => NULL
            ),
        );
 
  $themes["vku2_template"] = array(
            'template' => 'templates/vku2_template', // your template file called custompage.tpl.php
            'variables' => array(
              'nodes' => array(),
              'vku' => NULL
            ),
        );
  
  
   $themes["vku2_items"] = array(
            'template' => 'templates/vku2_items', // your template file called custompage.tpl.php
            'variables' => array(
              'items' => array(),
              'vku' => NULL
            ),
        );
 
   $themes["vku2_documents"] = array(
            'template' => 'templates/vku2_documents', // your template file called custompage.tpl.php
            'variables' => array(
              'print' => array(),
              'online' => array(),
              'sonstiges' => array(),
              'kampagnen' => array(),
              'vku' => NULL
            ),
        );
   
   
   $themes["vku2_item"] = array(
            'template' => 'templates/vku2_item', // your template file called custompage.tpl.php
            'variables' => array(
              'items' => array(),
              'vku' => NULL,
              'item' => array()  
            ),
        );
   
   
   $themes["vku2_kampagne"] = array(
            'template' => 'templates/vku2_kampagne', // your template file called custompage.tpl.php
            'variables' => array(
              'nid' => array(),
            ),
        );

   $themes["vku_content"] = array(
            'template' => 'templates/vku_content', // your template file called custompage.tpl.php
            'variables' => array(
              'nodes' => array(),
              'vku' => NULL
            ),
        );
   
  // Neu 
  $themes["vku_menu"] = array(
            'template' => 'templates/vku_menu', // your template file called custompage.tpl.php
            'variables' => array(
              'vku' => NULL
            ),
        ); 
  
  $themes["vkudetails"] = array(
            'template' => 'templates/vkudetails', // your template file called custompage.tpl.php
            'variables' => array(
              'account' => NULL,
              'vku' => array(),
              'form' => NULL
            ),
        );        
  
   $themes["vkudetails_admin"] = array(
            'template' => 'templates/vkudetails_admin', // your template file called custompage.tpl.php
            'variables' => array(
              'vku' => NULL,
            ),
        );       
  
  $themes["vkudetails_create"] = array(
            'template' => 'templates/vkudetails_create', // your template file called custompage.tpl.php
            'variables' => array(
              'account' => NULL,
              'vku' => array(),
              'form' => NULL
            ),
        );        

  $themes["vkudetails_lizenzen"] = array(
            'template' => 'templates/vkudetails_lizenzen', // your template file called custompage.tpl.php
            'variables' => array(
              'account' => NULL,
              'vku' => array(),
              'form' => NULL
            ),
        );        
  

  $themes["lizenz_download"] = array(
            'template' => 'templates/lizenz_download', // your template file called custompage.tpl.php
            'variables' => array(
              'lizenz' => NULL,
              "link" => NULL
            ),
        );        
  
  
  
  $themes["node_page_lizenz_purchased"] = array(
            'template' => 'templates/node_page_lizenz_purchased', // your template file called custompage.tpl.php
            'variables' => array(
              'lizenz' => NULL,
              "link" => NULL
            ),
        );        

   $themes["node_page_lizenz_purchased_small"] = array(
    'template' => 'templates/node_page_lizenz_purchased_small', // your template file called custompage.tpl.php
    'variables' => array(
      'lizenz' => NULL,
      "link" => NULL
     ),
    );

  $themes["node_page_lizenz_to_purchase"] = [
    'template' => 'templates/node_page_lizenz_to_purchase', // your template file called custompage.tpl.php
    'variables' => [
      'ausgaben' => NULL,
      'nid' => 0,
      'link' => NULL
    ],
  ];

  $themes["node_page_lizenz_purchas_can_not"] = [
    'template' => 'templates/node_page_lizenz_purchas_can_not',
  ];

  
  $themes['vkuconnection_settings'] = array(
            'template' => 'templates/vkuconnection_settings', // your template file called custompage.tpl.php
            'variables' => array(
              'items' => array()
            ),
        );
 
        $themes['vkuconnection_add'] = array(
            'template' => 'templates/vkuconnection_add', // your template file called custompage.tpl.php
            'variables' => array(
              'vku' => NULL,
              'items' => array()
            ),
        );
  
  
return $themes;
}


 function vku_menu(){
      
    $items['user/%user/vku/%/renew'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => '_vku_renew_vku',
      'page arguments' => array(1, 3),
      'file' => 'inc/func_renew.php',
      'title' => 'Generierte Verkaufsunterlagen',
      'type' => MENU_CONTEXT_NONE);     
        
 
     // vku / active
   $items['user/%user/vku/%/delete'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => '_vku_current_delete',
      'page arguments' => array(3),
       'file' => 'inc/func_vku_delete.php',
      'title' => 'Generierte Verkaufsunterlagen',
      'type' => MENU_CONTEXT_NONE);  
      
 
    // VKU Direkt erstellen und Lizenz kaufen
     // Ajax-Call
     $items['vkudirekt/%node'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => '_vku_direct_generate',
      'page arguments' => array(1),
      'title' => 'Verkaufsunterlagen',
      'file' => "inc/func_vku_direct.php",
      'type' => MENU_CONTEXT_NONE);       
  
  $items['user/%user/vku/%/details'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => '_vku_details',
      'page arguments' => array(1, 3),
      'file' => 'inc/func_vku_details.php',
      'title' => 'Generierte Verkaufsunterlagen',
      'type' => MENU_CONTEXT_NONE);  
  
  

  // VKU verwalten   
  $items['user/%user/vku/%/download'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => '_vku_download_vku',
      'page arguments' => array(1, 3),
      'file' => 'inc/func_vku_download.php',
      'title' => 'Generierte Verkaufsunterlagen',
      'type' => MENU_CONTEXT_NONE);    

  
  // VKU verwalten   
  $items['user/%user/vku/%/download/ppt'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => '_vku_download_vku',
      'page arguments' => array(1, 3, 'ppt'),
      'file' => 'inc/func_vku_download.php',
      'title' => 'Generierte Verkaufsunterlagen',
      'type' => MENU_CONTEXT_NONE);    

   
    // New VKU 2.0 Items
    
   $items['vku/%/setactive' ] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => '_vku_set_active',
      'page arguments' => array(1),
      'file' => "inc/func_vku2_set_active.php",
      'title' => 'VKU make active',
      'type' => MENU_CONTEXT_NONE);  
   
   
    $items['vku/%/callback' ] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => '_vku2_callback',
      'page arguments' => array(1),
      'file' => "inc/func_vku2_callback.php",
      'title' => 'VKU make active',
      'type' => MENU_CONTEXT_NONE);  
   
   // vku / active
    $items['vku/add_active/%node'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => '_vku2_add_node',
      'file' => 'inc/func_vku2_node_add.php',
      'page arguments' => array(2),
      'title' => 'Kampagne zur aktiven Verkaufsunterlage hinzufügen',
      'type' => MENU_CONTEXT_NONE);
   
  
   // VKU erstellen 
   $items['vku'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => '_vku_redirect_current',
      'title' => 'Verkaufsunterlagen',
      'file' => "inc/func_vku.php",
      'type' => MENU_CONTEXT_NONE); 

   $items['vku/%'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => '_vku_show',
      'title' => 'Verkaufsunterlagen',
      'page arguments' => array(1),
     'file' => "inc/func_vku.php",
      'type' => MENU_CONTEXT_NONE);

    $items['vku/create'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => '_vku_create',
      'title' => 'Verkaufsunterlagen',
      'file' => "inc/func_vku.php",
      'type' => MENU_CONTEXT_NONE);  


   $items['vku/%/delete' ] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => '_vku_delete_data',
      'page arguments' => array(1),
      'file' => "inc/func_vku_inner_remove_vku.php",
      'title' => 'VKU löschen',
      'type' => MENU_CONTEXT_NONE);  

      $items['vku/%/preview/%'] = array(
        'access callback' => 'lk_vku_access',
        'page callback' => 'vku_show_line_item_preview',
        'page arguments' => array(1,3),
        'title' => 'Verkaufsunterlagen',
        'file' => 'inc/func_vku_inner_preview.php',
        'type' => MENU_CONTEXT_NONE); 

      $items['user/%user/vku/%/download/%'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => '_vku_download_file',
      'page arguments' => array(1,3,5),
      'file' => 'download.inc',
      'title' => 'Herunterladen',
      'type' => MENU_CONTEXT_NONE);      
    
     $items['user/%user/vku/flush'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => 'vku_func_flush_deleted',
      'page arguments' => array(1),
      'file' => 'inc/func_flush_deleted_vku.php',
      'title' => 'Alle gelöschten Verkaufunterlagen löschen',
      'type' => MENU_CONTEXT_NONE);      
    
     
      // Download
     $items['download/%'] = array(
      'access callback' => true,
      'page callback' => '_vku_only_download',
      'page arguments' => array(1),
      'title' => 'Download',
      'file' => "download.inc",
      'type' => MENU_CONTEXT_NONE); 

     // VKU Templates
     
    $items['vku/create/vorlage'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => 'vkuconnection_create_vorlage',
      'page arguments' => array(),
      'file' => 'inc/vorlagen/vkuconnection_create_vorlage.php',
      'title' => 'VKU-Vorlage callback',
      'type' => MENU_CONTEXT_NONE);  

  $items['vku/%/template/%'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => 'vkuconnection_takeover_vorlage',
      'page arguments' => array(1, 3),
      'file' => 'inc/vorlagen/vkuconnection_takeover_vorlage.php',
      'title' => 'VKU-Entries add',
      'type' => MENU_CONTEXT_NONE);  
 
  $items['user/%user/vkusettings'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => 'vkuconn_settings',
      'page arguments' => array(1),
      'file' => 'inc/vorlagen/settings.php',
      'title' => 'VKU-Einstellungen',
      'type' => MENU_CONTEXT_NONE);   

 
      
    vku_menu_version1($items);
    
    return $items;
  }

/**
 * Adds Menu item for VKU-1-Version
 * 
 * @param array $items
 */  
function vku_menu_version1(&$items){
  
   $items['vku/%/addextra/%'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => '_vkuconn_add_docuement',
      'page arguments' => array(1, 3),
      'file' => 'inc/vku1/adddocument.php',
      'title' => 'VKU-Entries add',
      'type' => MENU_CONTEXT_NONE);  
   
    $items['vku/%/changeorder'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => 'vku_changeorder',
      'page arguments' => array(1),
      'title' => 'Verkaufsunterlagen',
      'file' => 'inc/vku1/func_vku_inner_order.php',
      'type' => MENU_CONTEXT_NONE);   
   
     $items['vku/%/delete/%'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => 'vku_delete_line_item',
      'page arguments' => array(1,3),
      'title' => 'Verkaufsunterlagen',
      'file' => 'inc/vku1/func_vku_inner_edit.php',
      'type' => MENU_CONTEXT_NONE);   
  
    $items['vku/%/edit/%'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => 'vku_edit_line_item',
      'page arguments' => array(1,3),
      'title' => 'Verkaufsunterlagen',
      'file' => 'inc/vku1/func_vku_inner_edit.php',
      'type' => MENU_CONTEXT_NONE);  

      $items['vku/%/%/status'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => 'vku_item_status_change',
      'page arguments' => array(1, 2),
      'file' => 'inc/vku1/func_vku_inner_status.php',
      'title' => 'VKU Item Status Change',
      'type' => MENU_CONTEXT_NONE);    
      
         // vku / active
    $items['vku/add/%node'] = array(
      'access callback' => 'lk_vku_access',
      'access arguments' => array(2),
      'page callback' => '_vku_add_node',
      'file' => 'inc/vku1/func_node_add.php',
      'page arguments' => array(2),
      'title' => 'Kampagne zu Verkaufsunterlagen hinzufügen',
      'type' => MENU_CONTEXT_NONE);
 
    // VKU final generieren
  $items['user/%user/vku/%/generate'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => '_vku_ontheflygenerate',
      'page arguments' => array(1, 3),
      'file' => 'inc/vku1/func_generate_vku.php',
      'title' => 'Generierte Verkaufsunterlagen',
      'type' => MENU_CONTEXT_NONE); 
      
     $items['vku/get'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => '_vku_show_current_ajax',
      'title' => 'Verkaufsunterlagen',
      'page arguments' => array(),
      'file' => "inc/vku1/func_vku_current.php",
      'type' => MENU_CONTEXT_NONE); 
     
     $items['vku/%/add/%node'] = array(
      'access callback' => 'lk_vku_access',
      'access arguments' => array(3),
      'page callback' => '_vku_add_node',
      'file' => 'inc/vku1/func_node_add.php',
      'page arguments' => array(3, 1),
      'title' => 'Kampagne zu Verkaufsunterlagen hinzufügen',
      'type' => MENU_CONTEXT_NONE);

}
