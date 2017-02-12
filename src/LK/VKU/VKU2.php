<?php

use LK\VKU\Vorlage\Vorlage;
use LK\VKU\PageManager;

namespace LK\VKU;

/**
 * Description of VKU2
 *
 * @author Maikito
 */
class VKU2 extends PageManager {
  
  use \LK\Log\LogTrait;
  use \LK\Stats\Action;
  
  var $LOG_CATEGORY = 'VKU2';
  var $vku = null;
  var $response = [];

  function __construct(\VKUCreator $vku, $response) {
    $this->vku = $vku;
    $this->response = $response;

    parent::__construct();
  }

  function performClient(){

    $this->checkSignature();

    $obj = $this->getResponse();
    $obj['error'] = 0;

    $action = $this->getResponseType();
    $vku = $this->getVKU();

     // Save Title
    if($action === 'title'){
      $response = $this->saveTitle();

      $this->sendJSON($response);
      //if($vku -> getStatus() !== 'template'){
      // $this->sendJSON($response);
      //}
    }

    if($action === 'save'){
      // VKU-Title & Template-Title
      if(isset($obj["vku_template_title"]) AND $vku -> getStatus() == 'template'){
        $this -> saveTemplateData();
      }

      $this->sendJSON($this->saveVKUPages());
    }

    if(in_array($action, ['savelast', 'finalize'])){
      $response = $this->finalCheck($response);

      if($response['error'] === 1 || $action === 'savelast'){
        $this->sendJSON($response);
      }

      $this->sendJSON($this->generateExports());
    }
  }

  /**
   * Generates the Export
   *
   * @param array $obj
   * @return array
   */
  private function generateExports($obj){

    $vku_updated = new \VKUCreator($this->getVKU()->getId());

    $export_manager = new \LK\VKU\Export\Manager();
    $pdf = $export_manager ->finalizeVKU($vku_updated);

    if(!$pdf){
      $obj["error"] = 1;
      $obj["msg"] = 'Die Verkaufsunterlage konnte nicht generiert werden.';

      return $obj;
    }

    $vku_updated -> setStatus('ready');

    // Sets the PLZ-Sperre for Short time
    $vku_updated -> setShortPlzSperre();

    // Generate PDF
    $obj["pdf_download_link"] = \url($vku_updated ->downloadUrl());
    $obj["pdf_download_size"] = format_size($vku_updated -> get("vku_ready_filesize"));

    $obj["vku_link"] = \url($vku_updated ->url());
    $obj["ppt_download_link"] = null;
    $obj["ppt_download_size"] = 0;

    if(vku_is_update_user_ppt()):
      // Generate PPT
      $obj["ppt_download_link"] = \url($vku_updated ->downloadUrlPPT());;
      $obj["ppt_download_size"] = format_size($vku_updated -> get("vku_ppt_filesize"));
    endif;

    return $obj;
  }

  /**
   * Checks the VKU for validity
   *
   * @param array $obj
   * @return array
   */
  private function finalCheck($obj){

    $vku_updated = new \VKUCreator($this->getVKU()->getId());
    $obj["error"] = 0;

    $nodes = $vku_updated ->getKampagnen();
    if(count($nodes) > 3){
      $obj["msg"] = 'Sie haben zu viele Kampagnen in Ihrer Verkaufsunterlage. Bitte reduzieren Sie die Anzahl auf maximal 3 Kampagnen.';
      $obj["error"] = 1;

      return $obj;
    }
    
    // check for Kampagnen die nicht lizenziert werden können
    foreach($nodes as $nid){
      if(!lk_can_purchase($nid)){
        $obj["error"] = 1;
        $node = node_load($nid);
        $obj["msg"] = 'Die Kampagne <strong>' . $node -> title . "</strong> kann im Moment nicht lizenziert werden. Bitte löschen Sie diese aus Ihrer Verkaufsunterlage.";
        
        return $obj;
      }
    }
  
    // Check for 0 Pages
    $count = 0;
    $pages = $vku_updated -> getPages();
    foreach($pages as $page){
      if($page["data_active"]){
        $count++;
      }
    }

    if($count == 0):
      $obj["error"] = 1;
      $obj["msg"] = 'Sie haben im Moment keine aktivierten Seiten in Ihrer Verkaufsunterlage.';
    endif;

    return $obj;
  }
  
