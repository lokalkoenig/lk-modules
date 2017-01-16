<?php

use LK\VKU\Vorlage;
use LK\VKU\Pages\PageManager;

namespace LK\VKU;

/**
 * Description of VKU2
 *
 * @author Maikito
 */
class VKU2 {
  
  use \LK\Log\LogTrait;
  use \LK\Stats\Action;
  
  var $vku = null;
  var $response = [];
  
  function __construct(\VKUCreator $vku, $response) {
    $this->vku = $vku;
    $this->response = $response;
  }
  
  /**
   * Checks the given Signature of the Response
   * and may sends back an Signature-Error
   */
  public function checkSignature(){
    if(!isset($this->response['signature']) || $this->response['signature'] != $this->getVKU()->get("vku_changed")){
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
      $pages = vku2_generate_category_pages($new_vku);
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
  protected function sendJSON($arr){
    
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
