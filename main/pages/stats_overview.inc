<?php
function lokalkoenig_admin_show_stats(){
  $tabs = '';
  
  if(arg(2) === 'weekly') {
    $stats = new \LK\Stats\Views\StatsViewerWeekly('lk');

    drupal_set_title('Wochenstatistiken');
    $tabs = \LK\UI\Tabs::render([['title' => 'Monatsstatistiken', 'url' => url('backoffice/stats')], ['title' => 'Wochenstatistiken', 'url' => url('backoffice/stats/weekly'), 'active' => TRUE]]);
  }
  else {
    $stats = new \LK\Stats\Views\StatsViewer('lk');

    drupal_set_title('Monatsstatistiken');
    $tabs = \LK\UI\Tabs::render([['title' => 'Monatsstatistiken', 'url' => url('backoffice/stats'), 'active' => TRUE], ['title' => 'Wochenstatistiken', 'url' => url('backoffice/stats/weekly')]]);
  }
  
  return $tabs . $stats->render();
}
