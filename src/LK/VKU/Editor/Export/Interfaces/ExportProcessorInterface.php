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
   * Loads Markup and gives back the parseAble Instance
   *
   * @param string $markup
   * @return \stdObject
   */
  final public function loadMarkup($markup){
    $instance = new \simple_html_dom();
    $instance->load('<html><body>'. $markup .'</body></html>');
    $body = $instance -> find('body');

    return $body[0];
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


  /**
   * Gets the HTML for the Table-Cell
   *
   * @param string $html
   * @return string
   */
  final function getTableMarkupSpripped($html){

    $html = str_replace('<p><br></p>', "&nbsp;<br>", $html);
    $html = str_replace('</p><p>', "</p><br><p>", $html);
    $html = strip_tags($html, "<br><b><strong><u><i><em>");

    return $html;
  }

}
