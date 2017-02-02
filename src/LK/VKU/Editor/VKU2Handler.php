<?php
namespace LK\VKU\Editor;

$GLOBALS['vku_document_manager'] = NULL;

/**
 * Description of VKU2Handler
 *
 * @author Maikito
 */
class VKU2Handler extends \LK\VKU\PageInterface {

  private function getDocumentHandler(){

    if(!$GLOBALS['vku_document_manager']){
      $GLOBALS['vku_document_manager'] = new UserManager(\LK\current());
    }

    return $GLOBALS['vku_document_manager'];
  }

  function getImplementation(\VKUCreator $vku, $item, $page){
    $item["delete"] = true;
    $item["id"] = $page["id"];
    $item["cid"] = $page["data_category"];
    $item["preview"] = true;
    $item["orig-id"] = 'vku_documents-' . $page["data_class"];
    $item["pages"] = 1;

    $manager = $this->getDocumentHandler();
    $document = $manager->getDocument($page["data_class"]);
    $item["title"] = $document ->getTitle();
    $item["additional_title"] = '<small>(' . $document ->getPageTitle() . ')</small>';
   
    return $item;
  }
  
  function getPossibilePages($category, \LK\User $account){

    if(!$account ->isModerator()){
      return false;
    }
    
    $manager = $this ->getDocumentHandler();
    return $manager->getDocumentsPerCategory($category);
  }

  function saveNewItem(array $item){
    
    $manager = $this->getDocumentHandler();
    $document = $manager->getDocument($item["data_class"]);
    $newdoc = $manager->cloneDocument($document);
    $item['data_entity_id'] = $newdoc ->getId();
    
    return $item;
  }


  function updateItem(\VKUCreator $vku, $pid, array $item){ }

  /**
   * Removes a Document
   *
   * @param \VKUCreator $vku
   * @param int $pid
   */
  function removeItem(\VKUCreator $vku, $pid){
    $page = $vku ->getPage($pid);
    $manager = $this->getDocumentHandler();
    $manager ->removeDocument($page['data_entity_id']);
  }
  
  function getOutputPDF($page, $pdf) {

    $manager = $this->getDocumentHandler();
    $document = $manager->getDocumentMitarbeiter($page["data_entity_id"]);

    $pdf->AddPage();
    $pdf -> SetTopMargin(30);
    $pdf -> SetLeftMargin(25);
    $pdf -> SetRightMargin(120);
    $pdf -> Ln(15);
    $pdf->SetFont(VKU_FONT,'B',28);
    $pdf->MultiCell(0, 0, $document ->getTitle(), 0, 'L', 0); 
  }
  
  function getOutputPPT($page, $ppt) {
    
  }
}
