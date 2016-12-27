<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//require_once __DIR__ . "/func_vku2_callback.php";

function vku2_generate_form(VKUCreator $vku){
    
   drupal_add_js(drupal_get_path('module', 'vku') .'/js/vku2.js', 'file');
   drupal_add_css(drupal_get_path('module', 'vku') .'/css/vku2.css');
   drupal_add_css(drupal_get_path('module', 'vku') .'/css/vku2-generate.css');
    
   drupal_set_title("Verkaufsunterlage");
   lk_set_icon('tint');
   
   $uid = $vku ->getAuthor();
   $print = vku2_generate_get_print($uid);
   $online = vku2_generate_get_online($uid);
   $sonstiges = vku2_generate_get_other($uid);
   $pages = vku2_generate_category_pages($vku);
   
   $status = $vku ->getStatus();
   $generated = theme("vku2_items", array("items" => $pages, 'vku' => $vku));
   
   $title = $vku -> get("vku_title");
   if(!$title){
       $title = 'NEU';
   }
   
   lk_set_subtitle('<span class="vku-title">' . $title . '</span><span class="pull-right label label-primary label-vku-editor">VKU Editor 2.0</span>');
   
   $array = array('items' => $generated, 'print' => $print, 'online' => $online, 'sonstiges' => $sonstiges, 'vku' => $vku, 'kampagnen' => array());
   
   if($status == 'template'){
       drupal_set_title("Vorlage");
       $array["dokumente"] = theme("vku2_documents", $array);
       
       return theme('vku2_template', $array);    
   }
   
   $array["templates"] = vkuconnection_get_user_templates($uid);
   $array["kampagnen"] = vku2_generate_kampagnen_to_add($uid); 
   $array["ausgaben"] = vku2_get_ausgaben_hinweis($vku, $uid);
   
   $array["dokumente"] = theme("vku2_documents", $array);
   
   return theme('vku2', $array);    
}

function vku2_get_ausgaben_hinweis(VKUCreator $vku, $user_uid){
    
   // Add Ausgaben-Support für Ausgaben
     // Show current Ausgaben, before VKU-Sumit
     $account = \LK\get_user($user_uid);
     $ausgaben =  $account -> getCurrentAusgaben();
        
     $ausgaben_formatted = array();
     foreach($ausgaben as $ausgabe){
        $object = \LK\get_ausgabe($ausgabe);     
         if($object){
             $ausgaben_formatted[] = $object -> getTitleFormatted(); 
         }
     }
     
     
     $link = '';
     // Can adjust theese Settings
     if($account -> isTelefonmitarbeiter()){
         $link = '<a class="pull-right btn btn-sm btn-primary" href="'. url('user/' . $account -> uid . '/setplz', array("query" => array('destination' => 'vku/' . $vku -> getId() .'/finalize'))) .'"><span class="glyphicon glyphicon-cog"></span> Ausgaben anpassen</a>';
     }
    
    $days = 0; 
    $verlag = $account -> getVerlag();
    if($verlag){
        $verlag_user = \LK\get_user($verlag);
        $days = $verlag_user -> getVerlagSetting('sperrung_vku_pdf', 0);
    }
     
    if($ausgaben_formatted AND $days){
          return '<div class="row"><div class="col-xs-6">'
            . '<p><span class="glyphicon glyphicon-exclamation-sign"></span> Bitte beachten Sie, dass die Kampagnen für '. $days .' Tage für '
            . 'Ihre ausgewählten Ausgaben vorgemerkt werden:</p></div><div class="col-xs-6">'
            . $link
            . '<ul class="list-inline"><li>' . implode("</li><li>", $ausgaben_formatted) . '</li></ul></div></div>';
        
   }   
    
return false;    
}


function vku2_generate_get_print($uid){
    
    $items = array();
    $items["default-tageszeitung"] = 'Medienargumentation Tageszeitungen';
    $items["default-wochen"] = 'Medienargumentation Wochen-/Anzeigeblätter';
    
return $items;    
}

