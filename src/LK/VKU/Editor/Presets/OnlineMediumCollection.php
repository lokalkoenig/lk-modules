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

  function performContentUpdate($content, array $medium_content, $id, $position){

    $defaults = $this -> getDefaultValues();

    if(!$content){
      $content = $defaults-> content;
    }

    // On Reset
    if(!$medium_content){
      $content[$position]['value'] = $defaults -> content[0];
      $content[$position + 1] = $defaults -> content[1];
      $content[$position + 2] = $defaults -> content[2]; 
   }
    else {
      $content[$position]['value'] = $id;
      $content[$position + 1] = $medium_content[0];
      $content[$position + 1]['id'] = $position;
      $content[$position + 2] = $medium_content[1];
      $content[$position + 1]['id'] = $position + 1;
    }
  
    return $content;
  }

  function getOnlineMediumDetails($id){
    $manager = $this->getManager();
    $document = $manager->getDocumentVerlag($manager->getAccount(), $id);

    if(!$document){
      
      $content = [];
      $content[] = [
        'id' => 0,
        'widget' => 'online_medium_chooser',
        'value' => 0,
      ];

      $content[] = [
        'id' => 1,
        'widget' => 'image',
        'fid' => 0
      ];

      $content[] = [
        'id' => 2,
        'widget' => 'editor',
        'value' => 'ssa'
      ];

      return $content;
    }

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
