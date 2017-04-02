<?php

// Classes that are global and used everywhere
require_once __DIR__ . '/LK/Log/LogTrait.php';
require_once __DIR__ . '/LK/Stats/Action.php';
require_once __DIR__ . '/LK/VKU/Data/VKU.php';

require_once(__DIR__ . "/../vku/vkuCreator.class.inc");
require_once __DIR__ . '/LK/Stats/Stats.php';
require_once __DIR__ . "/LK/Log/functions.php";
require_once __DIR__ . "/LK/User/UserManager.class.php";

// Composer Autoload
require_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * Autoloader function
 * 
 * @param string $className Path of the Class
 * @return boolean
 */
function lokalkoenig_Autoload($className) {
  
  $explode = explode('\\', $className);

  //PhpOffice\PhpPresentation\PhpPresentation
  if($explode[0] === 'PhpOffice' && $explode[1] === 'PhpPresentation') {
    $trim = $explode;
    unset($trim[0]);
    $include_file = str_replace('\\', '/', implode('//', $trim));
    $path = 'sites/all/libraries/phppresentation/src/' . $include_file . '.php';
    require_once $path;

    return TRUE;
  }

  if($explode[0] != "LK"):
    return ;
  endif;
  
  if(in_array($explode[1], array('Kampagne', 'Alert', 'Solr', 'Stats','Tests', 'UI', 'PDF', 'PPT', 'Files', 'Admin', 'Log', 'VKU', 'Merkliste', 'User'))){
    $include_file = str_replace('\\', '/', $className);
    require_once __DIR__ . '/' . $include_file . '.php';
    return true;
  }  

  return false;    
}

spl_autoload_register("lokalkoenig_Autoload");