function vku2_generate_get_online($uid){
    
    $items = array();
    $items["default-onlinewerbung"] = 'Online-Werbung (Display-Ads) ';

 return $items;    
}

function vku2_generate_get_other($uid){
    
    $items = array();
    $items["default-kplanung"] = 'Kampagnenplanung';
    $items["default-kontakt"] = 'Ihre Kontaktdaten';
 
    drupal_alter('vku2_add_sonstiges', $items);

 return $items;    
}


function _vku2_generate_add_new_category_page($vku_id, $type, $page){
   $category_id = db_insert('lk_vku_data_categories')->fields(array('vku_id' => $vku_id, 'category' => $type, 'sort_delta' => $page["data_delta"]))->execute();
   
   _vku2_add_page_to_category($page, $category_id);
}

function _vku2_add_page_to_category($page, $category_id){
   db_query("UPDATE lk_vku_data SET data_category='". $category_id ."' WHERE id='". $page["id"]  ."'");    
}


function vku2_generate_kampagnen_to_add($uid){
    
    $kampagnen = array('last' => array());
    
    $dbq = db_query("SELECT nid FROM lk_lastviewed WHERE uid='". $uid ."' ORDER BY lastviewed_time DESC LIMIT 10");
    while($all = $dbq -> fetchObject()){
        $kampagnen["last"][] = $all -> nid;
    }
    
    
    $kampagnen["merkliste"] = array();
    
    $tags = _get_merklistenterms();
    while(list($key, $val) = each($tags)){
        $kampagnen["merkliste"][$key]["title"] = $val;
        $kampagnen["merkliste"][$key]["nodes"] = array();
        
        $dbq = db_query("SELECT n.field_merkliste_node_nid as nid FROM field_data_field_merkliste_tags t, "
                . "field_data_field_merkliste_node n "
                . "WHERE n.entity_id=t.entity_id AND t.field_merkliste_tags_tid='". $key ."'");
        foreach($dbq as $all){
            $kampagnen["merkliste"][$key]["nodes"][] = $all -> nid;
        }  
        
        if(count($kampagnen["merkliste"][$key]["nodes"]) == 0){
            unset($kampagnen["merkliste"][$key]);
        }    
    }
    
return $kampagnen;    
}





/**
 * Returns back Children Information about a Kampagne
 * 
 * @param Object $node
 * @param Array $defaults
 * @return Array
 */
function vku2_get_kampagnen_childs($node, $defaults){
    $children = array();
    
    $children['desc'] = $defaults;
    $children['desc']["title"] = 'Allgemeine Kampagnenbeschreibung';
    
    foreach($node -> medien as $media){
        $tax = taxonomy_term_load($media->field_medium_typ['und'][0]['tid']);
        if($tax->description){
            $term_title = $tax->description;
        }
        else {
            $term_title = $tax -> name;
        }
        
     if(!$media->field_medium_main_reference){
         
        $filetype = _lk_get_medientyp_print_or_online($media->field_medium_typ['und'][0]['tid']);
        
        
        if($filetype == 'print'){
            $ext_title = 'Printanzeige';
        }
        else {
            $ext_title = 'Online-Anzeige';
        }
        
        $id = 'media_' . $media -> id . "_overview";
        
        $children[$id] = $defaults;
        
        $children[$id]["title"] = '<strong>Beschreibungstext ' . $ext_title . '</strong>';
     }   
     
        $farben = array();
        foreach($media->field_medium_varianten['und'] as $item){
            $farben[] = ucfirst($item["title"]);
        }
     
        $children['media_' . $media -> id] = $defaults;
        if(isset($tax -> field_medientyp_vku_pages["und"][0]["value"])):
           $children['media_' . $media -> id]["pages"] = $tax -> field_medientyp_vku_pages["und"][0]["value"]; ;        
        endif;    
        
        $children['media_' . $media -> id]["title"] = ' - Farbvarianten: ' . $media -> title . " (". $term_title .")<small class='varianten'>". implode(", ", $farben) ."</small>";
    }
    
    
    while(list($key, $val) = each($children)){
        $children[$key]["id"] = $key;
        
        if(!isset($children[$key]["pages"])){
            $children[$key]["pages"] = 1;
        }
    }
    
    reset($children);
    
 return $children;   
 }
 
 
