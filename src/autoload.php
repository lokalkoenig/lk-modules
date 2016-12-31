<?php

require_once __DIR__ . '/LK/Log/LogTrait.php';
require_once __DIR__ . '/LK/Stats/Stats.php';
require_once __DIR__ . "/LK/User/UserManager.class.php";
require_once __DIR__ . "/LK/Log/functions.php";

// Composer Autoload
require_once __DIR__ . '/vendor/autoload.php';


function lokalkoenig_Autoload($className) {
    
    $explode = explode('\\', $className);
   
    if($explode[0] != "LK"):
      return ;
    endif;
    
    if(in_array($explode[1], array('Kampagne', 'Alert', 'Solr', 'Tests', 'UI', 'PDF', 'PPT', 'Files', 'Admin', 'Log','Component'))){
        require __DIR__ . '/' .$className . '.php';
    }  
}



spl_autoload_register("lokalkoenig_Autoload");