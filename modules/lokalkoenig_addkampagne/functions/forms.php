<?php

function __function_node_edit_lk(&$form, &$form_state){

        $form["#after_build"][] = '___node_form_css';
        //$form['actions']['submit']['#submit'][] = '_lokalkoenig_taxonomy_save';   
       //dpm($form);
       
       $form['field_kamp_teasertext']['und'][0]['value']['#resizable'] = false;
       $form['field_kamp_untertitel']['und'][0]['value']['#maxlength']  = 60;
       
       
       
       if(arg(2) == "edit"){
         $node = node_load(arg(1));
         
         _lk_kampa_online_warning($node, $form);
         
         $tax = taxonomy_term_load($node->field_kamp_preisnivau['und'][0]['tid']);
         
         $form["field_kamp_preisnivau"]["#access"] = false; 
         $form["field_kamp_format"]["#access"] = false;
          
         unset($form["#groups"]["group_kamp_preis_tab"]);
          pathtitle('node/kampagne/edit', '<span class="label label-success">'. $tax -> name .'</span>');
           $form_state['redirect'] = 'node/' . $form["#node"] -> nid . "/media";  
           $form['actions']['submit']['#submit'][] = '_lokalkoenig_edit_kampagnen_form_react';   
          
          //dpm($form_state);
          
           _form_edit_tax_branchen($form);
           $form['actions']['delete']["#access"] = false;
          //array_push($form['#submit'], '_lokalkoenig_edit_kampagnen_form_react');    
       }
       else {
          pathtitle('node/add/kampagne');
          
           _form_edit_tax_branchen($form);
          
          //dpm($form);
          while(list($key, $val) = each($form['field_kamp_preisnivau']['und']['#options'])){
            $tax = taxonomy_term_load($key);  
            $form['field_kamp_preisnivau']['und']['#options'][$key] = '<strong>' . $val . '</strong><br />
            <small>'. $tax -> description .'</small>
            '; 
          }
          reset($form['field_kamp_preisnivau']['und']['#options']);
         
         $form["field_kamp_themenbereiche"]["test"] = array(
            '#markup' => '<div id="select2" style="width: 100%;"></div>',
            //'#title' => 'Branche suchen',
            '#weight' => -10,
            //'#autocomplete_path' => 'lkautoeditbranche',
         );

          
          $form["field_kamp_format"]["#access"] = false;
          $form['actions']['submit']['#submit'][] = '_lokalkoenig_add_kampagnen_form_react';
          $form['actions']['submit']["#value"] = 'Speichern und weiter zum Medienupload';
          
            
          //array_push($form['#submit'], '_lokalkoenig_add_kampagnen_form_react');    
       }

}


function lokalkoenig_addkampagne_node_delete($node) {
  if($node -> type == 'kampagne'){
    //drupal_set_message("Die Kampagne wurde physisch gelöscht.");
    lk_log_kampagne($node -> nid, 'Kampagne gelöscht');  
  }
}

/** Löscht einen kompletten Node */
function _lokalkoenig_deletekampagne_node_delete_confirm(&$form, &$form_state){
global $user;
 
 
 if(lk_is_moderator() AND $form["#node"] -> lkstatus == 'deleted'){
       // Do nothing - we let the DELETE Function do smth.
      $form_state['redirect'] = 'kampagnen';    
 }
 else {
   _lk_set_kampagnen_status($form["#node"] -> nid, 'deleted');   
    drupal_get_messages();
    drupal_set_message("Die Kampagne wurde gelöscht.");
    lk_log_kampagne(arg(1), 'Kampagne verworfen');
    drupal_goto('user/' . $form["#node"] -> uid . "/kampagnen");
    // prevent normal DELETE
  }
}


