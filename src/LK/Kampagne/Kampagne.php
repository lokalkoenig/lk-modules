<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Kampagne;

/**
 * Description of Kampagne
 *
 * @author Maikito
 */
class Kampagne {
    //put your code here
    
    var $version = 1;
    var $node = null;
    
    
    function __construct(\stdClass $node) {
         $this -> node = &$node;
         
         if(!isset($this -> node -> loadedmedias)){
             //dpm($this -> node -> nid); 
             $this -> initMedias();
         }
    }
    
    
    function getNode(){
        return $this -> node;
    }
    
    private function initMedias(){
        
        $this -> node -> loadedmedias = true;
        $this -> node -> lkstatus = $this -> node -> field_kamp_status["und"][0]["value"];   
        $this -> node -> plzaccess = \LK\Kampagne\AccessInfo::loadAccess($this -> node -> nid); 
        
        $medien = [];
        
        $result = db_query('SELECT field_medium_node_nid as nid, entity_id, entity_type '
            . 'FROM {field_data_field_medium_node} '
            . "WHERE entity_type='medium' AND field_medium_node_nid = :nid", array(':nid' => $this -> node -> nid));
        foreach ($result as $record) {
            $medien[] = entity_load_single($record -> entity_type, $record -> entity_id);
        }
        
        $medien_print = array();
        $medien_online = array();
        
        foreach($medien as $media){
            $test = \_lk_get_medientyp_print_or_online($media->field_medium_typ['und'][0]['tid']);
            if($test == 'print'){
                $medien_print[] = $media; 
            }
            else {
                $medien_online[] = $media; 
            }
        }
            
        foreach ($medien_online as $medium){
            $medien_print[] = $medium;
        }
        
        $this -> node -> medien = $medien_print;
    }
    
    
    /**
     * Gets basic Access-Information which gets Attached to the Node
     * 
     * @param String $view_mode Drupal View Modde
     */
    function getAccessInformation($view_mode){
       
      $node = &$this -> node;
      $account = \LK\current();
      
      if(!isset($node -> online)){
        $node -> online = false;
      }
    
      $node -> kid = $node->field_sid['und'][0]['value'];
      $node -> vku_url = false;
    
      $node -> vku_can = true;
      $node -> merkliste_can = true;
      $node -> in_vku = false; 
            
      if($node -> online == false || $account -> isAgentur()){
          $node -> vku_can = false;
          $node -> merkliste_can = false;
      }
      elseif($node -> plzaccess == false){ 
         $node -> vku_can = false; 
      }
    
      $node -> sperre_hinweis = '';
      $node -> basic_links = array();
      $node -> verlags_sperre = false;
      
      // Merkliste
      $ml = array();
      $ml["title"] = 'Merkliste';
         
      if($node -> merkliste_can){
          $ml["href"] = 'node/' . $node -> nid;
          $ml["title"] = 'Merkliste';
          $ml["attributes"]["class"] = array('merklistejs merkliste');
          $ml["attributes"]["mlid"] = '';
          $ml["attributes"]["onclick"] = 'return false;';
          $ml["attributes"]["items"] = '';
          $ml["attributes"]["nid"] = $node -> nid;

          if($node -> merkliste) {
              $ml["attributes"]["class"][] = 'on';
              $ml["attributes"]["mlid"] = $node -> merkliste_id;
              $ml["attributes"]["items"] = $node -> merkliste_title;
          }
      }
      else {
           $ml["href"] = 'node/' . $node -> nid;
           $ml["attributes"]["class"] = array('merkliste');
           $ml["attributes"]["data-toggle"] = 'tooltip';
           $ml["attributes"]["onclick"] = 'return false;';
           $ml["attributes"]["title"] = 'Diese Funktion ist für Sie nicht verfügbar';  
      }
      
      
      $node -> merkliste_link = $ml;
      
      // Verkaufsunterlage
       $vku_link = array();
       $vku_link["title"] = 'Verkaufsunterlage';
       
       if($node -> vku_can){
          if(vku_is_update_user()){
             $vku_link["href"] = 'vku/add_active/' . $node -> nid;
             $vku_link["attributes"]["class"] = array('addvku2js vku-added');
             $vku_link["attributes"]["data-nid"] = $node -> nid;
          }
          else {
             $vku_link["href"] = 'vku/add/' . $node -> nid;
             $vku_link["attributes"]["class"] = array('addvkujs');
          }
     
          if($node -> vku_url){
             $vku_link["href"] = $node -> vku_url;
             $vku_link["attributes"]["class"] = array('addvkujs-active');
          }
      }
      else {
        $vku_link["href"] = 'node/' . $node -> nid;
        $vku_link["attributes"]["class"] = array('addvkujs-no');   
        $vku_link["attributes"]["onclick"] = 'return false;';
        $vku_link["attributes"]["data-toggle"] = 'tooltip';
        $vku_link["attributes"]["title"] = 'Diese Funktion ist für Sie nicht verfügbar'; 
        $node -> vku_active = 0;   
      }
    
      $node -> vku_link = $vku_link;
      
      if($view_mode != "grid"):
        
        if($node -> plzaccess == false && $account->isAgentur() === FALSE){
            $result = na_check_user_has_access($user -> uid, $node -> nid);
            $node -> plzinfo = $result;

            if($node -> plzinfo["access"] == false){
                 $node -> sperre_hinweis = '<p class="text-center"><b>' . $node -> plzinfo["reason"] . '</b></p>'; 
            }

            if($node -> verlags_sperre){
               if($account -> getUid() == $node -> verlags_sperre["uid"]){
                  $vku = new \VKUCreator($node -> verlags_sperre["vku_id"]);
                  $info = $vku -> hasPlzSperre();
                  $url = $vku -> url();
                  $node -> vku_url = $vku -> url();
                  $node -> sperre_hinweis = '<p>Die Kampagne wurde bis zum '. date('d.m.Y', $info["until"]) .' für folgende Ausgaben für Sie reserviert: ' . implode(" ", $info["ausgaben"]) . ' / '. l("Zu Ihrer Verkaufsunterlage", $url) .'</p>';
                 }
               else {
                  $node -> sperre_hinweis = '<p>Die Kampagne wird innerhalb Ihres Verlages verwendet.</p>'; 
               } 
            }
        }   
        
      endif;
      
      // Attache also Format information
      $this -> getFormatInformation($view_mode);
    }
    
    
    
