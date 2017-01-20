<?php

function vku_get_top_menu(){
global $user;
  
  drupal_add_js(drupal_get_path('module', 'vku') .'/js/vku2-handling.js', 'file');
  drupal_add_css(drupal_get_path('module', 'vku') .'/css/vku2.css');
 
 $array = array(); 
 $dbq = db_query("SELECT vku_id FROM lk_vku WHERE vku_status='active' AND uid='".$user -> uid  ."' ORDER BY vku_changed DESC");
 foreach($dbq as $all){
   $array[]  = new VKUCreator($all -> vku_id);
 }
 
 return theme("vku_menu", array('vkus' => $array));   
}


/**
  * @deprecated 
  */
 function vku_get_active_id(){
 global $user; 
  
    return \LK\VKU\VKUManager::getActiveVku($user -> uid);
 }

 function get_nid_in_vku_count($nid, $vku_status = array(), $where = array()){

  $where_first = array();
  
  //$where = array();
  $where_first[] = "n.data_entity_id='". $nid  ."'";
  $where_first[] = "n.data_class='kampagne'";
  
  if($vku_status){
    $where_first[] = "v.vku_status IN ('". implode("','", $vku_status)  ."')";
  }
  
  if($where) {
    foreach($where as $item){
      $where_first[] = $item;
    }
  }


   $dbq = db_query("SELECT 
        count(*) as count
        FROM lk_vku_data n, lk_vku v 
        WHERE 
            n.vku_id=v.vku_id 
           
            AND " . implode(" AND ", $where_first));
  $all = $dbq -> fetchObject();
  
  return $all -> count;
}  
 

  function vku_active_user_notfinal_count($uid){
    //created ready downloaded
    $dbq = db_query("SELECT count(*) as count FROM lk_vku WHERE uid='". $uid ."' AND vku_status IN ('active', 'ready', 'created', 'downloaded')");
    $result = $dbq -> fetchObject();
    
  return $result -> count;  
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