function form_process_add_plz_select($form){
  
  $issets = false;
  
  
  // Mitarbeiter
   if(isset($form['profile_mitarbeiter']['field_plz_sperre']['und']['#title'])){
     $issets = true;
    
    
    $verlags_uid = arg(1);
    
    if(lk_is_verlag(user_load($verlags_uid))){
        $account = user_load($verlags_uid);
        $account -> profile = profile2_load_by_user($account);
        $form['profile_mitarbeiter']['field_plz_sperre']['#attributes']['fetch_url'] = url("lk/plz/". $verlags_uid);
    }
    
     
    
    $form['profile_mitarbeiter']['field_plz_sperre']['und']['#autocomplete_path'] = '';
    $form['profile_mitarbeiter']['field_plz_sperre']['#attributes']['class'][] = 'lkplz';
    $form['profile_mitarbeiter']['field_plz_sperre']['#attributes']['plz_id'] = 'plz_mitarbeiter';
    $form['profile_mitarbeiter']['field_plz_sperre']['und']['#field_suffix'] = '<button onclick="plzselect(this, \'plz_mitarbeiter\'); return false;" class="pull-right btn btn-success"><span style="color: White;" class="glyphicon glyphicon-pushpin"></span> PLZ auswählen</button>';    
   
    $form['profile_mitarbeiter']['field_plz_sperre']['und']['#maxlength'] = 10024;
    
   }
  
  
  // Verlag 
  
  if(isset($form['profile_verlag']['field_plz_sperre']['und']['#title'])){
    $issets = true;
    
    
    $form['profile_verlag']['field_plz_sperre']['und']['#autocomplete_path'] = '';
    $form['profile_verlag']['field_plz_sperre']['#attributes']['class'][] = 'lkplz';
    $form['profile_verlag']['field_plz_sperre']['#attributes']['plz_id'] = 'plz_verlag';
    $form['profile_verlag']['field_plz_sperre']['und']['#field_suffix'] = '<button  onclick="plzselect(this, \'plz_verlag\'); return false;" class="pull-right btn btn-success btn-sm"><span style="color: White;" class="glyphicon glyphicon-pushpin"></span> PLZ auswählen</button>';    
    $form['profile_verlag']['field_plz_sperre']['und']['#maxlength'] = 10024;
  }
  
  //dpm($form);
  
  // Ausgabe
  if(isset($form['profile_verlag']['field_plz_sperre']['und']['#title'])){
    $issets = true;
    
    
    $form['profile_verlag']['field_plz_sperre']['und']['#autocomplete_path'] = '';
    $form['profile_verlag']['field_plz_sperre']['#attributes']['class'][] = 'lkplz';
    $form['profile_verlag']['field_plz_sperre']['#attributes']['plz_id'] = 'plz_verlag';
    $form['profile_verlag']['field_plz_sperre']['und']['#field_suffix'] = '<button  onclick="plzselect(this, \'plz_verlag\'); return false;" class="pull-right btn btn-success btn-sm"><span style="color: White;" class="glyphicon glyphicon-pushpin"></span> PLZ auswählen</button>';    
    $form['profile_verlag']['field_plz_sperre']['und']['#maxlength'] = 10024;
  }

  // Kampagne
  
  if(isset($form['field_plz_sperre'])){
    $issets = true;
 
    if(arg(2) == "ausgaben"){
      $form['field_plz_sperre']['#attributes']['fetch_url'] = url("lk/plz/". arg(1));
      $form['field_plz_sperre']['und']['#autocomplete_path'] = '';
    }
 
 
    $form['field_plz_sperre']['#attributes']['class'][] = 'lkplz';
    $form['field_plz_sperre']['#attributes']['plz_id'] = 'plz_kampagne';
    $form['field_plz_sperre']['und']['#field_suffix'] = '<button  onclick="plzselect(this, \'plz_kampagne\'); return false;" class="pull-right btn btn-success btn-sm"><span style="color: White;" class="glyphicon glyphicon-pushpin"></span> PLZ auswählen</button>';    
    $form['field_plz_sperre']['und']['#maxlength'] = 10024;
  }
  
 
  
  
  if($issets){
     // Add JS and CSS
     $path = drupal_get_path('module', 'lokalkoenig_addkampagne');
     drupal_add_js($path . '/js/plzselect.js');
     drupal_add_css($path . '/css/plzselect.css');
  }
  
return $form;
}




