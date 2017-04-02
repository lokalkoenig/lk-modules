<?php

/**
 * @file
 * Imports all the necessary functions
 */

define("VKU_EDITOR_PATH", 'sites/all/libraries/lk_editor');
require_once __DIR__ . '/../../../../libraries/lk_editor/dist/src/autoload.php';

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
      'type' => MENU_LOCAL_TASK);

  $items['user/%user/vku_editor/settings'] = array(
      'access callback' => 'lk_verlag_access',
      'access arguments' => array(1),
      'page callback' => 'vku_editor_page_verlag_settings',
      'page arguments' => array(1),
      'file' => 'pages/verlag_vku_editor_settings.php',
      'title' => 'VKU Dokumente (Einstellungen)',
      'type' => MENU_LOCAL_TASK);

  
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

  // Verlagsmodus
  if(arg(2) == 'vku_editor' && \LK\VKU\Editor\EditorLoader::isLoaded()){
    $manager = new \LK\VKU\Editor\Manager();
    $account = \LK\get_user(arg(1));
    
    if($account && $account ->isVerlag()){
      $manager ->setAccount($account);
      $html = $manager-> getEditorTemplate();

      $vku_basics = \LK\VKU\VKUManager::getVKU_RenderSettings($account);
      drupal_add_css('.pxedit header, .pxedit footer { background-color: '. $vku_basics['vku_hintergrundfarbe'] .' !important; }', 'inline');

      $page['page_bottom']['vku_editor'] = array(
        '#weight' => -10,
        '#markup' => $html
      );
    } 
  }

  // MA-Modus
  if(arg(0) == 'vku' && \LK\VKU\Editor\EditorLoader::isLoaded()){
    $vku = \LK\VKU\VKUManager::getVKU(arg(1), true);
    if(!$vku) {
      return ;
    }

    drupal_add_library('vku_editor', 'vku_inlace_editor');
    drupal_add_js(drupal_get_path('module', 'vku_editor'). '/js/ma_controller.js');

    $account = \LK\get_user($vku->getAuthor());
    $manager = new \LK\VKU\Editor\UserManager($account);
    
    $vku_basics = \LK\VKU\VKUManager::getVKU_RenderSettings($account);
    drupal_add_css('.pxedit header, .pxedit footer { background-color: '. $vku_basics['vku_hintergrundfarbe'] .' !important; }', 'inline');

    $html = $manager-> getEditorTemplate();
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
    'variables' => [
      'account' => new stdClass,
      'documents' => NULL,
      'available_presets' => 0,  
    ],
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
      VKU_EDITOR_PATH . '/dist/js/trumbowyg.js' => array(),
      VKU_EDITOR_PATH . '/dist/js/jquery.fileupload.js' => array(),
      VKU_EDITOR_PATH . '/dist/js/PXEdit-packed.js' => array()  
    ),
    'css' => array(
      VKU_EDITOR_PATH . '/dist/css/project.css' => array(
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
  $styles['jpg'] = array(
      'label' => "[PXEdit] JPEG",
      'effects' =>  [
          [
          'name' => 'imagecache_alpha',
          'data' => array('opacity' => 1, 'flatten' => 1, 'RGB' => ['HEX' => '#FFF']),
          'weight' => -10,
      ],
       [
          'name' => 'coloractions_convert',
          'data' => array('format' => 'image/jpeg', 'quality' => 95),
          'weight' => -9,
      ],
      ],
    );

  // Footer Logos
  $styles['pxedit_footer_logo'] = array(
    'label' => "[PXEdit] Verlag logos (Footer)",
    'effects' => array(
      [
        'name' => 'image_scale',
        'data' => array('height' => 90, 'upscale' => 0, 'width' => ''),
        'weight' => 2,
      ],
      [
        'name' => 'image_scale',
        'data' => ['width' => 300, 'height' => '', 'upscale' => 0],
        'weight' => 3,
      ],
      [
        'name' => 'canvasactions_definecanvas',
        'data' => array('RGB' => ['HEX' => ''],
                        'under' => 1,
                        'exact' => ['width' => 300, 'height' => 90, 'xpos' => 'center', 'ypos' => 'center'],  
                       ),
        'weight' => 4,
      ],  
    ),
  );

  return $styles;
}

function vku_editor_verlag_documents_themed(\LK\Verlag $verlag){
  
  $manager = new \LK\VKU\Editor\Manager(); 
  $categories = $manager ->getCategoriesAvailable($verlag);
  
  $items = $verlag -> getVerlagSetting('vku_editor_dokumente');
  $presets = $manager->getPresetsAvailable();

  $array = [];
  while(list($key, $val) = each($categories)){
    $array[$key] = [
        'title' => $val, 
        'documents' => $manager ->getDocumentsPerVerlag($verlag, $key, 1, 'document_changed DESC'),
        'documents_unpublished' => $manager ->getDocumentsPerVerlag($verlag, $key, 0, 'document_changed DESC'),
        'presets' => [],
    ];

    // Aggregate presets
    reset($presets);
    while(list($key2, $val2) = each($presets)){
     if($val2['category'] === $key && in_array($key2, $items)){
       $array[$key]['presets'][$key2] = $val2;
     } 
    }
  }
  
  return theme('vku_editor_verlag_documents', [
    'account' => $verlag, 
    'documents' => $array,
    ]
  );  
}
