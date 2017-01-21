<?php

use LK\VKU\Vorlage;
use LK\VKU\PageManager;

namespace LK\VKU;

/**
 * Description of VKU2
 *
 * @author Maikito
 */
class VKU2 {
  
  use \LK\Log\LogTrait;
  use \LK\Stats\Action;
  
  var $LOG_CATEGORY = 'VKU2';
  var $vku = null;
  var $response = [];
  var $pagemanager = null;
  
  function __construct(\VKUCreator $vku, $response) {
    $this->vku = $vku;
    $this->response = $response;
  }
  
  /**
   * Gets the PageManager
   * 
   * @return \LK\VKU\PageManager
   */
  private function getPageManager(){
    
    if(!$this->pagemanager){
      $this -> pagemanager = new PageManager();
    }
      
    return $this->pagemanager;  
  }
  /**
   * Checks the given Signature of the Response
   * and may sends back an Signature-Error
   */
  public function checkSignature(){
    if(!isset($this->response['signature']) || $this->response['signature'] != $this->getVKU()->get("vku_changed")){
      $this->logNotice('Signatur-Fehler gesendet gefunden und behoben in: ' . $this->getVKU());
      $this->sendError(['signature_error' => true]);
    }
  }
  
  /**
   * Gets the current response
   * 
   * @return array
   */
  function getResponse(){
    return $this->response;
  }
  
  /**
   * Saves basic Template-Infomation
   */
  public function saveTemplateData(){
    
    $vku = $this->getVKU();
    $obj = $this->getResponse();
    
    $vku ->set('vku_title', $obj["vku_template_title"]);
    $vku ->set('vku_template_title', $obj["vku_template_title"]);
    
    $obj["vku_title"] = $vku -> get('vku_title');
            
    if(empty($obj["vku_title"])){
      $obj["vku_title"] = 'Ohne Titel';
    }
    
    $this->sendJSON($obj);
  }
  
