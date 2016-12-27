<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function _lk_generate_search_kampagne($node){
  
  
   $content = array();
   $content[] = $node -> title;
   $content[] = $node -> field_sid['und'][0]['value'];
   $content[] = $node -> field_kamp_untertitel['und'][0]['value'];
   $content[] = $node -> field_kamp_teasertext['und'][0]['value'];
   
   // Themenbereiche
   if(isset($node->field_kamp_themenbereiche['und'])){
      foreach($node->field_kamp_themenbereiche['und'] as $tax){
          $term = taxonomy_term_load($tax["tid"]);
          $content[] = $term -> name;
          $content[] = $term -> description;
      }
   }
   
   if(isset($node->field_kamp_anlass['und'])){
      foreach($node->field_kamp_anlass['und'] as $tax){
          $term = taxonomy_term_load($tax["tid"]);
          $content[] = $term -> name;
          $content[] = $term -> description;
      }
   }
   
   if(isset($node->field_kamp_kommunikationsziel['und'])){
      foreach($node->field_kamp_kommunikationsziel['und'] as $tax){
          $term = taxonomy_term_load($tax["tid"]);
          $content[] = $term -> name;
      }
   }
   
   
  if(isset($node -> medien)){
     foreach($node -> medien as $m){
       $content[] = $m -> title;
       
       if(isset($m->field_medium_beschreibung['und'][0]['value'])){
          $content[] = $m->field_medium_beschreibung['und'][0]['value'];
       }
     }
  }
    
  return implode("\n", $content);
}



function computed_field_field_kamp_suche_compute(&$entity_field, $entity_type, 
    $entity, $field, $instance, $langcode, $items){
    $entity_field[0]["value"] = _lk_generate_search_kampagne($entity);
}

function computed_field_field_kamp_status_compute(&$entity_field, $entity_type, 
    $entity, $field, $instance, $langcode, $items){
  
    if(isset($entity -> is_new) AND $entity -> is_new){
      $entity_field[0]["value"] = 'new';
    }
}


