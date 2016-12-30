<?php

/** Gets number of active VKUS */
function vku_get_active_count($account = null){
global $user;

  if(!$account){
    $account = $user;
  }

  $dbq = db_query("SELECT count(*) as count FROM lk_vku WHERE uid='". $account -> uid ."' AND vku_status='active'");
  $count = $dbq -> fetchObject();
 

return $count -> count;
}

function vku_get_plz_bereiche($vku_id){
    $bereiche = array();
    
    $dbq = db_query("SELECT DISTINCT plz_ausgabe_id FROM lk_vku_plz_sperre_ausgaben WHERE vku_id='". $vku_id ."'");
    foreach($dbq as $all){
       $ausgabe = \LK\get_ausgabe($all -> plz_ausgabe_id);
       $bereiche[] = $ausgabe ->getTitleFormatted();
    }
    
return $bereiche;    
}


function get_verlag_plz_sperre($nid, $full = false){
global $user;

    $account = \LK\get_user($user);
    $verlag = $account -> getVerlag();
    
    if(!$verlag) { 
        return false;
    }
    
    $dbq = db_query("SELECT * FROM lk_vku_plz_sperre WHERE nid='". $nid  ."' AND verlag_uid='". $verlag ."'");
    $all = $dbq -> fetchObject();
    
    if(!$all){
        return false;
    }
    else {
        $sperre = (array)$all;
        $sperre["is_user"] = false;
        // Load the Ausgaben to Show them
        
        
        if($user -> uid == $all -> uid){
            $sperre["is_user"] = true;
            
            if($full){
                
                $vku = new VKUCreator($sperre["vku_id"]);
                $sperre["url"] = $vku ->url();
      
                if(!$data = $vku -> hasPlzSperre()){
                    return false;
                }
                    
                
                $sperre["info"] = $data;
            }
        }
    }  
    
return $sperre;    
}


/** Get the Number of Kampangen in the Active VKU */
function vku_get_active_id_count(){
     $id = vku_get_active_id();
     
     if(!$id) return 0;

      $vku = new VKUCreator($id);
      if(!$vku -> is()){
          return 0;
      }

      $data = $vku -> getKampagnen();
      return count($data);
  }

  /** Get the VKU-ID of the Last active VKU */
 function vku_get_active_id(){
  global $user; 
  
    $dbq = db_query("SELECT vku_id FROM lk_vku WHERE uid='". $user -> uid ."' AND vku_status='active' ORDER BY vku_changed DESC LIMIT 1");
    $record = $dbq->fetchObject();
    
    if($record){
      return $record -> vku_id;
    }
    
    return false;
  }


/** Get the IDs of the last active VKUs */
function vku_get_active_ids($account){
global $user;

  if(!$account){
    $account = $user;
  }

  $vkus = array();
  $dbq = db_query("SELECT vku_id FROM lk_vku WHERE uid='". $account -> uid ."' AND vku_status='active' ORDER BY vku_changed DESC, vku_id DESC");
  foreach($dbq as $all){
    $vkus[] = $all -> vku_id;
  }

return $vkus; 
}

function createLizenz($nid, $vku){
   // KAUFEN

   $vku_id = $vku -> getId();
   $vku_author = $vku -> getAuthor();
   $obj = \LK\get_user($vku_author);
   $vid = $obj ->getVerlag();
   $team_id = $obj -> getTeam();
   
   $node = node_load($nid);
   $array = array();
   $array["vku_id"] = $vku_id;
   $array["nid"] = $nid;
   $array["lizenz_date"] = time();
   $array["lizenz_uid"] = $vku_author;
   $array["node_uid"] = $node -> uid;
          
   $array["lizenz_verlag_uid"] = (int)$vid;
   $array["lizenz_paket"] =  $node->field_kamp_preisnivau['und'][0]['tid'];
   $array["lizenz_team"] =  (int)$team_id;
   $array["lizenz_until"] = time() + (60 * 60 * 24 * 30);
   $lizenz_id = db_insert('lk_vku_lizenzen')->fields($array)->execute();
          
   lizenz_log_augaben($lizenz_id);
         
   $days =  (lk_get_lizenz_time(user_load($vku_author)));  
   $dateplz = date('Y-m-d',strtotime(date("Y-m-d", time()) . " + ". $days ." day"));
   $plz_id = na_create_node_rule($nid, $vku_author, $dateplz);
   
   \LK\Stats::countPurchasedVKU($vku);
   
   if($plz_id){
      db_query("UPDATE lk_vku_lizenzen SET plz_sperre_id='". $plz_id ."' WHERE id='". $lizenz_id ."'");  
   }
  
   if($generic = $vku -> get("vku_generic")){
   	$msg = 'Lizenz direkt erworben';   		
   }
   else {
   	$msg = 'Lizenz erworben';
   }
   
   $log = new \LK\Log\Verlag($msg);
   $log -> setNid($vku_id);
   $log ->setLizenz($plz_id);
   $log ->setVku($vku_id);
   $log ->save();
   
   return getLizenz($lizenz_id);
}


