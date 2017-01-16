<?php

// Classes that are global and used everywhere
require_once __DIR__ . '/LK/Log/LogTrait.php';
require_once __DIR__ . '/LK/Stats/Action.php';

require_once(__DIR__ . "/../vku/vkuCreator.class.inc");
require_once __DIR__ . '/LK/Stats/Stats.php';
require_once __DIR__ . "/LK/Log/functions.php";
require_once __DIR__ . "/LK/User/UserManager.class.php";

// Composer Autoload
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Autoloader function
 * 
 * @param string $className Path of the Class
 * @return boolean
 */
function lokalkoenig_Autoload($className) {
  $explode = explode('\\', $className);
  if($explode[0] != "LK"):
    return ;
  endif;

  if(in_array($explode[1], array('Kampagne', 'Alert', 'Solr', 'Stats','Tests', 'UI', 'PDF', 'PPT', 'Files', 'Admin', 'Log', 'VKU'))){
    $include_file = str_replace('\\', '/', $className);
    require __DIR__ . '/' . $include_file . '.php';
    return true;
  }  

  return false;    
}

spl_autoload_register("lokalkoenig_Autoload");
