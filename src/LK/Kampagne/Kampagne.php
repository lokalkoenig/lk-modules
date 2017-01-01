<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Kampagne;
use LK\Kampagne\AccessInfo;

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
         $node -> online = $node -> status;
         
         if(!isset($this -> node -> loadedmedias)){
             //dpm($this -> node -> nid); 
             $this -> initMedias();
         }
    }
    
    
    function getNode(){
        return $this -> node;
    }
    
    
    /**
     * Initialize Kampagne by loading Medias
     */
    private function initMedias(){
        
        $this -> node -> loadedmedias = true;
        $this -> node -> lkstatus = @$this -> node -> field_kamp_status["und"][0]["value"];   
        $this -> node -> plzaccess = AccessInfo::loadAccess($this -> node -> nid); 
        $this -> node -> sid = @$this -> node -> field_sid["und"][0]["value"];   
        
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
     * Gets the Searchable String for SOLR
     * 
     * @return String
     */
    function getSearchString(){
      
      $node = $this -> node;
      
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
    
    
    /**
     * Gets basic Access-Information which gets Attached to the Node
     * 
     * @param String $view_mode Drupal View Modde
     */
    function getAccessInformation($view_mode){
       
      $node = &$this -> node;
      $account = \LK\current();
     
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
      
      // Show Access-Info
      if($view_mode != "grid"):
        
        if($node -> plzaccess == false && $account->isAgentur() === FALSE){
            $result = AccessInfo::getUserBasedAccess($user -> uid, $node -> nid);
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
      
      
     if($view_mode != 'grid'):
        $node -> alerts = AccessInfo::hasAccess($node -> nid);
       
        if($node -> plzaccess == false){
            
            $node -> verlags_sperre = AccessInfo::get_verlag_plz_sperre($node -> nid, true); 
            if($node -> verlags_sperre AND $node -> verlags_sperre["uid"] == $account ->getUid()){
                $node -> alerts = false;  
            } 
        }
        
        // Alert Link
        if($node -> alerts AND $view_mode != 'full'){
            $node -> basic_links["alerts"] = array();
            $node -> basic_links["alerts"]["title"] = '<span class="glyphicon glyphicon-exclamation-sign" data-toggle="tooltip" title="Verwendung anzeigen"></span>';
            
            if($account ->isModerator()){
              $result = AccessInfo::getAccessCount($node -> nid, user_load($account->uid)); 
              $node -> basic_links["alerts"]["title"] .= '<sup><small>' . $result . '</small></sup>';
            }
            
            $node -> basic_links['alerts']["href"] = url("nodeaccess/" . $node -> nid, array("absolute" => true));
            $node -> basic_links["alerts"]["attributes"] = array('class' => array("new-style-icon alert-icon"));
        }
        
        if($node -> online && !$account ->isAgentur()){
           $node -> basic_links["recomend"] = array();
           $node -> basic_links["recomend"]["title"] = '<span class="glyphicon glyphicon-envelope" data-toggle="tooltip" title="Versenden Sie diese Kampagne"></span>';
           $node -> basic_links['recomend']["href"] = "node/" . $node -> nid;
           $node -> basic_links["recomend"]["attributes"]["class"] = array("recomendnode new-style-icon");
           $node -> basic_links["recomend"]["attributes"]["nid"] = $node -> nid;
        }
        
        // different send method for moderators
        if($account ->isModerator() && $node -> online){
            $node -> basic_links['recomend']["href"] = url("messages/new", array("absolute" => true, "query" => array("nid" => $node -> nid)));
            $node -> basic_links["recomend"]["attributes"] = array('class' => array("new-style-icon"));
        }
        
        if($view_mode == 'teaser' && $account ->isModerator() && $this -> getLizenzenCount()){
             $node -> basic_links["lizenz"] = array();
             $node -> basic_links["lizenz"]["title"] = '<span class="glyphicon glyphicon-euro" data-toggle="tooltip" title="Kampagne hat Lizenzen"></span>';
             $node -> basic_links['lizenz']["href"] = "node/" . $node -> nid . "/lizenzen";
             $node -> basic_links["lizenz"]["attributes"]["class"] = array("new-style-icon");
        }
        
     endif;
      
      // Attache also Format information
      $this -> getFormatInformation($view_mode);
      
      if($view_mode === 'full'){
        $this ->getFullViewAccessInformation();
      }
    }
    
    
    private function getFullViewAccessInformation(){
    global $user;
        
         $node = &$this -> node;  
           // Get use count
         if(lk_is_moderator()){
            $result = AccessInfo::getAccessCount($node -> nid, $user); 
            
            if($result){
                $node -> sperre_hinweis = '<h4 style="margin-top: 0">Kampagnenverwendung</h4>' . theme("lk_vku_usage", array('class' => 'clearfix', "account" => $user, "entries" => AccessInfo::getUserDetails($node -> nid, $user)));
            }

            return ;
         }

         $node -> lizenz = false;

         // If current lizenz
         if($node -> plzaccess == false){
            $current_lizenz = vku_user_has_lizenz_node($node -> nid, $user);
            if($current_lizenz){     
               //$node -> sperre_hinweis = 'Sie haben diese Kampagne lizenziert.'; 
               $node -> lizenz = $current_lizenz; 
               return ;
            }
         }

         if($node -> verlags_sperre AND isset($node -> verlags_sperre["info"])){
            $node -> sperre_hinweis = theme("lk_node_vku_info", array("info" => $node -> verlags_sperre));
            return ; 
         }

         // When there are Licences in the Area
         if($node -> plzaccess == false AND !$node -> verlags_sperre){
             $test = (get_ausgaben_access_nid($node -> nid, $user, true));

             if($test AND $test["count"]){
                $test["class"] = 'well clearfix';
                $node -> sperre_hinweis .= theme("lk_vku_lizenz_usage", $test, true);
                return ;
             }   
         }

         // Show Verlagssperren or Notifications
         if($node -> plzaccess == true OR $node -> verlags_sperre) {
            // Checken ob andere User VKU's mit der Kampagne erstellt haben
            $result = AccessInfo::getAccessCount($node -> nid, $user);
            
            if($result){
                $node -> sperre_hinweis = '<h4 style="margin-top: 0">Kampagnenverwendung</h4>' . theme("lk_vku_usage", array('class' => 'clearfix', "account" => $user, "entries" => vku_get_use_details($node -> nid, $user)));
                return ;
            }
        }   
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
    
    /**
     * Get the Lizenzen
     * 
     * @return Integer
     */
    function getLizenzenCount(){
      $dbq = db_query("SELECT count(*) as count FROM lk_vku_lizenzen WHERE nid='". $this -> node -> nid ."'");
      $result = $dbq -> fetchObject();
      return $result -> count;
    }
}
