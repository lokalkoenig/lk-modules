<?php
namespace LK\VKU\Editor\Pages;

use LK\VKU\Pages\Interfaces\PageInterface;
use LK\VKU\Editor\UserManager;
use LK\VKU\Editor\Export\ExportPPTProcessor;
use LK\PPT\LK_PPT_Creator;
use LK\PDF\LK_PDF;
use LK\VKU\Editor\Export\ExportPDFProcessor;

$GLOBALS['vku_document_manager'] = NULL;

/**
 * VKU2Handler
 * Document Handler for VKU 2.0
 *
 * @author Maikito
 */
class VKU2Handler extends PageInterface {

  /**
   * Gets the Document-Handler
   *
   * @return UserManager
   */
  private function getDocumentHandler(){
    
    $account = $this->getAuthor();
    
    if(!$GLOBALS['vku_document_manager']){
      $GLOBALS['vku_document_manager'] = new UserManager(\LK\get_user($account));
    }

    return $GLOBALS['vku_document_manager'];
  }

  /**
   * Gets the Implementation for the Front end
   *
   * @param \VKUCreator $vku
   * @param array $item
   * @param array $page
   * @return array
   */
  function getImplementation($item, $page){
    $item["delete"] = true;
    $item["id"] = $page["id"];
    $item["cid"] = $page["data_category"];
    $item["preview"] = true;
    $item["orig-id"] = 'vku_documents-' . $page["data_class"];
    $item["pages"] = 1;
    $item['edit-handler'] = '<a href="#" class="btn-document-edit" data-edit-id="' . $page["data_entity_id"] . '"><span class="prodid"><span class="glyphicon glyphicon-pencil" style="top: 1px;"></span>&nbsp;&nbsp;Bearbeiten</span></a>';

    $manager = $this->getDocumentHandler(\LK\current());
    $document = $manager->getDocumentMitarbeiter($page["data_entity_id"]);

    if(!$document){
      return FALSE;
    }

    $item["title"] = $document ->getTitle();
    $item["additional_title"] = '<small class="page-title page-title-text-overflow">(<span>' . $document ->getPageTitle() . '</span>)</small>';

    if($document ->getPreset() === 'OnlineMediumCollection'){
      $item['class'][] = 'vku-documents-online';
      $item["additional_title"] = $this->createOnlineMediumSelects($document, $item['edit-handler']);
      $item['edit-handler'] = '<a data-toggle="collapse" href="#document-chooser-'. $document ->getId() .'"><span class="prodid"><span class="glyphicon glyphicon-pencil" style="top: 1px;"></span>&nbsp;&nbsp;Bearbeiten</span></a>';
    }

    return $item;
  }

  /**
   * Gets back the Edit-Form for the OnlineMediumCollection
   *
   * @param \LK\VKU\Editor\Document $document
   * @param string $edit
   * @return string
   */
  private function createOnlineMediumSelects(\LK\VKU\Editor\Document $document, $edit){

    $handler = $this->getDocumentHandler(\LK\current());
    $documents = $handler->getDocumentsPerVerlagPreset('OnlineMedium');

    $id = $document ->getId();

    $select_options = [];
    foreach ($documents as $doc){
      $select_options[$doc['id']] = $doc['document_title'];
    }

    $output = [];

    $content = $document ->getContent();

    $overall_selected = [];
    for($x = 0; $x < 3; $x++){
     $position_relative = $x * 3;
     if(isset($content[$position_relative]) && $content[$position_relative]['value'] != 0){
        $overall_selected[] = $content[$position_relative]['value'];
     }
    }

    for($x = 0; $x < 3; $x++){
      $selected = 0;
      $position_relative = $x * 3;
      if(isset($content[$position_relative])){
        $selected = $content[$position_relative]['value'];
      }

      $output[] = '<p>' . $this->createOnlineMediumSelects_select($document,$select_options, $x, $selected, $overall_selected) . "</p>";
    }

    return '<div class="collapse" id="document-chooser-'. $id .'" style="clear: both;">'
            . '<div class="row" style="padding-top: 10px;"><div class="col-xs-6">'. implode('', $output) .'</div>'
            . '<div class="col-xs-6">'
            . '<p class="help-block">Bitte wählen Sie aus den vorhandenen Online-Medien aus. Sie können den Inhalt auch bearbeiten.</p>'
            . '<div class="text-center">' . str_replace("Bearbeiten", "Text bearbeiten", $edit) . '</div>'
            . '</div>'
            . '</div></div>';
  }

  /**
   * Gets back a Select for the OnlineMediumCollection
   *
   * @param \LK\VKU\Editor\Document $document
   * @param array $options
   * @param int $id
   * @param string $selected
   * @return string
   */
  private function createOnlineMediumSelects_select(\LK\VKU\Editor\Document $document, $options, $id, $selected, $overall_selected){

    $options_composed = ['0' => '- Kein Dokument ausgewählt -'] + $options;

    $select = '<select class="form-control form-select" data-index="'.  $id .'" data-document-id="'.  $document ->getId() .'">';
    while(list($key, $val) = each($options_composed)){
      if($selected == $key){
        $select .= '<option value="'. $key .'" selected="selected">'. $val .'</option>';
      }
      else {
        if(in_array($key, $overall_selected)){
          $select .= '<option value="'. $key .'" disabled="disabled">'. $val .'</option>';
        }
        else {
          $select .= '<option value="'. $key .'">'. $val .'</option>';
        }
      }
    }

    $select .= '</select>';

    return $select;
  }

  /**
   * Gets back an JS-Handler to Update/Init the Widget
   *
   * @param array $item
   * @return string
   */
  function saveNewItem_action(array $item) {
    $handler = $this->getDocumentHandler(\LK\current());
    $document = $handler->getDocumentMitarbeiter($item['data_entity_id']);
  
    if($document ->getPreset() === 'OnlineMediumCollection'){
      return "$('.entry[data-id=". $item['id'] ."] .action-edit a').click();";
    }
  }

  /**
   * Gets the possibile Pages per Category
   *
   * @param string $category
   * @param \LK\User $account
   * @return array
   */
  function getPossibilePages($category){
    $manager = $this ->getDocumentHandler();
    $documents = $manager->getDocumentsPerCategory($category);

    return $documents;
  }

  /**
   * Returns a new Document
   *
   * @param array $item
   * @return array
   */
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
   * @param array $items
   *
   * @return array
   */
  function renewItem($items) {
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
  function removeItem($pid, array $item){
    $manager = $this->getDocumentHandler();
    $manager ->removeDocument($item['data_entity_id']);
  }


  /**
   * Implements an PDF
   *
   * @param array $page
   * @param \PDF $pdf
   */
  function getOutputPDF($page, LK_PDF $pdf) {

    $manager = $this->getDocumentHandler();
    $document = $manager->getDocumentMitarbeiter($page["data_entity_id"]);
 
    $processor = new ExportPDFProcessor($document);
    $processor ->processPDF($pdf);
  }

  /**
   * Gets back the PPT-Implementation
   *
   * @param array $page
   * @param type $ppt
   */
  function getOutputPPT($page, LK_PPT_Creator $ppt) {
    $manager = $this->getDocumentHandler();
    $document = $manager->getDocumentMitarbeiter($page["data_entity_id"]);

    $processor = new ExportPPTProcessor($document);
    $processor->renderPPT($ppt);
  }
}
