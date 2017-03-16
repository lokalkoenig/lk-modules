<?php
function lokalkoenig_admin_show_stats(){
  $stats = new \LK\Stats\Views\StatsViewer('lk');
  return $stats->render();
}
