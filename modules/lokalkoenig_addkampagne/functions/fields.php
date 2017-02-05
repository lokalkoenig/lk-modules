<?php

function computed_field_field_kamp_medien_compute(&$entity_field, $entity_type, $entity, $field, $instance, $langcode, $items){
  $dbq = db_query("SELECT count(*) as count FROM field_data_field_medium_node WHERE bundle='medium' AND field_medium_node_nid='". $entity -> nid ."'");
  $result = $dbq->fetchObject();  
  
  $entity_field[0]["value"] = $result -> count;
}

function computed_field_field_kamp_beliebtheit_compute(&$entity_field, $entity_type, $entity, $field, $instance, $langcode, $items){
  $entity_field[0]["value"] = 0;
}

function computed_field_field_medium_order_compute(&$entity_field, 
    $entity_type, 
    $entity, $field, $instance, $langcode, $items){
    // Nur wenn neu
    if($entity -> is_new){
      $nid = $entity -> field_medium_node["und"][0]["nid"];
      $dbq = db_query("SELECT o.field_medium_order_value FROM field_data_field_medium_node n, field_data_field_medium_order o 
        WHERE  n.entity_id=o.entity_id AND
                n.bundle='presentation' 
              AND n.field_medium_node_nid='". $nid ."' 
        ORDER BY o.field_medium_order_value DESC LIMIT 1");
      $record = $dbq->fetchObject();   
      
      if(!$record) $entity_field[0]["value"] = 1;
      else  $entity_field[0]["value"] = $record -> field_medium_order_value + 1;
    }
}

function _lokalkoenig_change_media_count($nid){
  $node = node_load($nid);
  node_save($node);
}

function computed_field_field_kamp_status_display($field, $entity_field_item, $entity_lang, $langcode, $entity){
  
  $return = '??';
  switch($entity_field_item['value']){
    // Wenn New, dann 
    case 'new':
      $return = '<span class="label label-primary">Neu</span>';
      break;
  
    case 'proof':
      $return =  '<span class="label label-warning">Eingereicht</span>';
      break;
      
    case 'canceled':
      $return =  '<span class="label label-danger">Abgelehnt</span>';
      break;  
      
   case 'deleted':
      $return =  '<span class="label label-danger">Gelöscht</span>';
      break;     
      
    case 'published':
      $return =  '<span class="label label-success">Online</span>';
      break;  
      
    case 'new_lacks':
      $return = '<span class="label label-warning">Mit Mängeln</span>';
      break;   
  }
  
  return $return; 
}


function computed_field_field_format_kamp_print_compute(&$entity_field, $entity_type, $entity, $field, $instance, $langcode, $items){
  
  $entity_field[0]["value"] = '';


  if(isset($entity -> nid)){
    $node = node_load($entity -> nid);
    
    $values = array();
    foreach($node->medien as $medien){
      $res = _lk_get_medientyp_print_or_online($medien->field_medium_typ['und'][0]['tid']);   
      if($res == 'print'){
        $tax = taxonomy_term_load($medien->field_medium_typ['und'][0]['tid']);
        
        if($tax->description){
             $values[$tax -> tid] = $tax -> description;
        }
        else {
            $values[$tax -> tid] = $tax -> name;
        }
      }
    }
    
    $entity_field[0]["value"] = implode(",", $values);
    
  }
}

function computed_field_field_format_kamp_online_compute(&$entity_field, $entity_type, $entity, $field, $instance, $langcode, $items){
   $entity_field[0]["value"] = '';


  if(isset($entity -> nid)){
    $node = node_load($entity -> nid);
    $values = array();
    foreach($node->medien as $medien){
      $res = _lk_get_medientyp_print_or_online($medien->field_medium_typ['und'][0]['tid']);   
      if($res != 'print'){
        $tax = taxonomy_term_load($medien->field_medium_typ['und'][0]['tid']);
        $entity_field[0]["value"] = $tax -> name;
        
        if($tax->description){
             $values[$tax -> tid] = $tax -> description;
        }
        else {
            $values[$tax -> tid] = $tax -> name;
        }  
      }
    }
    
    $entity_field[0]["value"] = implode(",", $values);
  }
}


function computed_field_field_sid_compute(&$entity_field, $entity_type, $entity, $field, $instance, $langcode, $items){
  
  if(isset($entity -> nid) AND isset($entity->field_kamp_preisnivau['und'][0]['tid'])){
    $term = taxonomy_term_load($entity->field_kamp_preisnivau['und'][0]['tid']);
    $entity_field[0]["value"] =  $term->field_paket_kurz['und'][0]['value'] . '-' . $entity -> nid;
  }
  else {
    //$entity_field[0]["value"] = '';
  }  
}


/**
 * @todo Needs to be rewritten soon
 * 
 * @global int $totalpages
 * @param type $entity_field
 * @param type $entity_type
 * @param type $entity
 * @param type $field
 * @param type $instance
 * @param type $langcode
 * @param type $items
 * @return type
 */
function computed_field_field_kamp_pdf_pages_compute(&$entity_field, $entity_type, $entity, $field, $instance, $langcode, $items){
global $totalpages;
    
  $nid = $entity -> nid;
 
  if($entity -> status == 1) {
    $totalpages = 0;
    $node = node_load($nid);
    
    // Bei Batch, was anderes machen
    if(isset($node ->field_kamp_pdf_pages['und'][0]['value']) AND arg(0) == 'batch') {
       if($node ->field_kamp_pdf_pages['und'][0]['value'] > 1){
          return ;
       } 
    }
    
    $pdf = \LK\PDF\PDF_Loader::renderTestNode($node, false);
    $totalpages = $pdf -> PageNo();

    if($totalpages){
      $entity_field[0]["value"] = $totalpages;
    }
  }
}


function computed_field_field_medium_file_type_compute(&$entity_field, $entity_type, $entity, $field, $instance, $langcode, $items){
 $value = $entity->field_medium_source['und'][0]['fid'];
 $file = file_load($value);
 
 $file_name = $file -> filename;
 $ext = pathinfo($file_name, PATHINFO_EXTENSION);
 
 // ai - 137
 // indd - 139
 
 switch($ext){
    case 'psd':
      $entity_field[0]["value"] = 138;
      break;
    
    case 'ai':
      $entity_field[0]["value"] = 137;
      break;
    
    case 'indd':
      $entity_field[0]["value"] = 139;
      break; 
 }
}

function computed_field_field_medium_file_type_display($field, $entity_field_item, $entity_lang, $langcode, $entity){

  switch($entity_field_item['value']){
    case '138':
     return '<img class="file-icon" alt="" title="Photoshop" src="/sites/all/themes/bootstrap_lk/file-types/type-psd.png" />';
    break;
    
     case '137':
     return '<img class="file-icon" alt="" title="Illustrator" src="/sites/all/themes/bootstrap_lk/file-types/type-ai.png" />';
    break;
    
    //  
    case '139':
     return '<img class="file-icon" alt="" title="Indesign" src="/sites/all/themes/bootstrap_lk/file-types/type-indd.png" />';
      break;
    
    default:
      return '??';
    break;
    
  }


}


/** Views Informationen */
function lokalkoenig_addkampagne_views_data_alter(&$data){
   $data["eck_plz"]['edit_link'] = array(
      'field' => array(
        'title' => 'Editieren',
        'help' => 'bla',
        'handler' => 'lk_eck_views_handler_field_link_edit',
      ),
    );
    
   $data["eck_plz"]['delete_link'] = array(
      'field' => array(
        'title' => 'Löschen',
        'help' => 'bla',
        'handler' => 'lk_eck_views_handler_field_link_delete',
      ),
    ); 
    
    
    $data["eck_medium"]['edit_link'] = array(
      'field' => array(
        'title' => 'Editieren',
        'help' => 'bla',
        'handler' => 'lk_eck_views_handler_field_link_edit',
      ),
    );
    
   $data["eck_medium"]['delete_link'] = array(
      'field' => array(
        'title' => 'Löschen',
        'help' => 'bla',
        'handler' => 'lk_eck_views_handler_field_link_delete',
      ),
    ); 
    
}


/**
 * Field handler to present a link to editt the entity content.
 *
 */
class lk_eck_views_handler_field_link_delete extends eck_views_handler_field_link {
  
  function render_link($entity, $values) {
    $entity_type = $entity->entityType();
    $bundle = $entity->type;
    
    $nid = $values ->_field_data['id']['entity']->field_medium_node['und'][0]['nid'];
    
    $action = "delete";
    if (eck__multiple_access_check(
      array(
      'eck administer entities',
      "eck {$action} entities",
      "eck administer {$entity_type} {$bundle} entities",
      "eck {$action} {$entity_type} {$bundle} entities"))){
      
      $crud_info = get_bundle_crud_info($entity_type, $bundle);
      
      
      if($entity_type == 'medium') $edit_path = 'media';
      else $edit_path = $entity_type;
                
      
      $this->options['alter']['make_link'] = TRUE;
      $this->options['alter']['path'] = "node/" . $nid . "/". $edit_path ."/" . $values -> id . "/delete";
      $this->options['alter']["link_class"] = 'btn btn-danger btn-sm';
      //$this->options['alter']['query'] = drupal_get_destination();
      $text = !empty($this->options['text']) ? $this->options['text'] : t('delete');

      return $text;
    }
  }
}

/**
 * Field handler to present a link to editt the entity content.
 *
 */
class lk_eck_views_handler_field_link_edit extends eck_views_handler_field_link {
  
  function render_link($entity, $values) {
    $entity_type = $entity->entityType();
    $bundle = $entity->type;
    
    $nid = $values ->_field_data['id']['entity']->field_medium_node['und'][0]['nid'];
    
     if($entity_type == 'medium') $edit_path = 'media';
     else $edit_path = $entity_type;
    
    
    $action = "edit";
    if (eck__multiple_access_check(
      array(
      'eck administer entities',
      "eck {$action} entities",
      "eck administer {$entity_type} {$bundle} entities",
      "eck {$action} {$entity_type} {$bundle} entities"))){
      
      $crud_info = get_bundle_crud_info($entity_type, $bundle);
      $this->options['alter']['make_link'] = TRUE;
      $this->options['alter']['path'] = "node/" . $nid . "/". $edit_path ."/" . $values -> id . "/edit";
      $this->options['alter']["link_class"] = 'btn btn-primary btn-sm testajax';
      //$this->options['alter']['query'] = drupal_get_destination();
      $text = !empty($this->options['text']) ? $this->options['text'] : t('edit');

      return $text;
    }
  }
}


?>