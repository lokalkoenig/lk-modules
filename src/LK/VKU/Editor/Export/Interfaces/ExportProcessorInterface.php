<?php

namespace LK\VKU\Editor\Export\Interfaces;
use \LK\VKU\Editor\Document;

/**
 * Description of ExportProcessorInterface
 *
 * @author Maikito
 */
abstract class ExportProcessorInterface {
  
  var $document = null;

  function __construct(Document $document) {
    $this->document=$document;
  }

  /**
   * Gets the Document
   *
   * @return \LK\VKU\Editor\Document
   */
  final protected function getDocument(){
    return $this->document;
  }

   /**
   * Removes ending BR
   *
   * @param string $string
   * @return string
   */
  final protected function removeTrailingBR($string){
    return preg_replace('/(<br>)+$/', "", $string);
  }

  /**
   * Gets the Layout Definition
   *
   * @return array
   */
  final protected function getLayoutDefinition(){
    $document = $this->getDocument();
    $layout = $document->getLayout();
    $manager = new \LK\VKU\Editor\Manager();
    $obj = $manager->getLayout($layout);

    return $obj -> getDefinition();
  }


}
