<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\VKU\Editor;

/**
 * Description of VKU2Handler
 *
 * @author Maikito
 */
class VKU2Handler extends \LK\VKU\PageInterface {
  //put your code here
  
  function getImplementation(\VKUCreator $vku, $item, $page){
    
  }
  
  function getPossibilePages($category, \LK\User $account){
    if($category === 'print'){
      return [
        'vku_documents-1' => 'Bla, DAS ist AWESOME'  
      ];
    }
  return [];  
  }

  function saveNewItem(array $item){
    
  }
  
  function updateItem(\VKUCreator $vku, $pid, array $item){
    
  }
  
  function removeItem(\VKUCreator $vku, $pid){
    
  }
  
  function getOutputPDF(){
    
  }
  
  function getOutputPPT(){
    
  }
}