/** Form Alter für das Kampagnen erstellen */
function lokalkoenig_addkampagne_form_alter(&$form, &$form_state, $form_id) {
global $user;
 
  //$form['#process'][] = 'form_process_radios_blubb'; 
  $form['#process'][] = 'form_process_add_plz_select'; 
  //dpm($form);  
  
    
     
  if(arg(0) == "node"){
     $node = node_load(arg(1));
     $form["#lk_node_nid"] = arg(1);
     
  }
  
  _lokalkoenig_addkampagne_media_formadmin($form, $form_state, $form_id);
  
  switch($form_id){
   
    // PLZ Editieren  
    case 'eck__entity__form_edit_plz_plz':
       $form['field_medium_node']['#access']  = false;
       $form['#validate'][]  = 'lokalkoenig_addkampagne_plz_validate';
       
        pathtitle('node/x/plz/x/edit'); 
     
       //$form['field_plz_sperre_verlag']['und']['#options'] = provide_verlag_mitarbeiter_select();
       _formlk($form);
       array_push($form['#submit'], '_lokalkoenig_editplz_submit');
       break;  
  
  
  
    /** Entity löschen */
    case 'eck__entity__delete_form':
    
      $entity_type = $form['entity']['#value']->type;
      //dpm($form);
      
      if($entity_type == 'plz'){
         pathtitle('node/x/plz/x/delete');
        _formlkdelete($form, "node/" . arg(1) . "/plz");
      
       //drupal_set_title("Sind Sie sicher, dass Sie die PLZ-Regel löschen möchten?");
        $form['#submit'][0] = '_lokalkoenig_deletekampagne_plz_delete'; 
      }
      elseif($entity_type == "medium") {
        pathtitle('node/x/media/x/delete');
        _formlkdelete($form, "node/" . arg(1) . "/media");
        array_push($form['#submit'], '_lokalkoenig_deletekampagne_delete_medium');
      }
      break;
     
     
      
     
    
     
      case 'node_delete_confirm': 
        if($form["#node"] -> type == "kampagne"){
            pathtitle('node/x/delete');
            
            if(lk_is_moderator() AND $form["#node"] -> lkstatus == 'deleted'){
               
               if(!isset($_GET["destination"])){
                  drupal_goto('node/' . $form["#node"] -> nid . '/delete', array('query' => array('destination' => 'kampagnen'))); 
               }
                
                
               // Prevent Physical Delete  
               drupal_goto('node/' . $form["#node"] -> nid . '/status');
               drupal_exit();
               
               $form['description']['#markup'] = '<strong>Hinweis:</strong> die Kampagne wird nach dem Bestätigen physisch auf dem Server gelöscht. Alle Informationen gehen verloren. Dieser Vorgang ist nur für nicht eingereichte Kampagnen zu verwenden.';
               array_push($form['#validate'], '_lokalkoenig_deletekampagne_node_delete_confirm');    
                $form_state['redirect'] = 'kampagnen';    
            }
            else
            
              array_push($form['#validate'], '_lokalkoenig_deletekampagne_node_delete_confirm');    
              //array_push($form['#submit'], '_lokalkoenig_deletekampagne_node_delete_confirm');   
            
             _formlkdelete($form, "user/" . $user -> uid . "/kampagnen");
            
        }
        break;
      
      
      // Kampagne hinzufügen / Bearbeiten
      case 'kampagne_node_form':  
       // Editieren
       
       //dpm($form);
       
       //require_once('node_edit.php');
       __function_node_edit_lk($form, $form_state);
       _formlk($form); 
      break;  
  }
}


