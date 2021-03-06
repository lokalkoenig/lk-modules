<?php
namespace LK\VKU\Pages\Interfaces;

/**
 * Description of PageInterface
 *
 * @author Maikito
 */
abstract class PageInterface {
  
  protected $pagemanager = null;
  var $pdf_pages_dir = null;

  function __construct(\LK\VKU\PageManager $pagemanager) {
    $this->pagemanager = $pagemanager;
    $this -> pdf_pages_dir = 'sites/all/modules/lokalkoenig/vku/pages';
  }

  /**
   * @return \VKUCreator VKU
   */
  final function getVKU(){
    return $this->getPageManager()->getVKU();
  }

  /**
   * Gets the VKU-Author
   *
   * @return int UID
   */
  final function getAuthor(){
    return $this->getVKU()->getAuthor();
  }

  /**
   * Gets the Page-Manager
   *
   * @return \LK\VKU\PageManager
   */
  final function getPageManager(){
    return $this->pagemanager;
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

  /**
   * Gets the Implementation of the field
   */
  abstract function getImplementation($item, $page);

  /**
   * Gets the possible Pages
   */
  abstract function getPossibilePages($category);

  /**
   * Saves a new item
   */
  abstract function saveNewItem(array $item);
  
  /**
   * Gets an Action for adding a new item 
   * 
   * @param array $item
   * @return string
   */
  function saveNewItem_action(array $item){
    return null;
  }

  /**
   * Updates an Item
   *
   * @param \VKUCreator $vku
   * @param int $pid
   * @param array $item
   */
  function updateItem($pid, array $item){}

  /**
   * Removes an Item
   *
   * @param \VKUCreator $vku
   * @param int $pid
   * @param array $item
   */
  function removeItem($pid, array $item){
  
  }

  /**
   * Renew a page, copy from a Vorlage
   *
   * @param \VKUCreator $vku
   * @param array $items
   */
  function renewItem($items){
    return $items;
  }

  /**
   * Generates a PDF Output of the page
   *
   * @param array $page
   * @param \LK\PDF\PDF $pdf
   * @param \VKUCreator $vku
   */
  function getOutputPDF($page, \LK\PDF\LK_PDF $pdf){
    $pdf->AddPage();
  }

  /**
   * Creates a PPT Slide
   *
   * @param array $page
   * @param \LK\PPT\LK_PPT_Creator $ppt
   * @param \VKUCreator $vku
   */
  function getOutputPPT($page, \LK\PPT\LK_PPT_Creator $ppt){
    $ppt ->createSlide();
  }
}
