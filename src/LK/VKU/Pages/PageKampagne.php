<?php
/*
 * @file
 * The Defintition for Kampagnen in VKU
 */

namespace LK\VKU\Pages;
use LK\VKU\Pages\Interfaces\PageInterface;

/**
 * Description of PageKampagne
 *
 * @author Maikito
 */
class PageKampagne extends PageInterface {
  
  /**
   * 
   * @param type $item
   * @param type $page
   * @return type
   */
  function getImplementation($item, $page){

    $node = \node_load($page["data_entity_id"]);
    if(!$node || $node -> status === 0){
      return false;
    }
 
    $default_kampagne = $item;
    $default_kampagne["single_toggle"] = true;
    $default_kampagne["deactivate"] = true;
    $default_kampagne["active"] = 1;
    
    $item["has_children"] = true;
    $item["delete"] = true;
    $item["class"][] = 'entry-kampagne';
    
    $item["id"] = $page["id"];
    $item["cid"] = $page["data_category"];
    
    
    $kampagne = new \LK\Kampagne\Kampagne($node);
    $sid = $kampagne->getSID();
    
    $item["preview"] = true;
    $item["kampagne"] = true;
    $item["orig-id"] = 'kampagne-' . $node -> nid;
    $item["title"] = '<span class="prodid">'. $sid .'</span><span class="hidden"> / </span><span calss="kampagne-title">' . $node -> title . '</span>';
    
    if(!$kampagne -> canPurchase()){
      $item["additional_title"] = '<small class="error">Die Kampagne kann nicht lizenziert werden.</small>'; 
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
   * Save the Children configurations
   * 
   * @param \VKUCreator $vku
   * @param id $pid
   * @param array $item
   */
  function updateItem($pid, array $item){
  
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

    $vku = $this->getPageManager()->getVKU();
    $vku -> setPageSerializedSetting($pid, $save); 
  }
  
  
  /**
   * Adds an Item to the VKU
   */
  function saveNewItem(array $item){
    $item['data_entity_id'] = $item['data_class'];
    $item['data_class'] = 'kampagne';
    $item['data_module'] = 'node';
    $item['data_serialized'] = serialize([]);

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
   * Gets the possible Pages
   * 
   * @param string $category
   * @return array
   */
  function getPossibilePages($category){
    
    if($category === 'kampagnen'){
      return $this->getSuggestedKampagnen();
    }
    
    return [];  
  }
  
  
  /**
   * Get the Suggested Kampagnen for the given User
   * 
   * @param \LK\User $account
   * @return array
   */
  function getSuggestedKampagnen(){

    $account = $this->getPageManager()->getAuthorObject();
    $uid = $account->getUid();
    
    $kampagnen = array('last' => array());
    $dbq = db_query("SELECT nid FROM lk_lastviewed WHERE uid='". $uid ."' ORDER BY lastviewed_time DESC LIMIT 10");
    while($all = $dbq -> fetchObject()){
        $kampagnen["last"][] = $all -> nid;
    }
    
    $kampagnen["merkliste"] = array();
    
    $mlm = new \LK\Merkliste\UserMerkliste();
    $listen = $mlm ->getTerms();
    
    while(list($key, $val) = each($listen)){
      $merkliste = $mlm ->loadMerkliste($key);
      $kampagnen["merkliste"][$key]["title"] = $merkliste ->getName();
      $nodes = $merkliste ->getKampagnen();
      foreach($nodes as $nid){
        $kampagnen["merkliste"][$key]["nodes"][] = $nid;
      }  
    }

    return $kampagnen;  
  }

  /**
   * Loads the GIF-Parts from an Online-Medium
   *
   * @param array $medium
   */
  private function loadGIF(&$medium){
    $gif = new \LK\Kampagne\GIFExtractor();

    while(list($key, $val) = each($medium->field_medium_varianten["und"])){
      $array = $gif -> toArray($val);
      $medium->field_medium_varianten["und"][$key]['gif'] = $array;
    }
  }

  /**
   * Loads the settings to suppress certain VKU-Pages
   * 
   * @param /stdClass $node
   * @param array $page
   */
  private function _vku_load_vku_settings($node, $page = ['data_serialized' => []]){

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

      if($node -> medien[$key]-> media_type === 'online'){
        $this ->loadGIF($node -> medien[$key]);
      }

      // Allgemeine Beschreibung
      if(isset($data["media_" . $media -> id]) AND $data["media_" . $media -> id] == 1){
        $node -> medien[$key] -> vku_hide_varianten = true;
      }

      if(isset($data["media_" . $media -> id]) AND $data["media_" . $media -> id . "_overview"] == 1){
        $node -> medien[$key] -> vku_hide = true;
      }

    endwhile;
    
    return $node;
  }


  /**
   * Gets back the Output of the Kampagne
   * 
   * @param type $page
   * @param type $pdf
   */
  function getOutputPDF($page, \LK\PDF\LK_PDF $pdf){
    // added for Testing purposes
    if(isset($page['node'])){
      $node = $page['node'];
      $node = $this -> _vku_load_vku_settings($page['node']);
    }
    else {
      $nid = $page["data_entity_id"];
      $node = $this -> _vku_load_vku_settings(node_load($nid), $page);
    }

    $kampagne = new \LK\VKU\Pages\Kampagne\Kampagne($node);
    $kampagne->render($pdf);
  }
  
  
  function getOutputPPT($page, $ppt) {
    $nid = $page["data_entity_id"];
    $node = $this -> _vku_load_vku_settings(node_load($nid), $page);

    $obj = new \LK\PPT\Pages\RenderNode($ppt);
    $obj->render($node);
  }
}
