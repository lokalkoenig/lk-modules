<?php
/*
 * @file
 * The Defintition for Kampagnen in VKU
 */

namespace LK\VKU\Pages;
use LK\VKU\PageInterface;

/**
 * Description of PageKampagne
 *
 * @author Maikito
 */
class PageKampagne extends PageInterface {
  
  /**
   * 
   * @param \VKUCreator $vku
   * @param type $item
   * @param type $page
   * @return type
   */
  function getImplementation(\VKUCreator $vku, $item, $page){
   
    $default_kampagne = $item;
    $default_kampagne["single_toggle"] = true;
    $default_kampagne["deactivate"] = true;
    $default_kampagne["active"] = 1;
    
    $item["has_children"] = true;
    $item["delete"] = true;
    $item["class"][] = 'entry-kampagne';
    
    $item["id"] = $page["id"];
    $item["cid"] = $page["data_category"];
    
    $node = \node_load($page["data_entity_id"]);
    $kampagne = new \LK\Kampagne\Kampagne($node);
    $sid = $kampagne->getSID();
    
    $item["preview"] = true;
    $item["kampagne"] = true;
    $item["orig-id"] = 'kampagne-' . $node -> nid;
    $item["title"] = '<span class="prodid">'. $sid .'</span><span class="hidden"> / </span><span calss="kampagne-title">' . $node -> title . '</span>';
    
    if(!$kampagne -> canPurchase()){
      $item["additional_title"] .= '<small class="error">Die Kampagne kann nicht lizenziert werden.</small>'; 
    }
    
    $item["collapsed"] = true;
    $item["children"] = $this->getChildrenImplementation($kampagne, $default_kampagne);
    
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
   * Removes the item from the VKU
   * 
   * @param \VKUCreator $vku
   * @param type $pid
   */
  function removeItem(\VKUCreator $vku, $pid){
    ;
  }
  
  /**
   * Save the Children configurations
   * 
   * @param \VKUCreator $vku
   * @param id $pid
   * @param array $item
   */
  function updateItem(\VKUCreator $vku, $pid, array $item){
  
    $save = array(); 
    foreach($item['children'] as $child){
      $explode = explode("-", $child["sid"]);
      $child_id = $explode[1]; 
       
      if($child["status"] == 0){
          $save[$child_id] = 1;
      }              
      else {
          $save[$child_id] = 0;
      }              
    }
    
    $vku -> setPageSerializedSetting($pid, $save);
  }
  
  
  /**
   * Adds an Item to the VKU
   */
  function saveNewItem(array $item){
    
    $item['data_entity_id'] = $item['data_class'];
    $item['data_class'] = 'kampagne';
    
    $save = array(); 
    foreach($item['children'] as $child){
      $explode = explode("-", $child["sid"]);
      $child_id = $explode[1]; 
       
      if($child["status"] == 0){
          $save[$child_id] = 1;
      }              
      else {
          $save[$child_id] = 0;
      }              
    }
   
    $item['data_serialized'] = serialize($save);
    return $item;
 }
 
 
  
  
  
  /**
   * Gets the Children of the given Kampagne
   * 
   * @param \LK\Kampagne\Kampagne $kampagne
   * @param array $default_kampagne
   * @return array
   */
  private function getChildrenImplementation(\LK\Kampagne\Kampagne $kampagne, $default_kampagne){
    
    $default_kampagne['pages'] = 1;
    
    $children = array();
    $children['desc'] = $default_kampagne;
    $children['desc']["title"] = 'Allgemeine Kampagnenbeschreibung';
    
    $node = $kampagne->getNode();
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
        
        if($filetype === 'print'){
          $ext_title = 'Printanzeige';
        }
        else {
          $ext_title = 'Online-Anzeige';
        }
        
        $id = 'media_' . $media -> id . "_overview";
        $children[$id] = $default_kampagne;
        $children[$id]["title"] = '<strong>Beschreibungstext ' . $ext_title . '</strong>';
     }   
     
      $farben = array();
      foreach($media->field_medium_varianten['und'] as $item){
        $farben[] = ucfirst($item["title"]);
      }
     
      $children['media_' . $media -> id] = $default_kampagne;
      if(isset($tax -> field_medientyp_vku_pages["und"][0]["value"])):
        $children['media_' . $media -> id]["pages"] = $tax -> field_medientyp_vku_pages["und"][0]["value"]; ;        
      endif;    
        
      $children['media_' . $media -> id]["title"] = ' - Farbvarianten: ' . $media -> title . " (". $term_title .")<small class='varianten'>". implode(", ", $farben) ."</small>";
    }
    
    while(list($key, $val) = each($children)){
        $children[$key]["id"] = $key;
    }
    
    reset($children);
    return $children;
  }
  
  /**
   * Gets the possibile Pages
   * 
   * @param string $category
   * @return array
   */
  function getPossibilePages($category, \LK\User $account){
    
    if($category === 'kampagnen'){
      return $this->getSuggestedKampagnen($account);
    }
    
    return [];  
  }
  
  
  /**
   * Get the Suggested Kampagnen for the given User
   * 
   * @param \LK\User $account
   * @return array
   */
  function getSuggestedKampagnen(\LK\User $account){
    
    $uid = $account->getUid();
    
    $kampagnen = array('last' => array());
    $dbq = db_query("SELECT nid FROM lk_lastviewed WHERE uid='". $uid ."' ORDER BY lastviewed_time DESC LIMIT 10");
    while($all = $dbq -> fetchObject()){
        $kampagnen["last"][] = $all -> nid;
    }
    
    $kampagnen["merkliste"] = array();
    
    $mlm = new \LK\Merkliste\Manager();
    
    
    $tags = \_get_merklistenterms();
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
  
  function getOutputPDF(){
    
  }
  
  function getOutputPPT(){
    
  }
  
}