  public function saveVKUPages(){
  
    $new = null;
    $replace_sid = null;
    $vku = $this->getVKU();
    $obj = $this->getResponse();
    $pagemanager = $this->getPageManager();
    
    foreach($obj["data"] as $item){
      $sid = $item["sid"];
      $items = explode("-", $item["sid"]);
      $cid = $items[0];
      $pid = $items[1];
    
      // Load Container
      $category = $vku -> getDefaultCategory($cid);
      
      // Mainly print & online
      if($category && in_array($category -> category, array('print', 'online'))){
        $line[$sid] = array();
        
        // No Entries
        if(!isset($item["children"])):
          $item["children"] = array();
        endif;   
       
        // Check for Children
        foreach($item["children"] as $child){
          $items2 = explode("-", $child["sid"]);
          $pid2 = $items2[1];
                    
          // delete
          if($child["status"] == 2){
            $pagemanager->removePage($vku, $id, $child);
            $obj["item-remove"] = $pid2;
            continue;
          } 
          elseif($child["status"] == 3){
            $data = $pagemanager->addNewPage($vku, $cid, $items2[0], $items2[1], $child); 
            $id = db_insert('lk_vku_data')->fields($data)->execute();
            $replace_sid =  $child["sid"];
            $new = $cid . "-" . $id;
            $pid2 = $id;
          }
          else {
            $pagemanager->updatePage($vku, $id, $child);
          }          
          
          $sid2 = $cid . '-' . $pid2; 
          $line[$sid][] = $sid2;
        }
        
        continue;
      }
      
      // other - items
      
      // new item
      if($item["status"] == 3){
        $cid = $vku ->setDefaultCategory('other', 0);
        $data = $pagemanager->addNewPage($vku, $cid, $items[0], $items[1], $item); 
        $replace_sid =  $sid;
        $id = db_insert('lk_vku_data')->fields($data)->execute();
        $sid = $cid . "-" . $id; 
        $new = $sid;
      }
      // disable
      elseif($item["status"] === 0){
        $vku ->setPageStatus($pid, false);
      }
      // delete item
      elseif($item["status"] == 2){
        $vku ->removePage($pid);
        continue;
      }
      // nothing but update
      else {
        $vku ->setPageStatus($pid, true);
        $pagemanager->updatePage($vku, $pid, $item);
      }
      
      $line[$sid] = array();
    }
   
    // Save the Delta
    $this -> saveDeltaPages($line);
    
    $vku_updated = new \VKUCreator($vku ->getId());
    $pages = $pagemanager ->generatePageConfiguration($vku_updated);
    
    $response = [];
    $response["replace"] = null;
    
    // When there is a new Item we go through all the Items to find the item we need to replace
    if($new){
      $explode = explode("-", $new);
      $cid = $explode[0];
      $pid = $explode[1];
       
      while(list($key, $val) = each($pages)){
        if($cid == $key){
          $response["test"] = $pid;

          // PID can be 0 on print or online
          if($pid === $val["id"]){
              $response["replace_sid"] = $replace_sid;
              $response["replace"] = theme("vku2_item", array("item" => $val, 'vku' => $vku_updated));
          }   
          else {
            while(list($key2, $val2) = each($pages[$key]["children"])){
              if($pid == $val2["id"]){
                $response["replace_sid"] = $replace_sid;
                $response["replace"] = theme("vku2_item", array("item" => $val2, 'vku' => $vku_updated));    
              }
            }
          }
        }
      }
    }
    
    $this->sendJSON($response);
  }
    
    
    
    
  /**
   * Saves the Delta-Position
   * 
   * @param array $line
   */  
  protected function saveDeltaPages($line){
   
   $cid_order = 0;
   $page_order = 0;
   $vku = $this->getVKU();
   
   // save order
    while(list($key, $val) = each($line)){
      $explode = explode("-", $key);
      $cid = $explode[0];
      $pid = $explode[1];
    
      $vku -> setDefaultCategoryOrder($cid, $cid_order);
      $cid_order++;
            
      if($pid){
        $vku -> setPageOrder($pid, $page_order);
        $page_order++;
      }
            
      // means, there are Sub-Items
      if($val){
        foreach($val as $item){
          $explode = explode("-", $item);   
          $pid2 = $explode[1];
          $vku -> setPageOrder($pid2, $page_order);
          $page_order++;
        }
      }
    }
  }

  public function saveTitle(){
    
    $vku = $this->getVKU();
    $obj = $this->getResponse();
    
    $vku->set('vku_title', $obj["vku_title"]);
    $vku->set('vku_company', $obj["vku_company"]);
    $vku->set('vku_untertitel', $obj["vku_untertitel"]);
        
    $status = $vku ->getStatus();
    if($status == 'new'){
      $vku ->setStatus('active');
      $vku ->isCreated();
    }
        
    if(!empty($obj["template"])){
      $new_vku = Vorlage::takeOver($vku, $obj["template"]);
      
      $manager = $this->getPageManager();
      $pages = $manager->generatePageConfiguration($new_vku);
      $generated = theme("vku2_items", array("items" => $pages, 'vku' => $new_vku));
      $obj["renew_items"] = $generated;
    }    
  }
  
  /**
   * Gets the VKU
   * 
   * @return \VKUCreator
   */
  function getVKU(){
    return $this->vku;
  }
  
  /**
   * Sends an Error back
   * 
   * @param array $array
   */
  function sendError($array){
    $array['error'] = 1;
    $this->sendJSON($array);
  }
  
  /**
   * Outputs JSON
   * 
   * @param array $arr
   */
  public function sendJSON($arr){
    
    $time = $this->getVKU()->update();
  
    $defaults = [
      'msg' => null,
      'signature_error' => FALSE,  
      'signature' => $time,
      'changed' => format_date($time, 'short')  
    ];
    
    drupal_json_output($defaults + $arr);
    drupal_exit();  
  }
  
}