function getLizenz($lizenz_id){
  $dbq = db_query("SELECT * FROM lk_vku_lizenzen WHERE id='". $lizenz_id ."'");
  return $lizenz = $dbq -> fetchObject();
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



/** 
 * Gets a Standard PDF based from a VKU
 * 
 * @param Object $vku
 * @return type
 */
function vku_generate_get_pdf_object($vku){

  
  $author = $vku -> getAuthor();
  
  $account = \LK\get_user($author);
  $verlag_uid = $account -> getVerlag();
  
  if($account ->isModerator()){
      $verlag_uid = LK_TEST_VERLAG_UID;
  }
  
  $pdf = generate_pdf_object_verlag((int)$verlag_uid);
  
return $pdf;  
}

/**
 * Gets a PDF Object
 * 
 * @global type $user
 * @param type $verlag
 * @return \PDF
 */
function generate_pdf_object_verlag($verlag = 0){
global $user; 

    // If Local
    if($verlag){
        $verlag_id = $verlag;
    }
    else {
       // Get the Verlag ID from current User 
       $account = _lk_user($user);
       $verlag_return = lk_get_verlag_from_user($account);
       $verlag_id = (int)$verlag_return; 
    }
    
    $pdf = \LK\PDF\PDF_Loader::load();
    
    // Instanciation of inherited class
    $pdf -> setVKUDefaults();
    $pdf -> setVerlag($verlag_id);
    $pdf -> SetMargins(0);
    $pdf -> SetTopMargin(30);
    $pdf -> AliasNbPages(); 
    $pdf -> SetTextColor(69, 67, 71);

 return $pdf;   
}


// Items
function vku_generate_pdf_default($vku, $page, $pdf){
      
    $module_dir = 'sites/all/modules/lokalkoenig/vku/pages/';

    switch($page["data_class"]){
          case 'title':
            require($module_dir.'/a-cover.php');
            break;

          case 'contact':
          case 'kontakt':
            require($module_dir.'/z-contact.php');
            break;
          
          case 'kplanung':
            require($module_dir.'/r-kampagnenplanung.php');
            break;
            
          case 'onlinewerbung':
            require($module_dir.'/q-onlinewerbung.php');
            break;  
           
          case 'wochen':
              require($module_dir.'/p-wochenblaettern.php');
          break;  
              
          case 'tageszeitung':
              require($module_dir.'/o-tageszeitungen.php');
          break; 
      }
}

// Node
function vku_generate_pdf_node($vku, $page, $pdf){
     
     $nid = $page["data_entity_id"];
     $node = node_load($nid); 
     $module_dir = 'sites/all/modules/lokalkoenig/vku/pages/';

     require($module_dir.'/b-medias.php');
}



/**
 * Sortiert die Medien, so dass sie für die Ausgabe
 * verwendet werden können
 * 
 * @param Object $node
 */
function _vku_load_order_node(&$node){
    
    // Serie, Medien umsortieren
    if(count($node -> medien) > 2){
       $medien = array();
       $medien2 = array();
        
       
       
       // Print  
       foreach($node -> medien as $media){
           $test = _lk_get_medientyp_print_or_online($media->field_medium_typ['und'][0]['tid']);
           
           if(!lk_upgrade_medienformate() AND (isset($media -> variante) AND $media -> variante == 1)){
              continue;
           } 
           
           if($test == 'print'){
             $medien[] = $media; 
           }
           else {
               $medien2[] = $media; 
           }
       }

       foreach($medien2 as $media){
          $medien[] = $media;
       }

      $node -> medien = $medien;     
    }
}


function _vku_load_vku_settings_node(&$node, $page){
    
    if(!isset($page["data_serialized"]) OR !$page["data_serialized"]){
        $data = array();
    }
    else {
       $data = unserialize($page["data_serialized"]);
    }
    
   
    $node -> vku_hide = false;
    
    if($data AND isset($data["desc"]) AND $data["desc"] == 1){
        $node -> vku_hide = true;
    }
    
    // Parse Medien
    while(list($key, $media) = each($node-> medien)):
        $node -> medien[$key] -> vku_hide = false;
        $node -> medien[$key] -> vku_hide_varianten = false;
        
        // Allgemeine Beschreibung
        if($data AND isset($data["media_" . $media -> id]) AND $data["media_" . $media -> id] == 1){
            $node -> medien[$key] -> vku_hide_varianten = true;
        }
        
        if($data AND isset($data["media_" . $media -> id]) AND $data["media_" . $media -> id . "_overview"] == 1){
            $node -> medien[$key] -> vku_hide = true;
        }
    endwhile;       
}

?>