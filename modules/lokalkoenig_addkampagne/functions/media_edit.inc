<?php

/** Medium löschen */
function _lokalkoenig_deletekampagne_delete_medium(&$form, &$form_state){
  drupal_get_messages();
  drupal_set_message("Der Upload wurde gelöscht.");
  lk_log_kampagne(arg(1), 'Upload gelöscht'); 
  $form_state['redirect'] = 'node/' . arg(1) . "/media";   
  _lokalkoenig_change_media_count(arg(1));  
}

/** Medium editieren */
function _lokalkoenig_editkampagne_mediumedit_submit(&$form, &$form_state){
   drupal_get_messages();
   drupal_set_message("Die Einstellungen wurden gespeichert");
   lk_log_kampagne(arg(1), 'Upload editiert'); 
  
   $form_state['redirect'] = 'node/' . arg(1) . "/media";    
   _lokalkoenig_change_media_count(arg(1));    
}

/** Medium hinzufügen  */
function _lokalkoenig_addkampagne_addmedium(&$form, &$form_state){
   drupal_get_messages();
   drupal_set_message("Der Upload wurde erfolgreich hinzugefügt");
   lk_log_kampagne(arg(1), 'Upload hinzugefügt'); 
   
   
  
   $form_state['redirect'] = 'node/' . arg(1) . "/media";    
   _lokalkoenig_change_media_count(arg(1));    
}



/** Zeigt einen View an
 *  der alle Medien einer Kampagne anzeigt
 */  
function lokalkoenig_addkampagne_page_media($node){
  $node = node_load(arg(1));
  $tax = taxonomy_term_load($node->field_kamp_preisnivau['und'][0]['tid']);
  //dpm($tax);
  pathtitle('node/x/media');
  $return = '<div class="well"><h3 style="margin-top: 0;"><span class="glyphicon glyphicon-chevron-right"></span> '. $tax -> name.'</h3>
  <p>' . $tax -> description . '</p>
  
  </div>'; //views_embed_view('intern_kampagnen_medien', 'attachment_1', $node -> nid);
  $taxos = _lokalkeonig_get_missing_mediums($node);
 
  if(!isset($node -> medien)){
    $node -> medien = array();
  }
  
  $return .= theme('lk_node_show_medien_mod', array('node' => $node,
                                                    'medien' => $node -> medien, 
                                                    'info' => $taxos));
 
  if($taxos["print"] == 0 OR $taxos["online"] == 0){
    drupal_goto('node/'. $node -> nid .'/addmedia');
  
  }


  if((count($taxos["select"]) == 0 AND !$taxos["individuell"]) OR $taxos["incomplete"] == false){
     
      return 
        '<div class="well well-white">Sie haben alle notwendigen Medien zu diesem Paket hochgeladen. <div class="pull-right">'. lklink("Zur Vorschau", "node/" . arg(1), 'saved', 'btn btn-success btn-sm') .'</div></div>' . $return;
  }
       
  
  
  return $return;
  
  if($taxos['online'] == 0 OR $taxos['print'] == 0){
      $button = '<a href="'. url("node/" . arg(1) . "/addmedia") .'" class="btn btn-success"><span class="glyphicon glyphicon-cloud-upload"></span> Medium hochladen</a>';
  
  }
  else {
    
 $button = ' 
  <div class="btn-group">
  <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
    <span class="glyphicon glyphicon-cloud-upload"></span> Weitere Medien hochladen <span class="caret"></span>
  </button>
  
  <ul class="dropdown-menu" role="menu">
  ';
   
   while(list($key, $val) = each($taxos["select"])){
     $button .= '<li><a href="'. url("node/" . arg(1) . "/addmedia", array("query" => array("medium" => $key))) .'">'. $val .'</a></li>';
   }
    
  $button .= ' </ul>
</div>';
  
  }
  
  $return .= '<div class="pull-right">' .$button . '</div>';
  
  
return $return;  
}


/** Medium Löschen Formular */
function lokalkoenig_addkampagne_page_deletemedia($node, $entity){
  
  //$node = node_load(arg(1));
  module_load_include('inc', 'eck', 'eck.entity');
  $form = eck__entity__delete('medium', 'medium', $entity);
  
return $form;   
}


/** Medium editieren Formular */
function lokalkoenig_addkampagne_page_editmedia($node, $entity){
   
    drupal_set_title("Upload bearbeiten");
    //$node = node_load($node -> nid);
    module_load_include('inc', 'eck', 'eck.entity');
    $form = eck__entity__edit('medium', 'medium', $entity);
    
return $form;
}

/** Medium hinzufügen */
function lokalkoenig_addkampagne_page_addmedia($node){
  drupal_set_title("Upload hinzufügen");
  
  module_load_include('inc', 'eck', 'eck.entity');
  $form = eck__entity__add('medium', 'medium');
 
 return $form;
}


