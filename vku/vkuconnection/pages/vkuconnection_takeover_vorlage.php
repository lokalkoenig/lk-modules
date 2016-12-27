<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function vkuconnection_takeover_vorlage($vku_id, $vorlage_id){
    
   
    
    $vku = new VKUCreator((int)$vku_id);
    if(!$vku -> is('active') OR !$vku ->hasAccess()){
        $vku -> logEvent('access', 'Kein Zugriff auf VKU');
        drupal_set_message("Sie haben keinen Zugriff auf die Verkaufsunterlage");
        drupal_goto('vku');
    }
    
    $kampagnen = $vku ->getKampagnen();
   
    $vorlage = new VKUCreator((int)$vorlage_id);
    if(!$vorlage -> is('template') OR !$vku -> hasAccess()){  
        drupal_set_message("Sie haben keinen Zugriff auf die Vorlage");
        $vku -> logEvent('access', 'Kein Zugriff auf VKU');
        drupal_goto($vku -> url());
    }
    
    $new_vku_id = $vorlage ->cloneVku();
    
    $new_vku = new VKUCreator($new_vku_id);
    foreach($kampagnen as $nid){
       $new_vku ->addKampagne($nid); 
    }
    
    $vku ->remove();
    $new_vku ->logEvent('template', "VKU aus Vorlage ". $vorlage ->getTitle()  ." erstellt");
    drupal_goto($new_vku -> url());
}