function _validate_lk_tags($element, &$form_state){
   // Autocomplete widgets do not send their tids in the form, so we must detect
  // them here and process them independently.
  $value = array();
  
  if ($tags = $element['#value']) {
   
  
  
  } 
  
  $lkterms = array();
  
  if ($tags = $element['#value']) {
    // Collect candidate vocabularies.
    $field = field_widget_field($element, $form_state);
    $vocabularies = array();
    foreach ($field['settings']['allowed_values'] as $tree) {
      if ($vocabulary = taxonomy_vocabulary_machine_name_load($tree['vocabulary'])) {
        $vocabularies[$vocabulary->vid] = $vocabulary;
      }
    }

    // Translate term names into actual terms.
    $typed_terms = drupal_explode_tags($tags);
    
    foreach($typed_terms as $test){
        preg_match('/^(?:\s*|(.*) )?\[\s*tid\s*:\s*(\d+)\s*\]$/', $test, $matches);
        if (!empty($matches)) {
          $lkterms[] = $matches[2]; 
        }
    }
    
    $typed_terms = $lkterms;
    
    foreach ($typed_terms as $typed_term) {
      // See if the term exists in the chosen vocabulary and return the tid;
      // otherwise, create a new 'autocreate' term for insert/update.
      if ($possibilities = taxonomy_term_load_multiple(array(), array('tid' => trim($typed_term), 'vid' => array_keys($vocabularies)))) {
        $term = array_pop($possibilities);
      }
      else {
        $vocabulary = reset($vocabularies);
        $term = array(
          'tid' => 'autocreate',
          'vid' => $vocabulary->vid,
          'name' => $typed_term,
          'vocabulary_machine_name' => $vocabulary->machine_name,
        );
      }
      $value[] = (array) $term;
    }
  }


  // Prepopulate Terms by Voc
  $tids = array();
  foreach($lkterms as $tid){
    $tids[$tid] = $tid;
    
    // 4te Ebene
    $res = taxonomy_get_parents($tid);
    if($tid = key($res)){
       // 3te Ebene
        $tids[$tid] = $tid; 
        
        $res = taxonomy_get_parents($tid);
        if($tid = key($res)){
           $tids[$tid] = $tid; 
           
           //2te Ebene      
            $res = taxonomy_get_parents($tid);
            if($tid = key($res)){
                $tids[$tid] = $tid; 
                
                //1te Ebene
                $res = taxonomy_get_parents($tid);
                if($tid = key($res)){
                    $tids[$tid] = $tid; 
                }
            }
        }
    }
  }
  
  
  $returns = array();
  
  while(list($key, $val) = each($tids)){
     $returns[] = (array)taxonomy_term_load($key);
  
  }

  form_set_value($element, $returns, $form_state);
}

function _form_edit_tax_branchen(&$form){
  
  $form['field_kamp_themenbereiche']['und']['#element_validate'][0] = '_validate_lk_tags';
  $form['field_kamp_themenbereiche']['und']['#autocomplete_deluxe_path'] = url('lkautoeditbranche');
  
  $config = array(
    'vid' => 3,
    'exclude_tid' => NULL,
    'root_term' => 0,
    'entity_count_for_node_type' => NULL
  );
  
  
  $values = array();
  
  if(isset($form['#node']->field_kamp_themenbereiche['und'])){
    $tax_bisher = $form['#node']->field_kamp_themenbereiche['und'];
  }
  else $tax_bisher = array();
  
  
  $selection = array();
  foreach($tax_bisher as $tax){
    $term = taxonomy_term_load($tax["tid"]);
    $selection[] = $tax["tid"];  
    // Hier noch die Line darstellen
    $values[] = $term -> name . ' [tid:'. $term -> tid  .']';
  }
  
  // Use Helper from the Other Module
  $taxes = _hierarchical_select_dropbox_reconstruct_lineages_save_lineage_enabled('hs_taxonomy', $selection, $config);
  
  //dpm($taxes);
  
  $returns = array();
  foreach($taxes as $val){
    $returns[] = _lk_generate_lineage_label($val); 
  
  }
 
  
 
  $form['field_kamp_themenbereiche']['und']['#default_value'] = implode(";", $returns);
  $form['field_kamp_anlass']['und']['#default_value'] = implode(";", explode(",", $form['field_kamp_anlass']['und']['#default_value']));
}


