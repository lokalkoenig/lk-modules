<?php

/* 
 * VKU- Create Vorlage
 * @since 2015-11-09
 */

function vkuconnection_create_vorlage(){
global $user;
  
  $user_id = $user -> uid;  
  $arguments = array('uid' => $user_id); 
  $vku = new VKUCreator('new', $arguments);
  
  if(!$vku -> is()){
    drupal_set_message("Ein Fehler ist aufgetreten");
    drupal_goto("vku");
  }

  $id = $vku -> getId();
  
  $vku_new = new VKUCreator($id);
  $vku_new -> setStatus('template');
  $vku_new -> set('vku_template_title', 'Vorlage vom ' . date("d.m.Y"));
 
  drupal_set_message("Eine neue Verkaufsunterlagenvorlage wurde erstellt. Sie können diese nun bearbeiten.");
  drupal_goto("vku/" . $id);
}

