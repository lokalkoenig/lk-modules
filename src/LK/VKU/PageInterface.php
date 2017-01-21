<?php
namespace LK\VKU;

/**
 * Description of PageInterface
 *
 * @author Maikito
 */
abstract class PageInterface {
  
  protected $pagemanager = null;
  var $pdf_pages_dir = null;
  
  function __construct(PageManager $pagemanager) {
    $this->pagemanager = $pagemanager;
    $this -> pdf_pages_dir = __DIR__ .'/../../../vku/pages';
  }
  
  /**
   * Gets the Default Page-options
   */
  final public function getDefaults(){
    $this->pagemanager->getDefaultOptions();
  }
  
  /**
   * Gets back the PDF-Directory
   * @deprecated Should be not used by new Modules
   */
  final public function getPDFFileDirectory(){
    return $this->pdf_pages_dir;
  }
  
  abstract function getImplementation(\VKUCreator $vku, $item, $page);
  
  abstract function getPossibilePages($category, \LK\User $account);

  abstract function saveNewItem(array $item);
  
  abstract function updateItem(\VKUCreator $vku, $pid, array $item);
  
  abstract function removeItem(\VKUCreator $vku, $pid);
  
  function getOutputPDF($page, $pdf){
    $pdf->AddPage();
  }
  
  abstract function getOutputPPT();  
}