/**
 * 
 * @param VKUCreator $vku
 * @return Array
 */
function vku2_generate_category_pages(VKUCreator $vku){
    
    // Assuming there is nothing
    $categories = vku2_generate_vku_categories();
    
    $default = array('has_children' => false, 
                    'delete' => false, 
                    'preview' => false,
                    'container' => false, 
                    'children_sortable' => false,
                    'children' => array(),
                    'empty_shown' => false,
                    'deactivate' => false,
                    'single_toggle' => false,
                    'id' => 0,
                    'orig-id' => '',
                    'pages' => 1,
                    'kampagne' => false,
                    'collapsed' => false,
                    'class' => array(),
                    'drop-zone' => false
        );
    
    $pages = $vku ->getPages();
    $structure = array();
    $dbq = db_query("SELECT * FROM lk_vku_data_categories WHERE vku_id='". $vku -> getId() ."' ORDER BY sort_delta ASC");
    while($all = $dbq -> fetchObject()){
        
        $structure[$all -> id] = $default;
        $structure[$all -> id]["type"] = $all -> category;
        $structure[$all -> id]["cid"] = $all -> id;
        $structure[$all -> id]["id"] = 0;
        $structure[$all -> id]["active"] = true;
        
        // Print & Online
        if(in_array($all -> category, array('print', 'online'))){
            // Later
            $structure[$all -> id]["has_children"] = true;
            $structure[$all -> id]["title"] = '<strong>Argumentation ' .ucfirst($all -> category) . '</strong>';
            $structure[$all -> id]["delete"] = true;
            $structure[$all -> id]["container"] = true;
            $structure[$all -> id]["children_sortable"] = true;
            $structure[$all -> id]["empty_shown"] = true;
            $structure[$all -> id]["collapsed"] = true;
            $structure[$all -> id]["class"][] = 'dropable-items-' . $all -> category;
            $structure[$all -> id]["drop-zone"] = 'drop-zone-' . $all -> category;
            
            // get Children
            
            $dbq2 = db_query("SELECT id FROM lk_vku_data WHERE data_category='". $all -> id ."' ORDER BY data_delta ASC");
            foreach($dbq2 as $child){
                
                $page = $vku ->getPage($child -> id);
                
                $item = $default;
                $item["cid"] = $all -> id;
                $item["id"] = $child -> id;
                $item["title"] = $page["data_class"];
                
                $func_name = 'vku2_get_doc_info_' . $page["data_module"];
                if(function_exists($func_name)){
                    $return = $func_name($vku, $item, $page);
                    
                    if(!$return){
                        continue;
                    }
                    
                    $item = $return;
                }
                else {
                    continue;
                }
                
                $structure[$all -> id]["children"][] = $item;
            }
        }
        
        // Category Other
        if($all -> category == 'other'){
            $seite = null;
            
            $structure[$all -> id]["children"] = false;
            foreach($pages as $page):
                if($page["data_category"] == $all -> id){
                   $seite = $page;
                }
            endforeach;
        
            if(!$seite){
                unset($structure[$all -> id]);
                continue;
            }
            
            $func_name = 'vku2_get_doc_info_' . $seite["data_module"];
            
            if(function_exists($func_name)){
               $test = $func_name($vku, $structure[$all -> id], $seite); 
               
              if($test == false){
                 unset($structure[$all -> id]);
                 continue;  
               }
               
               $structure[$all -> id] = $test;
               
            }
            else {
               unset($structure[$all -> id]);
               continue; 
            }
        }
        
        if($all -> category == 'kampagne'){
            $kampagnen_page_id = null;
            $seite = null;
            foreach($pages as $page):
                if($page["data_category"] == $all -> id){
                   $seite = $page;
                   break;
                }
            endforeach;
            
            $func_name = 'vku2_get_doc_info_kampagne';
            $test = $func_name($vku, $structure[$all -> id], $seite);
            
            if(!$test){
                unset($structure[$all -> id]); 
                continue;
            }
            else {
                $structure[$all -> id] = $test;
            }
        }
        
        // Title
        if($all -> category == 'title'){
            
            $seite = null;
            foreach($pages as $page):
                if($page["data_category"] == $all -> id){
                   $seite = $page;
                   break;
                }
            endforeach;
            
            $test = vku2_get_doc_info_default($vku, $structure[$all -> id], $page);
            if($test){
                $structure[$all -> id] = $test;
            }
            else {
                unset($structure[$all -> id]);
                continue;
            }
        }
    }
    
    return $structure;
}