function _lk_generate_lineage_label($val){
  $return = array();
  
  $key = count($val) - 1;
  
  $return[] = $val[$key]["label"];
  
  $inbet = array();
  
  for($x = ($key -1); $x >= 0; $x--){
      $inbet[] = $val[$x]["label"];
  }
  
  $return[] = '('. implode(' > ', $inbet) .')';
  
  
  $return[] = '[tid:'. $val[$key]["value"]  .']';

return implode(' ', $return);
}


function _lokalkoenig_make_varianten_titles_requiered(&$form, $form_state){
  
  if(!isset($form_state["values"]["field_medium_varianten"]['und'])) return ;
  
  foreach($form_state["values"]["field_medium_varianten"]['und'] as $test){
    //dpm($test);  
    if(empty($test["title"]) AND $test["fid"]){
      form_set_error('field_medium_varianten', 'Bitte geben Sie einen Varianten-Titel ein.'); 
    }
  }
}


function _lokalkoenig_add_kampagnen_form_react(&$form, &$form_state){
   lk_log_kampagne( $form_state["nid"], 'Kampagne angelegt'); 
   $form_state['redirect'] = 'node/' . $form_state["nid"] . "/addmedia";    
}


function _lokalkoenig_edit_kampagnen_form_react(&$form, &$form_state){
   lk_log_kampagne($form["#node"] -> nid, 'Kampagne editiert'); 
   $form_state['redirect'] = 'node/' . $form["#node"] -> nid . "/media";    
}

function _lk_get_flat_medium_typ_options(){
  $voc = taxonomy_vocabulary_machine_name_load('medientyp');
  $tree = taxonomy_get_tree($voc -> vid, 0,NULL, true);

  $select = array();
  //dpm($tree);
  
  $rtree = array();
  foreach($tree as $t){
    $rtree[$t->tid] = $t;
  }
  while(list($key, $term) = each($rtree)){
      if($term -> parents[0] == 0) continue;
  
    $parent = $rtree[$term -> parents[0]];
    $select[$key] = $parent -> name . ": " . $term -> name; 
  }

  return $select;
}


