<?php

function vku_is_update_user_ppt(){
    return vku_is_update_user();
}    

function vku_is_update_user(){
    
    if(lk_is_moderator()){
           return true;    
    }
    
    $account = \LK\current();
    if($account AND 
          $account ->getVerlag() == LK_TEST_VERLAG_UID){
            return true;
    }   
    
return false;    
} 


/**
 * Returns back settings for the current VKU
 * Can be used by PPT and PDF output
 * 
 * @param VKUCreator $vku
 * @return Array
 */
function vku_get_output_basics(VKUCreator $vku){
   
    $array = array(
        // Base font - Lato or Arial
        'font' => 'lato',
        // Logo position Header
        'logo_position' => 'left',
        // Contact page layout
        'contact_layout' => 'default',
        
        'hide_size_online' => 'no',
        'vku_hintergrundfarbe' => 'FFFFFF',
        'title_bg_color' => '646464',
        'title_vg_color' => 'FFFFFF',
        'logo_oben' => '',
        'logos_unten' => array()
    );
    
    $author = $vku ->getAuthor();
    $account = \LK\get_user($author);
    $verlag = $account ->getVerlagObject();
    
    if(!$verlag){
        if($account ->isModerator()){
            // Load Handbuchverlag to test the Settings
            $verlag = \LK\get_user(LK_TEST_VERLAG_UID);
        }
        else {
            return $array;
        }
    }
    
    // Logo top
    $logo_oben = $verlag -> getVerlagSetting("verlag_logo", false, 'uri');
    if($logo_oben){
       $array["logo_oben"] = $logo_oben;
    }
    
    // Logo position
    $array["logo_position"] = $verlag -> getVerlagSetting("verlag_logo_position", 'left', 'value');
    
    // Logos unten
    $logos = array();
    if(isset($verlag->profile['verlag']->field_verlag_marken_logos['und'])){
        foreach($verlag->profile['verlag']->field_verlag_marken_logos['und'] as $logo){
          $logos[] = $logo["uri"];
        }
    }
    
    $array["logos_unten"] = $logos;
   
    // HG-Farbe VKU
    if($color = $verlag -> getVerlagSetting("vku_hintergrundfarbe", false, 'jquery_colorpicker')){
       $array["vku_hintergrundfarbe"] = $color; 
    }
    
    // HG-Farbe Titel
    if($color = $verlag -> getVerlagSetting("vku_hintergrundfarbe_titel", false, 'jquery_colorpicker')){
        $array["title_bg_color"] = $color; 
    }

    // VG-Farbe Titel    
    if($color = $verlag -> getVerlagSetting("vku_vordergrundfarbe_titel", false, 'jquery_colorpicker')){
        $array["title_vg_color"] = $color; 
    }
    
    // Font
    $array["font"] = $verlag -> getVerlagSetting("verlag_font", 'lato', 'value');
    
    // Contact Template
    $array["contact_layout"] = $verlag -> getVerlagSetting("verlag_kontakt_vorlage", 'default', 'value');
    
return $array;    
}



function vku_get_top_menu(){
global $user;
  
  drupal_add_js(drupal_get_path('module', 'vku') .'/js/vku2-handling.js', 'file');
  drupal_add_css(drupal_get_path('module', 'vku') .'/css/vku2.css');
 
 $array = array(); 
 $dbq = db_query("SELECT vku_id FROM lk_vku WHERE vku_status='active' AND uid='".$user -> uid  ."' ORDER BY vku_changed DESC");
 foreach($dbq as $all){
   $array[]  = new VKUCreator($all -> vku_id);
 }
 
 return theme("vku_menu", array('vkus' => $array));   
}

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
 
 
    // VKU-DOC
    $themes["vku2doc_overview"] = array(
            'template' => 'templates/doc/vku2doc_overview', // your template file called custompage.tpl.php
            'variables' => array(
              'nodes' => array(),
              'vku' => NULL
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
       
  
  $themes['vkuconnection_settings'] = array(
            'template' => 'templates/vkuconnection_settings', // your template file called custompage.tpl.php
            'variables' => array(
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
    $items['vku/add/%node'] = array(
      'access callback' => '_vku_node_access',
      'access arguments' => array(2),
      'page callback' => '_vku_add_node',
      'file' => 'inc/func_node_add.php',
      'page arguments' => array(2),
      'title' => 'Kampagne zu Verkaufsunterlagen hinzufügen',
      'type' => MENU_CONTEXT_NONE);
    
    
     $items['vkudoc'] = array(
      'access callback' => 'lk_is_moderator',
      'page callback' => 'newDocs',
      'file' => 'src/ap.php',
      'title' => 'TEST TEST',
      'type' => MENU_CONTEXT_NONE);

     // vku / active
    $items['vku/%/add/%node'] = array(
      'access callback' => '_vku_node_access',
      'access arguments' => array(3),
      'page callback' => '_vku_add_node',
      'file' => 'inc/func_node_add.php',
      'page arguments' => array(3, 1),
      'title' => 'Kampagne zu Verkaufsunterlagen hinzufügen',
      'type' => MENU_CONTEXT_NONE);

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
  
  // VKU final generieren
  $items['user/%user/vku/%/generate'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => '_vku_ontheflygenerate',
      'page arguments' => array(1, 3),
      'file' => 'inc/func_generate_vku.php',
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


    $items['vku/get'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => '_vku_show_current_ajax',
      'title' => 'Verkaufsunterlagen',
      'page arguments' => array(),
     'file' => "inc/func_vku_current.php",
      'type' => MENU_CONTEXT_NONE); 

    $items['vku/test'] = array(
      'access callback' => 'lk_is_moderator',
      'page callback' => 'vku2_checkup',
      'title' => 'Verkaufsunterlagen',
      'page arguments' => array(),
     'file' => "inc/func_vku2_check.php",
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

    $items['vku/%/%/status'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => 'vku_item_status_change',
      'page arguments' => array(1, 2),
      'file' => 'inc/func_vku_inner_status.php',
      'title' => 'VKU Item Status Change',
      'type' => MENU_CONTEXT_NONE);    


      $items['vku/%/changeorder'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => 'vku_changeorder',
      'page arguments' => array(1),
      'title' => 'Verkaufsunterlagen',
      'file' => 'inc/func_vku_inner_order.php',
      'type' => MENU_CONTEXT_NONE);   

       $items['vku/%/edit/%'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => 'vku_edit_line_item',
      'page arguments' => array(1,3),
      'title' => 'Verkaufsunterlagen',
      'file' => 'inc/func_vku_inner_edit.php',
      'type' => MENU_CONTEXT_NONE);  


      $items['vku/%/delete/%'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => 'vku_delete_line_item',
      'page arguments' => array(1,3),
      'title' => 'Verkaufsunterlagen',
      'file' => 'inc/func_vku_inner_edit.php',
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

     
     

    // Admin
    $items['logbuch/editlizenz/%'] = array(
      'access callback' => lk_is_moderator(),
      'page callback' => 'vku_vku_admin_edit',
      'page arguments' => array(2),
      'title' => 'Lizenz editieren',
      'file' => "admin/vku_admin.inc",
      'type' => MENU_CONTEXT_NONE); 
     
    return $items;
  }


?>