<?php

/** Gets number of active VKUS */
function vku_get_active_count($account){
global $user;

  if(!$account){
    $account = $user;
  }

  $dbq = db_query("SELECT count(*) as count FROM lk_vku WHERE uid='". $account -> uid ."' AND vku_status='active'");
  $count = $dbq -> fetchObject();
 

return $count -> count;
}


function vku_create_item_desc($vku, $item){

	$vku_id = $item["vku_id"];
	$id = $item["id"];

	$item['pages'] = 1;
	$item['icon'] = 'tasks';
	
        
        
	$item['edit'] = false;
        $item['candeactivate'] = false;
	
        if(!$item['data_active']){
            $item['candeactivate'] = true;
	}
      
        $item['candeactivate_url'] = 'vku/' . $vku_id . "/" . $id . '/status';
	$item['preview'] = "vku/" . $vku_id . "/preview/" . $id;
        $item['candelete'] = false;
        $item['candelete_url'] = 'vku/' . $vku_id .'/delete/' . $item['id'];
        $item["candelete_desc"] = 'Möchten Sie das Dokument aus der Verkaufsunterlage löschen?';

	// need to be overwritten
	$item['title'] = $item['desc'] = $item['short'] =  ucfirst($item['data_module']);

	// Edit
	$item['edit_title'] = 'Editieren';
	$item['edit_glyph'] = 'edit';
	$item['edit_class'] = 'primary';

	// make them Dynamic
	$func = 'vku_create_item_desc_' . $item['data_module'];
	
	if(function_exists($func)){
		$func($item, $vku);	
	}
			
return $item;
}


function vku_create_item_desc_node(&$item, $vku){

$nid = $item["data_entity_id"];
$node = node_load($nid);
$id = $vku -> getId();
$item['title'] = $item['desc'] = $item['short'] = 'Kampagne: ' . $node -> title;
$item['short'] = 'Kampagne';
$item["desc"] = '<span class="label label-primary">'. $node->field_sid['und'][0]['value'] .'</span> ' .$node->field_kamp_untertitel['und'][0]['value'];
$item['pages'] = (int)$node -> field_kamp_pdf_pages['und'][0]['value'];




$item["desc"] .= ' -  ' . l("Kampagne ansehen", "node/" . $nid, array("attributes" => array("target" => "_blank")));

if($item["data_serialized"]){
    
   $data = unserialize($item["data_serialized"]);
   if(isset($data["pages"])){
       $item["pages"] = $data["pages"];
   }
    
   $item["desc"] .= '<br /><em class="pull-right">Individuelle Einstellungen</em>'; 
}

 $bild = $node->field_kamp_teaserbild['und'][0]['uri'];
 $img = image_style_url('kampagne_klein', $bild);

 $item["title"] .= '<img src="'. $img .'" class="pull-left" style="margin-right: 20px;" />';
 $item['candeactivate'] = false;
 $item['candelete'] = true;
 $item['candelete_url'] = 'vku/' . $id .'/delete/' . $item['id'];
 $item["candelete_desc"] = 'Möchten Sie die Kampagne aus der Verkaufsunterlage löschen?';
 $item['icon'] = 'lock';
 
 if(lk_is_moderator()){
    //vku/' . $id .'/edit/' . $item['id'];
    $item['edit_title'] = 'Kampagnenausgabe editieren';
    require_once __DIR__ . "/inc/func_vku_inner_edit.php";    
    $form = drupal_get_form("vku_form_vku_edit_kampagne", $id, $item['id'], false);
    $item['edit_form'] = render($form);
 }
 
  $author = $vku -> getAuthor();
  $access = na_check_user_has_access($author, $node -> nid);

 
  if(!$node -> status OR $access["access"] == false){
       if(isset($access["reason"])){
          $item["desc"] .= '<p class="alert alert-danger">'. $access["reason"] .'</p>';
       }
       else {
          $item["desc"] .= '<p class="alert alert-danger">Diese Kampagne ist für Sie nicht verfügbar</p>'; 
       }

  }
}

/**
 * Default VKU Items
 * 
 * @changed 2015-11-05, Added Delete Support
 * 
 * @param type $item
 * @param type $vku
 */
function vku_create_item_desc_default(&$item, $vku){
        
    
        $item["candelete"] = true;    
        $item["title"] = $item["desc"] = $item["short"] = vku_get_default_titles($item["data_class"]);
        
	$id = $vku -> getId();

	switch($item["data_class"]){
			case 'title':
				$item['edit'] = 'vku/' . $id .'/edit/' . $item['id'];
				$item['edit_title'] = 'Titelseite editieren';
		
                            $arr = array();

                            if($title = $vku -> get("vku_title")){
                                     $arr[] = "Titel: " . $title;
                            }

                            if($title = $vku -> get("vku_company")){
                                  $arr[] = "Unternehmen: " . $title;
                            }
            
        
                                $item['desc'] = implode(", ", $arr);
				break;	

			case 'tageszeitung':
				$item['short'] = 'Tageszeitungen';
				break;		

			case 'wochen':
				$item['short'] = 'Wochen-/Anzeigeblätter';
				break;	
			
			case 'onlinewerbung':
				$item['short'] = 'Online';
				break;		

			case 'kplanung':
				$item['short'] = 'Planung';
				break;					

			case 'kontakt':
				$author = $vku -> getAuthor();
				$item['edit'] = 'user/' . $author .'/edit/main';
				$item['edit_title'] = 'Profildaten editieren';
				$item['icon'] = 'envelope';
				$item['short'] = 'Kontakt';
				break;

	}
}

/**
 * 
 * Gets the standard VKU Elements
 * 
 * @since 2015-11-05
 * @return Array
 */
function vku_get_standard_documents(){
   $keys = array('title', 'tageszeitung', 'wochen', 'onlinewerbung', 'kplanung', 'kontakt');
   
   $return = array();
   $x = 0;
   foreach($keys as $item){
      $return[$item]['id'] = $item;
      $return[$item]['weight'] = $x;
      $return[$item]['title'] = vku_get_default_titles($item);
      $x += 10; 
   }
   
   return $return;
}


/**
 * Returns the Titles of the Standard Elements
 * @since 2015-11-05
 * 
 * @param type $key
 * @return string
 * 
 */
function vku_get_default_titles($key){
   
    switch($key){
        case 'kontakt':
          return 'Ihre Kontaktdaten';  
      
        case 'kplanung':
            return 'Kampagnenplanung';
     
        case 'onlinewerbung':
            return 'Online-Werbung (Display-Ads)';
     
               
        case 'wochen':
            return 'Medienargumentation Wochen-/Anzeigeblätter';
            
         case 'tageszeitung':
            return 'Medienargumentation Tageszeitungen';
            
            
        case 'title':
            return 'Titelseite';
          
        default:
            return 'Unbekanntes Dokument';
    }
}