  /**
   * Checks the given Signature of the Response
   * and may sends back an Signature-Error
   */
  public function checkSignature(){

    $vku = $this->getVKU();

    if(!isset($this->response['signature']) || $this->response['signature'] != $vku->get("vku_changed")){
      $this->logNotice('Signatur-Fehler gesendet gefunden und behoben in: ' . $vku->getTitle());
      $this->sendError(['signature_error' => true, '_false_signatue' => $vku->get("vku_changed"), '_submitted_sign' => $this->response['signature']]);
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

  function  getResponseType(){
    $response = $this->getResponse();
    return $response['type'];
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
  }
  
  public function saveVKUPages(){
  
    $new = null;
    $replace_sid = null;
    $vku = $this->getVKU();
    $obj = $this->getResponse();
    
    foreach($obj["data"] as $item){
      $sid = $item["sid"];
      $items = explode("-", $item["sid"]);
      $cid = $items[0];
      $pid = $items[1];
    
      // Load Container
      $category = $this->_getCategoryData($cid);
      
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
            $this->removePage($vku, $pid2);
            $obj["item-remove"] = $pid2;
            continue;
          }
          // new
          elseif($child["status"] == 3){
            $id = $this->addNewPage($vku, $cid, $items2[0], $items2[1], $child);
            $replace_sid =  $child["sid"];
            $new = $cid . "-" . $id;
            $pid2 = $id;
          }
          else {
            $this->updatePage($vku, $id, $child);
          }          
          
          $sid2 = $cid . '-' . $pid2; 
          $line[$sid][] = $sid2;
        }
        
        continue;
      }
      
      // new item
      if($item["status"] == 3){
        $cid = $this -> addCategory($vku, 'other', 0);
        $id = $this->addNewPage($vku, $cid, $items[0], $items[1], $item);
        $replace_sid =  $sid;
        $sid = $cid . "-" . $id;
        $new = $sid;
      }
      // disable
      elseif($item["status"] === 0){
        $this ->setPageStatus($pid, 0);
      }
      // delete item
      elseif($item["status"] == 2){
        $this->removePage($vku, $pid);
        continue;
      }
      // nothing but update
      else {
        $this -> setPageStatus($pid, 1);
        $this-> updatePage($vku, $pid, $item);
      }
      
      $line[$sid] = array();
    }
   
    // Save the Delta
    $this -> saveDeltaPages($line);
    
    $vku_updated = new \VKUCreator($vku ->getId());
    $pages = $this ->generatePageConfiguration($vku_updated);
    
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
    
    return $response;
  }
    
  /**
   * Saves the Delta-Position
   * 
   * @param array $line
   */  
  protected function saveDeltaPages($line){
   
   $cid_order = 0;
   $page_order = 0;
   $vku = $this -> getVKU();
   
   // save order
    while(list($key, $val) = each($line)){
      $explode = explode("-", $key);
      $cid = $explode[0];
      $pid = $explode[1];
    
      $this -> setCategoryOrder($cid, $cid_order);
      $cid_order++;
            
      if($pid){
        $this -> setPageOrder($pid, $page_order);
        $page_order++;
      }
            
      // means, there are Sub-Items
      if($val){
        foreach($val as $item){
          $explode = explode("-", $item);   
          $pid2 = $explode[1];

          $this -> setPageOrder($pid2, $page_order);
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
    if($status === 'new'){
      $vku ->setStatus('active');
      $vku ->isCreated();
    }
    if(!empty($obj["template"])){
      $new_vku = \LK\VKU\Vorlage\Vorlage::takeOver($vku, $obj["template"]);
      $pages = $this->generatePageConfiguration($new_vku);

      $generated = theme("vku2_items", array("items" => $pages, 'vku' => $new_vku));
      $obj["renew_items"] = $generated;
    }

    return $obj;
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
      'test' => $arr,
      'error' => 0,
      'changed' => format_date($time, 'short')  
    ];

    while(list($key, $val) = each($arr)){
      $defaults[$key] = $val;
    }

    $defaults['signature'] = $time;
    $defaults['signature_error'] = FALSE;

    drupal_json_output($defaults);
    drupal_exit();  
  }
  
}
