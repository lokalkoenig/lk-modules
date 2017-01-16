<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\VKU;

/**
 * Description of PageInterface
 *
 * @author Maikito
 */
abstract class PageInterface {
  
  protected $pagemanager = null;
  
  function __construct(PageManager $pagemanager) {
    $this->pagemanager = $pagemanager;
  }
  
  /**
   * Gets the Default Page-options
   */
  final public function getDefaults(){
    $this->pagemanager->getDefaultOptions();
  }
  
  
  abstract function getImplementation(\VKUCreator $vku, $item, $page);
  
  abstract function getOutputPDF();
  
  abstract function getOutputPPT();  
}
