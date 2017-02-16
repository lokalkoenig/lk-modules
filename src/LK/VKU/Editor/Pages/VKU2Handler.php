<?php
namespace LK\VKU\Editor\Pages;
use LK\VKU\Pages\Interfaces\PageInterface;
use LK\VKU\Editor\UserManager;

$GLOBALS['vku_document_manager'] = NULL;

/**
 * Description of VKU2Handler
 *
 * @author Maikito
 */
class VKU2Handler extends PageInterface {

  private function getDocumentHandler(){

    if(!$GLOBALS['vku_document_manager']){
      $GLOBALS['vku_document_manager'] = new UserManager(\LK\current());
    }

    return $GLOBALS['vku_document_manager'];
  }

  /**
   * Gets the Implementation for the Frontend
   *
   * @param \VKUCreator $vku
   * @param array $item
   * @param array $page
   * @return array
   */
  function getImplementation(\VKUCreator $vku, $item, $page){
    $item["delete"] = true;
    $item["id"] = $page["id"];
    $item["cid"] = $page["data_category"];
    $item["preview"] = true;
    $item["orig-id"] = 'vku_documents-' . $page["data_class"];
    $item["pages"] = 1;
    $item['edit-handler'] = '<a href="#" class="btn-document-edit" data-edit-id="' . $page["data_entity_id"] . '"><span class="prodid"><span class="glyphicon glyphicon-pencil" style="top: 1px;"></span>&nbsp;&nbsp;Bearbeiten</span></a>';

    $manager = $this->getDocumentHandler();
    $document = $manager->getDocumentMitarbeiter($page["data_entity_id"]);

    if(!$document){
      return FALSE;
    }

    $item["title"] = $document ->getTitle();
    $item["additional_title"] = '<small class="page-title page-title-text-overflow">(<span>' . $document ->getPageTitle() . '</span>)</small>';
   
    return $item;
  }

  /**
   * Gets the possibile Pages per Category
   *
   * @param string $category
   * @param \LK\User $account
   * @return array
   */
  function getPossibilePages($category, \LK\User $account){
    $manager = $this ->getDocumentHandler();
    $documents = $manager->getDocumentsPerCategory($category);

    return $documents;
  }

  function saveNewItem(array $item){
    
    $manager = $this->getDocumentHandler();
    $document = $manager->getDocument($item["data_class"]);
    $newdoc = $manager->cloneDocument($document);
    
    $item['data_entity_id'] = $newdoc ->getId();

    // online-medien-kollektion
    if($item["data_class"] === 'online'){
      $item["data_class"] = $item['data_entity_id'];
    }

    return $item;
  }


  /**
   * Clones an MA-Document
   *
   * @param \VKUCreator $vku
   * @param array $items
   *
   * @return array
   */
  function renewItem(\VKUCreator $vku, $items) {
    $handler = $this->getDocumentHandler();
    $document = $handler->getDocumentMitarbeiter($items['data_entity_id']);
    $new_document = $handler->cloneDocument($document);
    $items['data_entity_id'] = $new_document->getId();
    
    return $items;
  }

  /**
   * Removes a Document
   *
   * @param \VKUCreator $vku
   * @param int $pid
   */
  function removeItem(\VKUCreator $vku, $pid, array $item){
    $manager = $this->getDocumentHandler();
    $manager ->removeDocument($item['data_entity_id']);
  }


  /**
   * Implements an PDF
   *
   * @param array $page
   * @param \PDF $pdf
   */
  function getOutputPDF($page, $pdf) {

    $manager = $this->getDocumentHandler();
    $document = $manager->getDocumentMitarbeiter($page["data_entity_id"]);

    $pdf->AddPage();
    $pdf -> SetTopMargin(30);
    $pdf -> SetLeftMargin(25);
    $pdf -> SetRightMargin(25);
    $pdf -> Ln(15);
    $pdf->SetFont(VKU_FONT,'B',22);
    $pdf->MultiCell(0, 0, $document ->getPageTitle(), 0, 'L', 0);
  }


  function getOutputPPT($page, $ppt) { }
}
