<?php

use LK\VKU\Editor\Manager as DocManager;

function vku_editor_page_team_cb($account) {

  $ma = \LK\get_user($account->uid);
  $team = $ma->getTeamObject();

  if(!$team) {
    throw new Exception('No Team found');
  }

  drupal_set_title('Vorlagen für Verkaufsunterlagen');
  lk_set_icon('file');


  $manager = new DocManager();
  

  

  return  'Foobar';
}