function vku2_get_doc_info_kampagne($vku, $item, $page){
    
    $default_kampagne = $item;
    $default_kampagne["single_toggle"] = true;
    $default_kampagne["deactivate"] = true;
    $default_kampagne["active"] = 1;
    
    $item["has_children"] = true;
    $item["delete"] = true;
    $item["class"][] = 'entry-kampagne';
    
    $item["id"] = $page["id"];
    $item["cid"] = $page["data_category"];
    
    $node = node_load($page["data_entity_id"]);
    $sid = _lk_get_kampa_sid($node);
    
    $item["preview"] = true;
    $item["kampagne"] = true;
    $item["orig-id"] = 'kampagne-' . $node -> nid;
    $item["title"] = '<span class="prodid">'. $sid .'</span><span class="hidden"> / </span><span calss="kampagne-title">' . $node -> title . '</span>';
    
    
    if(!vku2_node_can_add($node -> nid, $vku -> getAuthor())){
       $item["additional_title"] .= '<small class="error">Die Kampagne kann nicht lizenziert werden.</small>'; 
    }
    
    $item["collapsed"] = true;
    $item["children"] = vku2_get_kampagnen_childs($node, $default_kampagne);
    
    $settings = $page["data_serialized"];
    
    // if we have saved settings
    if($settings):
       $subpages = unserialize($settings);
              
       while(list($key, $val) = each($item["children"])){
          if(isset($subpages[$key]) AND $subpages[$key] == 1){
              $item["children"][$key]["active"] = 0;
          }    
          else {
              $item["children"][$key]["active"] = 1;
          }
       }
    endif;
            
    reset($item["children"]);
           
    $pages_count = 0;
    // count pages
    while(list($key, $val) = each($item["children"])){
       if($val["active"]){
           $pages_count += $val["pages"];
       }
    }
            
    $item["pages"] = $pages_count;

return $item;            
}

/**
 * Gets back yes or no, if Node can be licenced
 * 
 * @param Int $nid
 * @param Int $uid
 * @return boolean
 */
function vku2_node_can_add($nid, $uid){
    
    $node = node_load($nid);
    $access = na_check_user_has_access($uid, $node -> nid);
    
    if(!$node -> status OR !$access["access"]){
        return false;
    }
    
return true;    
}


/**
 * Gets back Information about the Default VKU-Documents
 * 
 * @param type $vku
 * @param type $item
 * @param type $page
 * @return boolean
 */
function vku2_get_doc_info_default($vku, $item, $page){
    
    $item["preview"] = true;
    $item["delete"] = true;
    $item["id"] = $page["id"];
    $item["cid"] = $page["data_category"];
    
    switch($page["data_class"]){
        case 'wochen':
             $item["title"] = 'Medienargumentation Wochen-/Anzeigeblätter';
            break;
        
        case 'tageszeitung':
            $item["title"] = 'Medienargumentation Tageszeitungen';
            break;
     
        case 'onlinewerbung':
            $item["title"] = 'Online-Werbung (Display-Ads)';
            break;
        
        
        case 'kontakt':
            $item["title"] = 'Ihre Kontaktdaten';
            break;
        
        case 'kplanung':
            $item["title"] = 'Kampagnenplanung';
            break;
        
        case 'title':
             $item["active"] = $page["data_active"];
            
             $vku_title = $vku -> get("vku_title");   
             
             if(empty($vku_title)):
                 $vku_title = 'Ohne Titel';
             endif;
            
             $item["title"] = 'Titelseite: <span class="vku-title">' . $vku_title . '</span>';
             $item["delete"] = false;
             $item["deactivate"] = true;
             $item["container"] = false;
             $item["empty_shown"] = true;
             
             
             break;
        
        default :
            return false;
         break;   
    }
    
    $item["orig-id"] = 'default-' . $page["data_class"];
    
return $item;    
}


