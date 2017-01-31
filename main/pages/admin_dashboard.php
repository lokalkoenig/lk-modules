<?php
/**
 * @path /backoffice
 * @file
 * Shows the Backoffice Dashboard
 */

/**
 * Views the Backoffice Dashboard
 *
 * @return string
 */
function lokalkoenig_main_dashboard(){

  $views = [];
  $views['verlage'] = ['display' => 'dashboard', 'title' => "Aktive Benutzer", 'size' => 'smaller', 'link' => 'backoffice/users'];
  $views['lizenzen'] = ['display' => 'dashboard', 'title' => "Letzte Lizenz", 'size' => 'normal', 'link' => 'backoffice/logbuch/lizenzen'];
  $views['verkaufsunterlagen_admin'] = ['display' => 'dashboard', 'title' => "VKU", 'size' => 'normal', 'link' => 'backoffice/logbuch/vku'];
  $views['search_history'] = ['display' => 'dashboard', 'title' => "Letzte Suchen", 'size' => 'smaller', 'link' => 'backoffice/logbuch/searches'];
  $views['log'] = ['display' => 'dashboard', 'title' => "Logbuch", 'size' => 'normal', 'link' => 'backoffice/logbuch'];
 
  $output = '<div class="flexbox">';

  while(list($key, $val) =  each($views)){
    $output .= '<div class="flexitem well well-white '. $val['size'] .'"><h4><small class="pull-right"><a href="'. url($val['link']) .'" class="btn btn-default btn-sm">Mehr</a></small>'. $val['title'] .'</h4>' . views_embed_view($key, $val['display']) . "</div>";
  }

  $output .= "</div>";

  return $output;
}
