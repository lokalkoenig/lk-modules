<?php 

// Online
 $online_f = explode(",", $node->field_format_kamp_online['und'][0]['value']);
 $online_formate = $online_f[0];
 $online_formate_count = count($online_f);
 
 if(!lk_upgrade_medienformate() OR ($online_formate_count) == 1){
     $overview_online = '<img src="/sites/all/themes/bootstrap_lk/design/icon-webanzeige.png" width="20" height="20"/><span class="k-desc">'. $online_formate .'</span>';
 }
 else {
     $overview_online =  '<span class="multiple-formate" data-toggle="tooltip" title="Diese Kampagne enthält mehrere Formate: '. implode(", ", $online_f) .'"><span class="label label-primary label-lk"><sup>' . $online_formate_count .'</sup><strong>@</strong></span><span class="k-desc">u.a. '.  $online_formate . '</span></span>';    
 }

 
 // Print
 $orig = $node->field_format_kamp_print['und'][0]['value'];
 $print_f = explode(",", $orig);

 $print_formate = $print_f[0];
 $print_formate_count = count($print_f);
 
 if(!lk_upgrade_medienformate() OR ($print_formate_count) == 1){
     $overview_print = '<img src="/sites/all/themes/bootstrap_lk/design/icon-printanzeige.png" width="20" height="20" /><span class="k-desc">'. $print_formate .'</span>';
 }
 else {
     $overview_print =  '<span class="multiple-formate" data-toggle="tooltip" title="Diese Kampagne enthält mehrere Formate: '. implode(", ", $print_f) .'"><span class="label label-primary label-lk label-lk-print"><sup>' . $print_formate_count .'</sup><strong>P</strong></span><span class="k-desc">u.a. '.  $print_formate . '</span></span>';    
 }
 