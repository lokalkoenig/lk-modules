<?php

function vku_create_item_desc_vkuaddon(&$item, $vku){

  $entity_id = $item["data_entity_id"];
  $entity = entity_load_single('vkuaddon', $entity_id);
  if(!$entity) return ;
  $vku_id = $vku -> getId();  
  $item["candelete"] = true;

  switch($entity -> type){
    case 'vkuaddon_region':
      $item["short"] = 'Regional';
      $item["title"] = 'Regionalargumentation';
      $item["desc"] = $entity -> title; 
      break;

    case 'vkuaddon_text':
      $item["short"] = 'Text';
      $item["title"] = 'Textblock';
      $item["desc"] = $entity -> title; 
      $item["canedit"] = true;
      $item['edit'] = 'vku/' . $vku_id .'/edit/' . $item['id'];
      $item['edit_title'] = 'Textblock editieren';
      break;  

    case 'vkuaddon_preise':
      $item["short"] = 'Preise';
      $item["title"] = 'Preiskalkulation';
      $item["desc"] = $entity -> title; 
      $item["canedit"] = true;
      $item['edit'] = 'vku/' . $vku_id .'/edit/' . $item['id'];
      $item['edit_title'] = 'Preiskalkulation editieren';
      break;    
  
   case 'vkuaddon_mediencol':
      $item["short"] = 'Medien';
      $item["title"] = 'Medienargumentationen (bis zur 3 verschiedene pro Seite)';
      $item["desc"] = $entity -> title; 
      $item["canedit"] = true;
      $item['edit'] = 'vku/' . $vku_id .'/edit/' . $item['id'];
      $item['edit_title'] = 'Medienargumentation editieren';
      break;    
  }
}


function vku_generate_pdf_vkuaddon($vku, $page, $pdf){
     $entity_id = $page["data_entity_id"];
     $entity = entity_load_single('vkuaddon', $entity_id);
     
     _show_pdf_vkuaddon_entity($entity, $pdf);
}

function _show_pdf_vkuaddon_entity($entity, $pdf){
   require_once('sites/all/modules/lokalkoenig/vkuaddon/pdf/show.inc');
     
   if(!$entity) {
       die("Kein Zugriff");
   } 
   
    if($entity -> type == 'vkuaddon_region'){
        require_once('sites/all/modules/lokalkoenig/vkuaddon/pdf/vkuaddon_region.php');
        $preview = new PDF_vkuaddon_region(); 
     }

     if($entity -> type == 'vkuaddon_text'){
        require_once('sites/all/modules/lokalkoenig/vkuaddon/pdf/vkuaddon_text.php');
        $preview = new PDF_vkuaddon_text(); 
     }

      if($entity -> type == 'vkuaddon_preise'){
        require_once('sites/all/modules/lokalkoenig/vkuaddon/pdf/vkuaddon_preise.php');
        $preview = new PDF_vkuaddon_preise(); 
     }
     
     if($entity -> type == 'vkuaddon_mediencol'){
        require_once('sites/all/modules/lokalkoenig/vkuaddon/pdf/vkuaddon_medien.php');
        $preview = new PDF_vkuaddon_medien(); 
     }


     $preview -> setEntity($entity);
     $preview -> generatePreview($pdf);
}

/** DEPREICATED */
function vku_form_vorlage($form, $form_status, $vku){
    
    $form["#vku"] = $vku -> getId();
    
    $form["title"] = array(
        '#type' => 'textfield',
        '#title' => "Titel Ihrer Vorlage",
        '#default_value' => $vku -> get('vku_template_title'),
        '#required' => true
    );
    
    
    $form['submit'] = array(
      '#type' => 'submit',
      '#attributes' => array('class' => array('btn btn-yellow-arrow')),
      '#value' => 'Speichern',
    );
    
    return $form;
}


/** DEPREICATED */
function vku_form_vorlage_submit($form, &$form_state){
    $vku_id = $form["#vku"];
    $vku = new VKUCreator($vku_id);
    
    $vku -> set("vku_template_title", $form_state["values"]["title"]);
    $vku -> update();
    drupal_set_message("Ihre Vorlage wurde angepasst.");
    drupal_goto($vku -> vku_url());
}


function vkuconnection_get_user_templates($uid = null){
global $user;    
    
    if(!$uid):
        $uid = $user -> uid;
    endif;

    $array = array();
    $dbq = db_query("SELECT vku_template_default, vku_title, vku_template_title, vku_id, vku_changed, uid FROM lk_vku WHERE uid='". $uid ."' AND vku_status='template' ORDER BY vku_changed DESC"); 
    foreach($dbq as $all){
      
       if(empty($all -> vku_title)){
           continue;
       } 
        
      $all -> renew_url = 'user/' . $all -> uid . "/vku/" . $all -> vku_id . "/renew";  
      $array[] = $all;  
    }
    
return $array;    
} 