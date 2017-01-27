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
  $views['verlage'] = ['display' => 'dashboard', 'title' => "Aktive Benutzer", 'size' => 'smaller'];
  $views['lizenzen'] = ['display' => 'dashboard', 'title' => "Letzte Lizenz", 'size' => 'normal'];
  $views['verkaufsunterlagen_admin'] = ['display' => 'dashboard', 'title' => "VKU", 'size' => 'normal'];
  $views['search_history'] = ['display' => 'dashboard', 'title' => "Letzte Suchen", 'size' => 'smaller'];

  $output = '<div class="flexbox">';

  while(list($key, $val) =  each($views)){
    $output .= '<div class="flexitem well well-white '. $val['size'] .'"><h4>'. $val['title'] .'</h4><hr />' . views_embed_view($key, $val['display']) . "</div>";
  }

  $output .= "</div>";

  return $output;
}
