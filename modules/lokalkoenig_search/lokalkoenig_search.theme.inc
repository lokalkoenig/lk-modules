<?php

/** Block Theme */
function lokalkoenig_search_theme(){
   $themes = array();
   
   
    $themes["lk_search_other"] = array(
            'template' => 'templates/lk_search_other', // your template file called custompage.tpl.phü
            'variables'=> array(
              'total_items' => NULL,
              'tags_display' => null,
              'viewsout' => null,
              'url' => null
            ));
   
   $themes["lk_search"] = array(
            'template' => 'templates/lk_search', // your template file called custompage.tpl.phü
            'variables'=> array('node' => NULL));
   
    $themes["lk_search_empty"] = array(
            'template' => 'templates/lk_search_empty', // your template file called custompage.tpl.phü
            'variables'=> array());

   
   
return $themes;
}            
