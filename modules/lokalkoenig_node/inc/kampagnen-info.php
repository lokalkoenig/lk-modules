<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function lokalkoenig_node_prepare_view($node, $view_mode = 'teaser'){
global $user;

    if(!isset($node -> online)){
       $node -> online = false;
    }
    
    $node -> kid = _lk_get_kampa_sid($node);
    $node -> vku_url = false;
    
    $node -> vku_can = true;
    $node -> merkliste_can = true;
    $node -> in_vku = false; 
            
    if($node -> online == false OR lk_is_agentur()){
        $node -> vku_can = false;
        $node -> merkliste_can = false;
    }
    elseif($node -> plzaccess == false){ 
       $node -> vku_can = false; 
    }
    
    $node -> sperre_hinweis = '';
    $node -> basic_links = array();
    $node -> verlags_sperre = false;
    
    if($view_mode != 'grid'):
        $node -> alerts = _lokalkoenig_node_access_info_count($node -> nid);
       
        if($node -> plzaccess == false){
            $node -> verlags_sperre = get_verlag_plz_sperre($node -> nid, true); 
            if($node -> verlags_sperre AND $node -> verlags_sperre["uid"] == $user -> uid){
                $node -> alerts = false;  
            } 
        }
        
        // Alert Link
        if($node -> alerts AND $view_mode != 'full'){
            $node -> basic_links["alerts"] = array();
            $node -> basic_links["alerts"]["title"] = '<span class="glyphicon glyphicon-exclamation-sign" data-toggle="tooltip" title="Verwendung anzeigen"></span>';
            
            if(lk_is_moderator()):
                   $result = vku_get_use_count($node -> nid, $user); 
                   $node -> basic_links["alerts"]["title"] .= '<sup><small>' . $result . '</small></sup>';
            endif;
            
            $node -> basic_links['alerts']["href"] = url("nodeaccess/" . $node -> nid, array("absolute" => true));
            $node -> basic_links["alerts"]["attributes"] = array('class' => array("new-style-icon alert-icon"));
        }
    
    if(lk_is_moderator()){
        // Send Moderator
        if($node -> online){
            $node -> basic_links["recomend"] = array();
            $node -> basic_links["recomend"]["title"] = '<span class="glyphicon glyphicon-envelope" data-toggle="tooltip" title="Versenden Sie diese Kampagne"></span>';
            $node -> basic_links['recomend']["href"] = url("messages/new", array("absolute" => true, "query" => array("nid" => $node -> nid)));
            $node -> basic_links["recomend"]["attributes"] = array('class' => array("new-style-icon"));
        }
       
        if($view_mode != 'full' AND node_has_lizenz_allready($node -> nid)){
              $node -> basic_links["lizenz"] = array();
              $node -> basic_links["lizenz"]["title"] = '<span class="glyphicon glyphicon-euro" data-toggle="tooltip" title="Kampagne hat Lizenzen"></span>';
              $node -> basic_links['lizenz']["href"] = "node/" . $node -> nid . "/lizenzen";
              $node -> basic_links["lizenz"]["attributes"]["class"] = array("new-style-icon");
            }
        }
        elseif($node -> online){
                // Send Kampagne
                $node -> basic_links["recomend"] = array();
                $node -> basic_links["recomend"]["title"] = '<span class="glyphicon glyphicon-envelope" data-toggle="tooltip" title="Versenden Sie diese Kampagne"></span>';
                $node -> basic_links['recomend']["href"] = "node/" . $node -> nid;
                $node -> basic_links["recomend"]["attributes"]["class"] = array("recomendnode new-style-icon");
                $node -> basic_links["recomend"]["attributes"]["nid"] = $node -> nid;
                // Check Sperre     
            
        } 
    
    endif;
      
   if($node -> plzaccess == false){
      $result = na_check_user_has_access($user -> uid, $node -> nid);
      
      $node -> plzinfo = $result;
      
      if($node -> plzinfo["access"] == false){
           $node -> sperre_hinweis = '<p class="text-center"><b>' . $node -> plzinfo["reason"] . '</b></p>'; 
      }
      
      if($node -> verlags_sperre){
         if($user -> uid == $node -> verlags_sperre["uid"]){
            $vku = new VKUCreator($node -> verlags_sperre["vku_id"]);
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
   
   if($view_mode == 'full'){
      _lokalkoenig_node_extend_access_info($node);    
   }
   
   if($node -> merkliste_can){
        $ml = array();
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
        $ml["title"] = 'Merkliste';
        $ml["attributes"]["class"] = array('merkliste');
        $ml["attributes"]["data-toggle"] = 'tooltip';
        $ml["attributes"]["onclick"] = 'return false;';
        $ml["attributes"]["title"] = 'Diese Funktion ist für Sie nicht verfügbar';  
   }
   
   
   if($node -> vku_can){
     $vku_link = array();
     
     if(vku_is_update_user()){
        $vku_link["href"] = 'vku/add_active/' . $node -> nid;
        $vku_link["title"] = 'Verkaufsunterlage';
        $vku_link["attributes"]["class"] = array('addvku2js vku-added');
        $vku_link["attributes"]["data-nid"] = $node -> nid;
     }
     else {
         $vku_link["href"] = 'vku/add/' . $node -> nid;
        $vku_link["title"] = 'Verkaufsunterlage';
        $vku_link["attributes"]["class"] = array('addvkujs');
     }
     
     if($node -> vku_url){
        $vku_link["href"] = $node -> vku_url;
        $vku_link["attributes"]["class"] = array('addvkujs-active');
     }
   }
   else {
     $vku_link = array();
     $vku_link["href"] = 'node/' . $node -> nid;
     $vku_link["title"] = 'Verkaufsunterlage';
     $vku_link["attributes"]["class"] = array('addvkujs-no');   
     $vku_link["attributes"]["onclick"] = 'return false;';
     $vku_link["attributes"]["data-toggle"] = 'tooltip';
     $vku_link["attributes"]["title"] = 'Diese Funktion ist für Sie nicht verfügbar'; 
     $node -> vku_active = 0;   
   }
    
   $node -> merkliste_link = $ml;
   $node -> vku_link = $vku_link;
  }   
    
 
function _lokalkoenig_node_extend_access_info($node){
global $user;

    // This don't need 
    if(!lk_vku_access()){
         return ;
     }
     
     // Get use count
     if(lk_is_moderator()){
        $result = vku_get_use_count($node -> nid, $user);
        if($result){
            $node -> sperre_hinweis = '<h4 style="margin-top: 0">Kampagnenverwendung</h4>' . theme("lk_vku_usage", array('class' => 'clearfix', "account" => $user, "entries" => vku_get_use_details($node -> nid, $user)));
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
        $result = vku_get_use_count($node -> nid, $user);
        if($result){
            $node -> sperre_hinweis = '<h4 style="margin-top: 0">Kampagnenverwendung</h4>' . theme("lk_vku_usage", array('class' => 'clearfix', "account" => $user, "entries" => vku_get_use_details($node -> nid, $user)));
            return ;
        }
    }   
}