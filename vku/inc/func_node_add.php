<?php

 function _vku_add_node($node, $vku_id = NULL){
 global $user;

    $count = vku_get_active_count($user);
    // Wenn größer als 1, mehr als eine aktive VKU
    
    // New VKU Request
    if(isset($_POST["vku_new"])){
       
        
        $vku = new VKUCreator('new');
        if($_POST["vku_title"]){
           $vku ->set('vku_title', $_POST["vku_title"]);
        }
        
        $vku ->set('vku_company', $_POST["vku_company"]);
        $vku ->set('vku_untertitel', $_POST["vku_untertitel"]);
        $vku ->addKampagne($node -> nid);
        
        $max_nids = variable_get("lk_vku_add_max", 3);
        $vku ->logEvent('nodeadded', 'Kampagne ' . $node -> title . " (". $node -> nid .") wurde hinzugefügt"); 
     
        $return = array();
        $return['msg'] = 'Die Kampagne <em>'. $node -> title . "</em> wurde Ihrer Verkaufsunterlage hinzugefügt";
        $return['max'] = $max_nids;
        $return['total'] = vku_get_active_id_count();
        $return['nid'] = $node -> nid; 
        $return["link_vku"] = url($vku -> vku_url());
        $return["added"] = 1;
        
        drupal_json_output($return);
        drupal_exit();  
    }

    if($count > 1 OR true){
         if($vku_id == null){

             // List VKUs
             $return = array();
             $return['msg'] = '<p>Bitte wählen Sie eine Verkaufsunterlage, zu der Sie die Kampagne hinzufügen möchten:</p>';

             $links = array();
             $vkus = vku_get_active_ids($user);
             foreach($vkus as $v){
                $vku = new VKUCreator($v);   
                $id = $vku -> getId(); 

                if($vku -> hasKampagne($node -> nid)){
                    $links[] = '<td>'. l($vku -> getTitle(), $vku -> url(), array("html" => true)) .'</td><td class="text-right"><em>Bereits vorhanden</em></td>';     
                }    
                else {
                   $links[] = '<td>'. $vku -> getTitle() .'</td><td class="text-right"><a href="'. url("vku/" . $id . "/add/" . $node -> nid) .'" onclick="nodeadd2vku(this); return false;" class="addvkujs2">Hinzufügen</a></td>';         
                }    
                
             }   
             
               $url_new = "'" . url("vku/add/" . $node -> nid) . "'";
                
               $links[] = '<td><strong>Neue Verkaufsunterlage erstellen</strong>'
                        . '<p><label>Titel:</label><input id="vku_new_title" class="form-control form-text" name="vku_title" maxlength="75" type="" value="Ihr Angebot" /></p>'
                        . '<p><label>Unternehmen (optional):</label><input id="vku_new_company" class="form-control form-text" name="vku_company" type="" placeholder="Bäckerei Müller" value="" /></p>'
                        . '<p><label>Untertitel (optional):</label><input class="form-control form-text" id="vku_new_untertitel" name="vku_untertitel" type="" placeholder="Neue Ideen für ..." value="" /></p>'
                        . '</td><td class="text-right" style="vertical-align: bottom;">'
                        . '<button onclick="nodeadd2vku_form('.  $url_new . '); return false;" class="addvkujs2 btn btn-primary btn-sm" data-loading-text="Speichern..." id="vku_new_button">Speichern</button></td>';         
     
             $return['msg'] .='<table class="table"><tr>'. implode("</tr><tr>", $links) .'</tr></table>';

             $return['added'] = 0;
             drupal_json_output($return);
             drupal_exit();
         }   
    }
    elseif($count == 0) {
      if(function_exists('vkuconnection_get_user_templates')){
           $templates = vkuconnection_get_user_templates(); 
           
           if($templates AND $templates[0] -> vku_template_default){
               $template = new VKUCreator($templates[0] -> vku_id);
               $template ->logEvent('template', "Template wurde automatisch verwendet.");
               $vku_id = $template ->cloneVku();
           }
           else {
              $vku_new = new VKUCreator('new');
              $vku_id = $vku_new -> getId(); 
           }
       } 
       else {
          $vku_new = new VKUCreator('new');
          $vku_id = $vku_new -> getId();
       } 
    }
    else {
       $vku_id = vku_get_active_id(); 
        
    }
    
   
    
    
   
     $vku = new VKUCreator($vku_id);
     $nodes = $vku -> getKampagnen();

     if(in_array($node -> nid, $nodes)){
        if(isset($_POST["ajax"])){
            $return = array();
            $return['msg'] = 'Die Kampagne ist schon in den aktuellen Verkaufsunterlagen.<br />';
            $return['total'] = vku_get_active_id_count();
            $return["link_vku"] = url($vku -> vku_url());
            $return['nid'] = $node -> nid; 
            $return["added"] = 0;
            drupal_json_output($return);
            drupal_exit();
       }
       
       drupal_goto('vku');
       drupal_exit();
     }

    $return = array();
    $return['added'] = 1;
    $return['msg'] = 'Die Kampagne <em>'. $node -> title . "</em> wurde Ihrer Verkaufsunterlage hinzugefügt";
             
    $max_nids = variable_get("lk_vku_add_max", 3);

    if(count($nodes) >= $max_nids){
        $return['msg'] = "<div class='alert alert-danger'><span class='glyphicon glyphicon-exclamation-sign'></span> Maximal ". $max_nids ." Kampagnen pro Verkaufsunterlage möglich.</div>";
        $return['added'] = 0;
    }
    else {
         if($node -> plzaccess == false){
             $result = na_check_user_has_access(0, $node -> nid);
             if(isset($result["time"])){
                $return['msg'] = $result["reason"];     
                $return['added'] = 0;
             }
             else {
               $return['msg'] = 'Die Kampagne konnte nicht ihren VKU hinzugefügt werden';
               $return['added'] = 0;
             }
         }
         else {
            $vku ->logEvent('nodeadded', 'Kampagne ' . $node -> title . " (". $node -> nid .") wurde hinzugefügt"); 
            $vku -> addKampagne($node -> nid);
            $vku_id = $vku -> getId();
         }
    }

    if(isset($_POST["ajax"])){ 
            $return['max'] = $max_nids;
            $return['total'] = vku_get_active_id_count();
            $return['nid'] = $node -> nid; 
            $return["link_vku"] = url($vku -> vku_url());
            //$return['msg'] = 'Die Kampagne wurde der Verkaufsunterlage hinzugefügt';
            drupal_json_output($return);
            drupal_exit();
    }
       
    drupal_set_message($return['msg']); 
    drupal_goto('vku');  
  }


?>