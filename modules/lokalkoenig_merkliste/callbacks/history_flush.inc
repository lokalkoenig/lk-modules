<?php

function lokalkoenig_merkliste_flush_history(){
global $user;
  db_query("DELETE FROM lk_lastviewed WHERE uid='". $user -> uid ."'");  
  drupal_set_message("Ihr Verlauf wurde gelöscht.");
  drupal_goto('history');   
}
