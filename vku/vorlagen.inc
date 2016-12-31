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


function _vkuconn_generate($vku){

	if(is_int($vku)){
          $vku = new VKUCreator($vku);
	} 
        
        $author = $vku ->getAuthor();
        $account = \LK\get_user($author);
        $verlags_id = $account ->getVerlag();
        
	$items = array();
            
        if($vku ->getStatus() != 'template'){
            $items["kampagnen"] = array('link' => 'suche', "title" => "Kampagnen", 'desc' => 'Fügen Sie dieser Verkaufsunterlage bis zu drei Kampagnen hinzu.', 'icon' => 'search');
        }
         
        $items["new"] = array(
			"title" => "Dokumente hinzufügen",
	 		'desc' => 'Fügen Sie dieser Verkaufsunterlage weitere Dokumente hinzu.',
	 		'items' => array(),
	 		'icon' => 'th-list'
	 	);

	$arr = array();
        vkuconn_get_default_documents($vku, $verlags_id, $arr);
        
        $items["new"]["items"] = $arr;
         
         // Active only
         if($vku -> is('active')){
             $templates = vkuconnection_get_user_templates();
            if($templates){
               $items["template"] = array("title" => "Vorlagen", 'desc' => 'Ihre benutzerdefinierten Vorlagen verwenden', 'icon' => 'repeat');
               $template_arr = array();


               foreach($templates as $template){

                   $template_arr[] = array("title" => $template -> vku_template_title . '<br /><small>zuletzt geändert am '. format_date($template -> vku_changed, "short") .'</small>', 
                               'link' => "vku/" . $vku_id . "/template/" . $template -> vku_id,
                               'optin' => 'Vorlage übernehmen',
                               'optin_text' => 'Die Kampagnen werden zusammen mit den Dokumenten der Verkaufsunterlage migriert.', 
                               'link_title' => "Template anwenden",
                               'optin_label' => "Übernehmen"
                     );
               }

               $items["template"]["items"] = $template_arr;   
            } 
         }
         
 	 return theme("vkuconnection_add", array("vku" => $vku, 'items' => $items));
}

/**
 * Adds Default Documents to the VKU
 * @since 2015-11-05
 * 
 * @param type $vku
 * @param type $verlags_id
 * @param type $items
 */
function vkuconn_get_default_documents($vku, $verlags_id, &$items){
  
    $check = vku_get_standard_documents();
    $vku_id = $vku -> getId();
    
    foreach($check as $item){
       $has = $vku -> getPageId($item["id"]);
       
       if(!$has){
           $items[$item["id"]] = 
                array(
                    'title' => $item["title"],
                    'link' => 'vku/' . $vku_id . "/addextra/" . $item["id"],
                    'type' => 'default',	
                    //'preview' =>  'preview-template/' . $item["id"],
    		);	
       }
    } 
}
