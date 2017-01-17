<?php
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
  
  abstract function getPossibilePages($category, \LK\User $account);

  abstract function saveNewItem(array $item);
  
  abstract function updateItem(\VKUCreator $vku, $pid, array $item);
  
  abstract function removeItem(\VKUCreator $vku, $pid);
  
  abstract function getOutputPDF();
  
  abstract function getOutputPPT();  
}
