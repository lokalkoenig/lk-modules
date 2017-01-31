<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function _vku2_add_node($node){
    
    $vkuid = vku_get_active_id();
    if(!$vkuid){
        $vku = new VKUCreator('new', array('uid' => $user -> uid));
        $count = 1;
        $vku -> addKampagne($node -> nid);
        $cta = '<a href="'. url($vku -> url()) .'" class="btn btn-primary btn-hollow">Verkaufsunterlage fertigstellen</a>';
   
        $message = 'Die Kampagne <b>'  . $node -> title . '</b> wurde zu Ihrer neuen Verkaufsunterlage hinzugefügt.<br /><br />'. $cta;
        $menu = vku_get_top_menu();
        
        // check
        if(!isset($_POST["ajax"])){
            drupal_goto($vku -> url());
        }
        
        drupal_json_output(array('menu' => $menu, 'vku_id' => $vku -> getId(), 'message' => $message, 'error' => 0, 'nid' => $node -> nid, 'total' => $count));
        drupal_exit();  
    }
   
    
    
    $vku = new VKUCreator($vkuid);
    $title = $vku ->getTitleTrimmed(75);
    
    // Check if in
    $kampagnen = $vku ->getKampagnen();
    
    $cta = '<a href="'. url($vku -> url()) .'" class="btn btn-primary btn-hollow">Verkaufsunterlage fertigstellen</a>';
    
    
    
    if(in_array($node -> nid, $kampagnen)){
       $message = 'Die Kampagne befindet sich bereits in Ihrer aktiven Verkaufsunterlage <strong>'. $title .'</strong>.';
       $vku ->logEvent('vku2_add_kampagne', $message);
     
       
       drupal_json_output(array('error' => 1, 'message' => $message . "<br /><br />" . $cta));
       drupal_exit();  
    }
    
    $kampas = $vku ->getKampagnen();
    $count = count($kampas);
    
    if($count >= 3){
        $message = "Sie haben bereits 3 Kampagnen in Ihrer aktiven Verkaufsunterlage <b>". $title ."</b>.";
        $vku ->logEvent('vku2_add_kampagne', 'Maximum erreicht.');
    
        
       drupal_json_output(array('error' => 1, 'message' => $message. "<br /><br />" . $cta));
       drupal_exit();  
    }
    
    $vku ->addKampagne($node -> nid);
    $count_after = $count + 1;
    
    $message = '';
    
    if($count_after == 3){
        $message = 'Die Kampagne <b>'  . $node -> title . '</b> wurde Ihrer aktiven Verkaufsunterlage <strong>'. $title .'</strong> hinzugefügt. Sie haben nun 3 Kampagnen in Ihrer aktiven Verkaufsunterlage.<br /><br />'. $cta;
    }
    else {
       $message = 'Die Kampagne <b>'  . $node -> title . '</b> wurde Ihrer aktiven Verkaufsunterlage <strong>'. $title .'</strong> hinzugefügt. <br /><br />'. $cta;
    }
    
    $kampas[] = $node -> nid;
    $kampagnen_implode = implode(",", $kampas);
    
    drupal_json_output(array('kampagnen' => $kampagnen_implode,'vku_id' => $vku -> getId(), 'message' => $message, 'error' => 0, 'nid' => $node -> nid, 'total' => $count_after));
    drupal_exit();  
}