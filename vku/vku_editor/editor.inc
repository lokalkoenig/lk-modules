<?php

/**
 * @file
 * Imports all the necessary functions
 */

require_once __DIR__ . '/../../editor/dist/src/autoload.php';


/**
 * HOOK_menu
 * 
 * @return array
 */
function vku_editor_menu(){
    
  $items =[];
  $items['user/%user/vku_editor'] = array(
      'access callback' => 'lk_verlag_access',
      'access arguments' => array(1),
      'page callback' => 'vku_editor_page_verlag_cb',
      'page arguments' => array(1),
      'file' => 'pages/verlag_vku_editor.php',
      'title' => 'VKU Dokumente',
      'type' => MENU_CONTEXT_NONE);   
  
  $items['vku_editor'] = array(
      'access callback' => 'lk_vku_access',
      'page callback' => 'vku_editor_api_cb',
      'page arguments' => array(1),
      'file' => 'api.php',
      'title' => 'VKU Dokumente',
      'type' => MENU_CONTEXT_NONE);   
  
  
return $items;  
}

/**
 * Alters the Page-Variables 
 * in order to place the VKU-Editor
 * 
 * @param array $page
 */
function vku_editor_page_alter(&$page) {
  
  if(arg(2) == 'vku_editor'){
    $document = new \LK\VKU\Editor\Manager();
    
    $html = $document-> getEditorTemplate([
        'pxeditid' => 'verlag-' . arg(1) . '-' . time() 
    ]);
    
    $page['page_bottom']['vku_editor'] = array(
      '#weight' => -10,
      '#markup' => $html
    );
  }  
}

/**
 * HOOK_theme
 * 
 * @return array
 */
function vku_editor_theme(){
  
  $themes["vku_editor_verlag"] = array(
    'template' => 'templates/vku_editor_verlag_overview',
    'variables' => array(
      'account' => new stdClass,
      'documents' => NULL
    ),
  );
  
  $themes["vku_editor_verlag_documents"] = array(
    'template' => 'templates/vku_editor_verlag_documents',
    'variables' => array(
      'account' => array(),
      'documents' => array(),
    ),
  );
  
return $themes;  
}

/**
 * HOOK_library
 * 
 * @return array
 */
function vku_editor_library() {
  // Library One.
  
  $libraries = [];

  $libraries['vku_inlace_editor'] = array(
    'title' => 'PXEdit',
    'version' => '1.0.1',
    'js' => array(
      drupal_get_path('module', 'vku') . '/../editor/dist/js/trumbowyg.js' => array(),
      drupal_get_path('module', 'vku') . '/../editor/dist/js/jquery.fileupload.js' => array(),
      drupal_get_path('module', 'vku') . '/../editor/dist/js/PXEdit-packed.js' => array()  
    ),
    'css' => array(
      drupal_get_path('module', 'vku') . '/../editor/dist/css/project.css' => array(
        'type' => 'file',
        'media' => 'screen',
      ),
    ),
 );
  
return $libraries;  
}  

/**
 * HOOK_image_default_styles
 * 
 * @return array
 */
function vku_editor_image_default_styles() {
  
  $styles = array();
  
  $manager = new LK\VKU\Editor\Manager();
  $image_styles = $manager -> getImagePresets();
  
  while(list($key, $val) = each($image_styles)){
    $styles[$key] = array(
      'label' => "[PXEdit] " . $val['title'],
      'effects' => array(
        array(
          'name' => 'image_scale_and_crop',
          'data' => array('width' => $val['width'], 'height' => $val['height']),
          'weight' => 0,
        ),
      ),
    );
  }

  return $styles;
}

function vku_editor_verlag_documents_themed(\LK\Verlag $verlag){
  
  $manager = new \LK\VKU\Editor\Manager(); 
  $categories = $manager ->getCategoriesAvailable($verlag);
  $presets = $manager->getPresetsAvailable($verlag);
  
  $array = [];
  while(list($key, $val) = each($categories)){
    $array[$key] = [
        'title' => $val, 
        'documents' => $manager ->getDocumentsPerVerlag($verlag, $key),
        'presets' => []
    ];
  }
  
  // Add the presets
  while(list($key, $val) = each($presets)){
    $array[$val['category']]['presets'][$key] = $val;
  }
  
  return theme('vku_editor_verlag_documents', [
    'account' => $verlag, 
    'documents' => $array,
    ]
  );  
}
