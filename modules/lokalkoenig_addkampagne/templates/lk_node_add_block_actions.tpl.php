<?php

 if(arg(2) == "media" AND arg(0) == "node" AND arg(4) != 'edit'){
  $footer = lklink("Weiteres Medium hochladen", "node/" . arg(1) . "/addmedia", 'cloud-upload', 'btn btn-default');
  
 //$node = node_load(arg(1));
 //$tax = taxonomy_term_load($node->field_kamp_preisnivau['und'][0]['tid']);
   
   //print '<span class="label label-success">'. $tax -> name  .'</span><br />' . $tax -> description;
  
  //$footer .= " " . lklink("Präsentation einrichten", "node/" . arg(1) . "/presentation", 'align-justify', 'btn btn-success');
 }

  /**if(arg(2) == "presentation" AND isset($node -> presentation)){
     //$footer = lklink("PLZ-Sperre einrichten", "node/" . arg(1) . "/plz", 'wrench', 'btn btn-default');
     $footer .= " " . lklink("Kampagne freischalten", "node/" . arg(1), 'ok', 'btn btn-success');
  }

  if(arg(2) == "plz"){
      $footer = lklink("Weitere PLZ-Regel hinzufügen", "node/" . arg(1) . "/plz/addextra", 'plus', 'btn btn-default');
      $footer .= " " . lklink("Kampagne freischalten", "node/" . arg(1), 'ok', 'btn btn-success');
   } */

 if(isset($footer)){
   print '<div class="pull-right clearfix">';
   print $footer;
   print '</div>';
 } 

?>