function _lokalkeonig_get_missing_mediums($node){
  
  $taxo = taxonomy_term_load($node->field_kamp_preisnivau['und'][0]['tid']);
  $individuell = false;
  $voc = taxonomy_vocabulary_machine_name_load('medientyp');
  $tree = taxonomy_get_tree($voc -> vid, 0,NULL, true);
  
  $serie = false;
  
  if($taxo -> tid == 6423){
     $individuell = true;
     $serie = true;
  }
  
  //dpm($taxo);
  if(isset($taxo->field_paket_medienserie['und'][0]['value']) AND 
      $taxo->field_paket_medienserie['und'][0]['value']){
    $serie = true;
  }
  
  $rtree = array();
  foreach($tree as $t){
    $rtree[$t->tid] = $t;
  }
  
  $needtohave = array();
  $return = array();
  
  $hasprint = 0;
  $hasonline = 0;
  
  $additional = array(
      'print' => array(),
      'online' => array()
  );
  
   $additional = array(
      'print' => array(),
      'online' => array()
  );
  
  if(!$individuell){
    foreach ($taxo->field_paket_medientypen['und'] as $med){
      $tid = $med["tid"];
      
      $parent = $rtree[$tid]->parents[0];
      $parent_tax = $rtree[$parent];
      // Print
      
      if($parent == 119){
        $rtree[$tid] -> is = 'print';
        $additional["print"][$tid] =  $parent_tax -> name . ": " . $rtree[$tid] -> name;
      }
      elseif($parent == 120){
        $rtree[$tid] -> is = 'online';
        $additional["online"][$tid] =  $parent_tax -> name . ": " . $rtree[$tid] -> name;
      }
      
      $return[$tid] = $parent_tax -> name . ": " . $rtree[$tid] -> name;
      $needtohave[$tid] = $rtree[$tid];
    }
  }
  
  
  $mediumhas = array();
  
  // Wenn Medien hochgeladen
  if(isset($node -> medien)){
   //dpm($node -> medien);
   
    
    foreach($node -> medien as $entity){
      $mediumhas[] = $rtree[$entity->field_medium_typ['und'][0]['tid']];
    }
  }

  
    
  
  foreach($mediumhas as $taxcheck){
    if(!isset($taxcheck -> is)){
       $taxcheck -> is = 'bulk';
    }
  
    if($taxcheck -> is == 'print'){
      $hasprint++;
    }
    elseif($taxcheck -> is == 'online'){
      $hasonline++;
    }
  }
    
  // Wenn keine Serie   
  if(!$serie){
    while(list($tid, $term) = each($return)){
      
      if(!isset($needtohave[$tid] -> is)){
         $needtohave[$tid] -> is = 'bulk';
      }  
   
   
      if($hasprint AND $needtohave[$tid] -> is == 'print'){
        unset($return[$tid]);
      }
      
      if($hasonline AND $needtohave[$tid] -> is == 'online'){
        unset($return[$tid]);
      }
    }
  }
  
  $incomplete = false;
  if(!$serie AND count($return) > 0){
     $incomplete = true;
  }
  // Wenn Individuell
  elseif($individuell){
      $countall = count($mediumhas);
      if($countall < 2){
         $incomplete = true;
      }
  
  }
  elseif($serie){
    if($hasprint < 2 OR $hasonline < 2){
      $incomplete = true;
    }
  }  
  
  $array=  array('additional' => $additional, 'individuell' => $individuell, 'incomplete' => $incomplete, 'need' => $needtohave, 'select' => $return, 'print' => $hasprint, 'online' => $hasonline);
  $array["serie"] = $serie;
  
  return $array;
}


function lk_generate_dropdown($title, $glyph, $items, $class = "btn-success btn-sm"){
  
   $html = '<div class="btn-group">
  <button type="button" class="btn '. $class .' dropdown-toggle" data-toggle="dropdown">
    <span class="glyphicon glyphicon-'. $glyph .'"></span> '. $title .' <span class="caret"></span>
  </button>
  <ul class="dropdown-menu" role="menu">'; 
    
   foreach($items as $item):
       $html .= '<li>'. $item .'</li>';
   endforeach;
 
   $html .= '</ul></div>';   

return $html;   
}


function _lk_get_filetype($filename){
  $ext = pathinfo($filename, PATHINFO_EXTENSION);
  
  switch($ext){
    case 'psd':
    case 'indd':
    case 'ai': 
    case 'zip':    
      
      $icon_url = url("sites/all/themes/bootstrap_lk/file-types/type-". $ext .".png");  
      break;
  
    
  
  
    default:
      $icon_url = url("sites/all/themes/bootstrap_lk/file-types/type-jpg.png");  
      break;
  }
  
  
  return '<img class="file-icon" width="30" alt="" title="' . strtoupper($ext) . '" src="' . $icon_url . '" />';

}


function _lk_get_medientyp_with_type($tid){
 $tax = taxonomy_term_load($tid);
 $voc = taxonomy_vocabulary_machine_name_load('medientyp');
 $tree = taxonomy_get_tree($voc -> vid, 0,NULL, true);
 
 
 foreach($tree as $tax2){
    if($tid ==  $tax2 -> tid){
        if(!$tax2 -> parents[0]) return 'WRONG';
       $tax3 = taxonomy_term_load($tax2 -> parents[0]);
    
    }
 }
 
 if($tax -> description){
    return $tax3 -> name . ": " . $tax -> description;  
 }
 
 
return $tax3 -> name . ": " . $tax -> name;   
}