function ___medium_new_form_css($form, &$form_state){
  
  $path = drupal_get_path('module', 'lokalkoenig_addkampagne');
  drupal_add_css($path . '/css/node.css');
 
return $form;  
}


function lokalkoenig_addkampagne_form_eck__entity__form_add_medium_medium_alter(&$form, &$form_state, $form_id){
 
}




function _lokalkoenig_addkampagne_media_formadmin(&$form, &$form_state, $form_id) {

   switch($form_id){
      // Medium editieren
      case 'eck__entity__form_edit_medium_medium':
         
         
         // Get the Node Nid from the Entity
         if(!isset($form["#lk_node_nid"])){
             if(isset($form['entity']['#value']->field_medium_node['und'][0]['nid'])){
                 $form["#lk_node_nid"] = $form['entity']['#value']->field_medium_node['und'][0]['nid'];
             }
         }
         
         
         $form['title']['#title'] = "Titel des Mediums";
         $form['field_medium_node']['#access']  = false;
         $form['field_medium_typ']['#access']  = false;
         $form['field_medium_main_reference']['#access']  = false;
        
        
         $node = node_load($form["#lk_node_nid"]);
         $taxos = _lokalkeonig_get_missing_mediums($node);
         
         _lk_kampa_online_warning($node, $form);
         
         
         
         /**
         // User kann Medien-Format verändern
         $type = $form['entity']['#value']->field_medium_typ['und'][0]['tid'];
         
         $type = _lk_get_medientyp_print_or_online($type);
         $select = array();
         
         while(list($tid, $val) = each($taxos["need"])){
            if($val -> is == $type){
              $select[$val -> tid] = ucfirst($val -> is) . " - " . $val -> name;
            }
         }
         
         $form['field_medium_typ']['und']['#options'] = $select;   
         */
          
         
         // Disable things
         if(isset($form['entity']['#value']->field_medium_main_reference['und'][0]['target_id'])){
              $form['field_medium_bild']['#required'] = false;
              $form['field_medium_beschreibung']['#required'] = false;
              $form['field_medium_bild']['und'][0]['#required'] = false;
              $form['field_medium_beschreibung']['und'][0]['#required'] = false;
              $form['field_medium_beschreibung']['und']['#required'] = false;
              $form['field_medium_beschreibung']['und'][0]['value']['#required'] = false;
              $form['field_medium_beschreibung']['und'][0]["#access"] = false;
              $form['field_medium_bild']['und'][0]["#access"] = false;
              $form['#fieldgroups']['group_medium_presetation']->format_type = 'hidden';
              
         }
         
         
         array_push($form['#validate'], '_lokalkoenig_make_varianten_titles_requiered'); 
         $form["#after_build"][] = '___medium_new_form_css';
         $form["#group_children"]["test"] = 'group_field_medium_files';
       
         pathtitle('node/x/media/x/edit', '<span class="label label-success">' . _lk_get_medientyp_with_type($form['entity']['#value']->field_medium_typ['und'][0]['tid']) . '</span>');
        _formlk($form);
        array_push($form['#submit'], '_lokalkoenig_editkampagne_mediumedit_submit');
        break;
      
      
      
      
      // Medium hinzufügen
      case 'eck__entity__form_add_medium_medium':
        
          
        $form["#after_build"][] = '___medium_new_form_css';
        array_push($form['#validate'], '_lokalkoenig_make_varianten_titles_requiered'); 
        pathtitle('node/x/addmedia');
       
        $form['field_medium_node']['#access']  = false;      
        $form['title']['#title'] = "Titel des Mediums";
        $form['field_medium_main_reference']['#access']  = false;
        
        if(isset($form["#lk_node_nid"])){
          $form["lk_nid"] = array('#type' => 'hidden', '#value' => $form["#lk_node_nid"]);
        }
        elseif(isset($form_state["input"]["lk_nid"])) {
           $form["lk_nid"] = array('#type' => 'hidden', '#value' => $form_state["input"]["lk_nid"]);
           $form["#lk_node_nid"] = $form_state["input"]["lk_nid"];
        }
        
        
          

       $node = node_load($form["#lk_node_nid"]);
        
        if($node){
          
          $taxos = _lokalkeonig_get_missing_mediums($node);
          

          // Wir brauchen ein Print-Medium
          if($taxos["print"] == 0){
             $form["markup_oben"] = array(
              '#markup' => '<div class="well"><span class="glyphicon glyphicon-chevron-right"></span> Bitte laden Sie zuerst das <strong>Print-Medium</strong> hoch</div>',
              '#weight' => -100
             );
           
           $default_taxo = taxonomy_term_load(119);
           $form['title']["#default_value"] = $default_taxo->field_medientyp_standard_titel['und'][0]['value'];; 
           
           while(list($key, $val) = each($taxos["need"])){
             if($val -> is != 'print'){
                unset($taxos["select"][$key]);
             }
           }  
          }
          elseif($taxos["online"] == 0){
             $form["markup_oben"] = array(
              '#markup' => '<div class="well"><span class="glyphicon glyphicon-chevron-right"></span> Bitte laden Sie nun das <strong>Online-Medium</strong> hoch</div>',
              '#weight' => -100
             );
             
             $default_taxo = taxonomy_term_load(120);
             
              $form['title']["#default_value"] = $default_taxo->field_medientyp_standard_titel['und'][0]['value']; 
              while(list($key, $val) = each($taxos["need"])){
                 if($val -> is != 'online'){
                    unset($taxos["select"][$key]);
                 }
              }  
          }
          else {
              
            //if($taxos["serie"]){
              $form['field_medium_bild']['#required'] = false;
              $form['field_medium_beschreibung']['#required'] = false;
              $form['field_medium_bild']['und'][0]['#required'] = false;
              $form['field_medium_beschreibung']['und'][0]['#required'] = false;
              $form['field_medium_beschreibung']['und']['#required'] = false;
              $form['field_medium_beschreibung']['und'][0]['value']['#required'] = false;
              $form['#fieldgroups']['group_medium_presetation']->format_type = 'hidden';
              $form['field_medium_beschreibung']['#access'] = false;
              $form['field_medium_bild']['#access'] = false;
              
              
               if(isset($_GET["medium"])){
                  if(isset($taxos["select"][$_GET["medium"]])){
                      $test = _lk_get_medientyp_print_or_online($_GET["medium"]);
                      if($test == 'print')  $default_taxo = taxonomy_term_load(119);
                      else $default_taxo = taxonomy_term_load(120);
                      
                      $form['title']["#default_value"] = $default_taxo->field_medientyp_standard_titel['und'][0]['value'];      
                    
                      while(list($key, $val) = each($taxos["select"])){
                        if($_GET["medium"] != $key){
                          unset($taxos["select"][$key]);
                        }
                      
                      }
                      
                      reset($taxos["select"]);
                  }
               } 
          }
          
          $variante = false;
          $medium = false;
          //http://lk.dev/node/87/addmedia?parent=90&variante=1&medium=6413
          
          $variante = false;
          
          if(isset($_REQUEST["variante"])){
             $variante =  $_REQUEST["variante"];
          }
          elseif(isset($form_state["input"]["variante"])){
             $variante =  $form_state["input"]["variante"];
          }
          
          if(isset($_REQUEST['parent'])){
              $parent = $_REQUEST['parent'];
          }
          elseif(isset($form_state["input"]["parent"])){
             $parent =  $form_state["input"]["parent"];
          }
     
          if(isset($_REQUEST['medium'])){
              $medium = $_REQUEST['medium'];
          }
          elseif(isset($form_state["input"]["medium"])){
             $medium =  $form_state["input"]["medium"];
          }
          
          
          if($variante){
              $medium_load = new LKMedium($parent);
              // Check if Medium can add an extra
              
              // Wenn das Basis-Medium nicht vorhanden ist
              if(!$medium_load -> is){
                 drupal_set_message('Unzulässige Editierung');
                 drupal_goto('node/' . arg(1) . '/media');
              }
              
              $info = LKMedium_get_possibile_varianten($medium_load);
              
              // Wenn das Medium nicht existiert.
              if(!isset($info[$medium]) OR $info[$medium]["medium"] != 0){
                 drupal_set_message('Unzulässige Editierung');
                 drupal_goto('node/' . $node -> nid . '/media');
              }
              
              $form["parent"] = array('#type' => 'hidden', '#value' => $medium_load -> id);
              $form["medium"] = array('#type' => 'hidden', '#value' => $medium);
              $form["variante"] = array('#type' => 'hidden', '#value' => 1);
              
              $term = taxonomy_term_load($medium);
              drupal_set_title("Formatvariante / " . $term -> name);
              $form['title']['#default_value'] = $medium_load ->getTitle();
              $form['field_medium_typ']['#access'] = false;
              $varianten = $taxos['additional']['print'];
          }
          elseif(count($taxos["select"]) == 0 AND !$taxos["individuell"] AND !$variante){
              drupal_set_message('Sie haben bereits alle notwendigen Anzeigen hochgeladen');
              drupal_goto('node/' . $node -> nid . '/media');
          }
          
          if($taxos["individuell"]){
              $form['field_medium_typ']['und']['#options'] = _lk_get_flat_medium_typ_options();
          }
          else { 
               //array_push($form['#validate'], '_lokalkoenig_addkampagne_addmedium_requiered_format');
              $form['field_medium_typ']['und']['#options'] = array('' => '- Format auswählen -') + $taxos["select"]; 
          }
         }
        
        _formlk($form);
        array_push($form['#submit'], '_lokalkoenig_addkampagne_addmedium');
        break;
   }
}



?>