    function getFormatInformation($view_mode){
     
      $node = &$this -> node;
      
      $online_f = explode(",", $node->field_format_kamp_online['und'][0]['value']);
      $online_formate = $online_f[0];
      $online_formate_count = count($online_f);
 
      if(!\lk_upgrade_medienformate() OR ($online_formate_count) == 1){
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

    if(!\lk_upgrade_medienformate() OR ($print_formate_count) == 1){
      $overview_print = '<img src="/sites/all/themes/bootstrap_lk/design/icon-printanzeige.png" width="20" height="20" /><span class="k-desc">'. $print_formate .'</span>';
    }
    else {
      $overview_print =  '<span class="multiple-formate" data-toggle="tooltip" title="Diese Kampagne enthält mehrere Formate: '. implode(", ", $print_f) .'"><span class="label label-primary label-lk label-lk-print"><sup>' . $print_formate_count .'</sup><strong>P</strong></span><span class="k-desc">u.a. '.  $print_formate . '</span></span>';    
    }

    $node -> formate_print = $overview_print;
    $node -> formate_online = $overview_online;
    
  }
    
    /**
     * Removes a Kampagne and its relations
     */
    function remove(){
        if(isset($this -> node -> medien)){
            // Medien
            foreach($this -> node -> medien as $entity){
              entity_delete('medium', $entity -> id);    
            }   
        }
  
        // remove PLZ-Sperren        
        $manager = new \LK\Kampagne\SperrenManager();  
        $result = db_query('SELECT field_medium_node_nid as nid, entity_id, entity_type FROM {field_data_field_medium_node} WHERE field_medium_node_nid =:nid', array(':nids' => $this -> node -> nid));
        foreach ($result as $record) {
           if($record -> entity_type == "plz"){
             $manager ->removeSperre($record -> entity_id);
           }
        }
    }   
}