function _lk_get_medientyp_print_or_online($tid){
 $tax = taxonomy_term_load($tid);
 $voc = taxonomy_vocabulary_machine_name_load('medientyp');
 $tree = taxonomy_get_tree($voc -> vid, 0,NULL, true);
 
 foreach($tree as $tax2){
    if($tid ==  $tax2 -> tid){
        if(!$tax2 -> parents[0]) return 'WRONG';
       $tax3 = taxonomy_term_load($tax2 -> parents[0]);
    
    }
 }
 
 
 
 
return strtolower($tax3 -> name);   
}


function bootstrap_lk_views_view_field__intern_kampagnen_medien__title($vars){
  $view = $vars['view'];
  $field = $vars['field'];
  //dpm($vars["row"]);
  $row = $vars['row'];
  
  return $row -> eck_medium_title . ' <br /><small>' . _lk_get_medientyp_with_type($vars["row"]->field_field_medium_typ[0]['raw']['tid']) . '</small>'; 
  
  return $vars['output']; 
}

/** Assume the Varinate is outstanding */
function LKMedium_get_possibile_varianten(LKMedium $medium){
    
    $node = node_load($medium ->getNid());
    $id = $medium -> id;
    if(!$node){
        return array();
    }
    
    $type = $medium -> getType();
    
    $array = array();
    
    $info = _lokalkeonig_get_missing_mediums($node);
    $varianten = $info['additional'][$type];
    
    while(list($key, $val) = each($varianten)){
      $array[$key]["title"] = $val; 
      $array[$key]["medium"] = 0; 
      $array[$key]["type"] = 'none'; 
      
    }
    
    // Mark BaseMedium
    $tid = $medium ->getTermId();
    if(isset($array[$tid])){
        $array[$tid]["medium"] = $id;
        $array[$tid]["type"] = 'base';
    }
    
    // Varianten
    foreach($node -> medien as $med){
         $obj = new LKMedium($med -> id);
         $parent = $obj -> getParent();
         $variante = $obj -> isVariante();
       
         if($id == $parent AND $variante){
             $tid = $obj ->getTermId();
             
             if(isset($array[$tid])){
                $array[$tid]["medium"] = $obj -> id; 
                $array[$tid]["type"] = 'variante';
             }
         }
    }
   
    return $array;
}


class LKMedium {
    
    var $is = false;
    var $type = null;
    var $data = null;
    var $id = null;
    var $nid = null;
    
    function __construct($media_id){
        $media = entity_load_single("medium", $media_id);
        
        if($media){
            $this -> is = true;
            $this -> data = $media;
            $this -> id = $media -> id;
            $this -> type =_lk_get_medientyp_print_or_online($media->field_medium_typ['und'][0]['tid']);
            $this -> nid = $this -> data->field_medium_node['und'][0]['nid'];
        }
    }
    
    function getTermId(){
       return $this -> data->field_medium_typ['und'][0]['tid']; 
    }
    
    function getNid(){
        return $this -> nid;
    }
    
    function getType(){
        return $this -> type;
    }
    
    function getParent(){
      if(isset($this -> data -> field_medium_main_reference["und"][0]["target_id"])){
        return $this -> data -> field_medium_main_reference["und"][0]["target_id"];  
      }
      else {
          return false;
      }
    }
    
    function getTitle(){
        return $this -> data -> title;
        
    }
    
    function isVariante(){
        if($this -> data -> variante){
            return true;
        }
        else {
            return false;
        }
    }
    
    function isOnline(){
        if($this -> type == 'online'){
            return true;
        }
    
    return false;    
    }
    
    function isPrint(){
        if($this -> type == 'print'){
            return true;
        }
    
    return false;    
    }
}


?>