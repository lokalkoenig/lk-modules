<?php

/* 
 * VKU- Create Vorlage
 * @since 2015-11-09
 */

function vkuconnection_create_vorlage(){

  $vku_new = \LK\VKU\VKUManager::createEmptyVKU(\LK\current());
  $vku_new -> setStatus('template');
  $vku_new -> set('vku_template_title', 'Vorlage vom ' . date("d.m.Y"));
 
  drupal_set_message("Eine neue Verkaufsunterlagenvorlage wurde erstellt. Sie können diese nun bearbeiten.");
  drupal_goto("vku/" . $vku_new->getId());
}