/**
 * Returns the Basic Configuration of the
 * new VKU2 Categories
 * 
 * @return Array
 */
function vku2_generate_vku_categories(){
    
    
    $categories = array();
    $categories["title"] = array(
            'title' => 'Titelseite',
            'children' => false,
            'children_sortable' => false,
            'delete' => false,
            'deactivate' => true,
            'empty_shown' => true,
            'delta' => 1,
    );
    
    $categories["print"] = array(
            'title' => 'Print',
            'children' => true,
            'delete' => true,
            'children_sortable' => true,
            'empty_shown' => true,
            'deactivate' => true,
            'delta' => 2
    );
    
    $categories["online"] = $categories["print"];
    $categories["online"]["title"] = 'Online';
    $categories["online"]["delta"] = 3;
    
    
    $categories["kampagne"] = array(
            'title' => 'Kampagne',
            'children' => true,
            'children_sortable' => false,
            'delete' => true,
            'deactivate' => false,
            'delta' => 4,
            'empty_shown' => false,
    );
    
    $categories["other"] = array(
            'title' => 'Sonstiges',
            'children' => false,
            'children_sortable' => false,
            'delete' => true,
            'deactivate' => true,
            'empty_shown' => false,
    );
    
   return $categories;
}


/**
 * Copy a Vorlage to a existing VKU
 * 
 * 
 * @param VKUCreator $vku
 * @param Integer $vorlage_vku_id
 * @return VKUCreator
 */
function vku2_generate_takeover_vorlage(VKUCreator $vku, $vorlage_vku_id){
    
    $vorlage = new VKUCreator($vorlage_vku_id);
    // Check vorlage
    
    $copy = new VKUCreator($vku -> getId());
    
    // empty VKU
    $pages = $copy ->getPages();
    foreach($pages as $page){
        if(!in_array($page["data_class"], array("kampagne"))){
            $copy ->removePage($page["id"]);
            $copy ->removeCategory($page["data_category"]);
        } 
    }   
    
    $test = $copy ->getCategoryByName('print');
    
    foreach($test as $item){
        $copy ->removeCategory($item);
    }  
    
    $test2 = $copy ->getCategoryByName('online');
    foreach($test2 as $item){
        $copy ->removeCategory($item);
    }  
    
    $entries = $vorlage ->getCategoryByName('print');
    $vorlage_catgegory_print = $vorlage -> getCategory($entries[0]);
    
    $entries = $vorlage ->getCategoryByName('online');
    $vorlage_catgegory_online = $vorlage -> getCategory($entries[0]);
    
    $category_print = $copy ->setDefaultCategory('print', $vorlage_catgegory_print -> sort_delta);
    $category_online = $copy ->setDefaultCategory('online', $vorlage_catgegory_online -> sort_delta);;
    
     // we create new once
    // we have now an VKU with only Kampagnen
    $pages_vorlage = $vorlage -> getPages();
    foreach($pages_vorlage as $page){
        
        $catgegory = $vorlage -> getCategory($page["data_category"]);
        
        if($catgegory -> category == 'print'){
            $cid = $category_print;
        }    
        elseif($catgegory -> category == 'online'){
            $cid = $category_online;
        }    
        else {
            // we add an new Category and the Page
            $cid = $copy ->setDefaultCategory($catgegory -> category, $catgegory -> sort_delta);
        }
        
        $serialized = null;
        if($page["data_serialized"]){
            $serialized = unserialize($page["data_serialized"]);
        }    
        
        $copy -> data -> add($page["data_module"], $page["data_class"], $page["data_delta"], $page["data_active"], $page["data_entity_id"], $serialized, $cid);
    }
    
    // we got an empty one
    
    $final = new VKUCreator($copy -> getId());
    
  return $final;  
}    