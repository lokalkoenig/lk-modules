<?php
use LK\Alert\AltertCron;

function parse_active_alerts($through = 0){

  $hour = date('H');
  $hour = (int)$hour;         
  
  if($through != 111){
     if($hour != 8){
        return ; 
     }
  }
  
  AltertCron::run();
}

?>