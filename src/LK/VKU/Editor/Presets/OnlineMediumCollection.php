<?php
namespace LK\VKU\Editor\Presets;

/**
 * Description of OnlineMediumCollection
 *
 * @author Maikito
 */
class OnlineMediumCollection extends \LK\PXEdit\Presets\OnlineMediumCollection {


  /**
   * Returns the Manager
   *
   * @return \LK\VKU\Editor\Manager
   */
  function getManager(){
    return parent::getManager();
  }

  function getOnlineMediumDetails($id){
    $manager = $this->getManager();
    $document = $manager->getDocumentVerlag($manager->getAccount(), $id);
    $content = $document->getContent();
   
    return $content;
  }


  function getOnlineMediumOptions(){

    $manager = $this->getManager();
    $documents = $manager -> getDocumentsPerVerlagPreset('OnlineMedium', 1);

    $options = [];
    foreach ($documents as $document){
      $options[$document['id']] = $document['document_title'];
    }

    return $options;
  }
}
