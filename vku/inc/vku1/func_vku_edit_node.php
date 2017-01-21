<?php




/** 
 * GETs an POST request to save 
 * 
 * @param VKUCreator $vku
 * @param type $seite
 * @return type
 */
function vku_edit_inner_node(VKUCreator $vku, $seite){
    
  $node = node_load($seite["data_entity_id"]);
  $message = $vku ->logEvent("kampagne-setting", "Die Kampagnen-Ansichtseinstellungen für die Kampagne \"". $node -> title ."/". $node -> nid ."\" wurden angepasst");
 
  $values = $_POST; 
  $seite["data_serialized"] = serialize($values);
  
  $pdf = generate_pdf_object_verlag(0);
  $pdf -> disableImagerendering();
   
  vku_generate_pdf_node($vku, $seite, $pdf);
   
  $values["pages"] = $pdf -> PageNo();
  $vku -> setPageSerializedSetting($seite["id"], $values);
  $seite["data_serialized"] = serialize($values);
  
  $return = array("message" => $message, 
                  'pages' => $values["pages"], 
                  'error' => 0);
  
  $return["val"] = $seite;
   
  drupal_json_output($return);
  drupal_exit();    
}

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function vku_form_vku_edit_kampagne($form, $form_state, $vku_id, $seite_id, $show_colors = true){
    
    $form["#vku_id"] = $vku_id;
    $form["#seite_id"] = $seite_id;
    
    $form["#attributes"]["class"][] = 'node-kampagne-settings';
    $form["#action"] = url('vku/'. $vku_id  .'/edit/' . $seite_id);
    
    $vku = new VKUCreator($vku_id);
    $seite = $vku ->getPage($seite_id);
    $node = node_load($seite["data_entity_id"]);
    
    _vku_load_vku_settings_node($node, $seite);
    
    if($seite["data_serialized"]){
         $settings = unserialize($seite["data_serialized"]);
    }
    else {
        $settings = array();
    }
      
       $form['intro'] = array(
             '#markup' => '<p class="help-block">Deaktivieren Sie die Bereiche, die nicht in der Dokumentausgabe erscheinen sollen.</p><hr />',
        );   
        
        $form['desc'] = array(
             '#type' => 'checkbox',
            '#title' => '<div class="clearfix"><big>Allgemeine Kampagnenbeschreibung</big></div>' ,
            '#default_value' => 1  

          );   
    
         //$form['node_general_text'] = array(
         //    '#markup' => '<hr /><h3>Medien & Varianten</h3>',
         // );   
        
        
    foreach($node -> medien as $media){
     
    
     $tax = taxonomy_term_load($media->field_medium_typ['und'][0]['tid']);
     if($tax->description){
         $term_title = $tax->description;
     }
     else {
         $term_title = $tax -> name;
     }
     
        
     if(!$media->field_medium_main_reference){
         $form['media_' . $media -> id . "_sep"] = array(
            '#markup' => '<hr />',
        );   
        
        $filetype = _lk_get_medientyp_print_or_online($media->field_medium_typ['und'][0]['tid']);
        if($filetype == 'print'){
            $ext_title = 'Printanzeige';
        }
        else {
            $ext_title = 'Online-Anzeige';
        }
         
        $form['media_' . $media -> id . "_overview"] = array(
         '#type' => 'checkbox',
         '#title' => '<strong><big>Beschreibungstext '. $ext_title .'</big></strong>' ,
         '#default_value' => 1  
       );   
     }   
     
     $form['media_' . $media -> id] = array(
         '#type' => 'checkbox',
         '#title' => '<strong>Farbvarianten:</strong> '. $media -> title  .' ('. $term_title .')' ,
         '#default_value' => 1  
       );   
    }
    
     $form['cancel'] = array(
         
        '#markup' => '<hr /><div class="pull-right"><a href="#" class="cancel">Abbrechen</a></div>',
      );
    
    
     $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Save'),
      );
     
     if($settings):
         while(list($key, $val) = each($form)):
           if(isset($val["#type"]) AND $val["#type"] == 'checkbox' AND isset($settings[$key])):
               $form[$key]["#default_value"] = 1;
           
           elseif(isset($val["#type"]) AND $val["#type"] == 'checkbox'): 
               $form[$key]["#default_value"] = 0;
            endif;
        endwhile;
     endif;
         
return $form;    
}