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
    $html = $this->removeTrailingBR($html);
    
    return $html;
  }

  abstract function getMargins();
  abstract function getWidth();
  abstract function getHeight();
  abstract function getTopMargin();
  abstract function getTextSeperator();
  abstract function getOffsetY();


  final protected function processContent() {

    $left_y = $left_margin = $this->getMargins();
    $top_x = $top_run_region = $this->getTopMargin();
    $text_sep = $this->getTextSeperator();
    $height_100 = $this->getHeight();
    $width_100 = $this->getWidth() - ($left_y * 2);
    $width_100_with_relative = $width_100;

    $content = $this->getDocument()->getContent();
    $defintion = $this->getLayoutDefinition();
    $width_100 -= ($defintion['regions'] - 1) * ($text_sep);

    $values_top = [
      'calc' => 0,
      0 => $top_x,
      50 => $top_x + ($height_100 / 2) + $text_sep,
    ];

    $values_width = [
      'full' => $width_100_with_relative,
      100 => $width_100,
      50 => $width_100/2,
      33 => $width_100/3,
    ];

    $values_left = [
      0 => $left_y,
      33 => $left_y + $values_width[33] + $text_sep,
      50 => $left_y + $values_width[50] + $text_sep,
      66 => $left_y + ($values_width[33] * 2) + $text_sep * 2,
    ];

    $values_height = [
      'calc' => 0,
      'auto' => $height_100,
      0 => 0,
      50 => $height_100 / 2 - ($text_sep / 2),
      100 => $height_100,
    ];

    $calc_last_height = 0;

    while(list($key, $val) = each($defintion['fields'])){
      
      if(isset($val['skip'])) {
        
        continue;
      }

      $run_y = $values_top[$val['top']];
      $run_width = $values_width[$val['width']];
      $run_height = $values_height[$val['height']];
      $run_x = $values_left[$val['left']];

      // change the Top & the remaining-height value
      if($val['height'] === 'calc' && $val['top'] === 'calc') {
        $run_y = $top_x + $calc_last_height + $text_sep;
        $run_height = $height_100 - $top_x - $calc_last_height - $text_sep / 2;
      }

      $field = $content[$key];
      $this->addContent($field, $run_x, $run_y, $run_width, $run_height, $width_100);

      // Save value for further use
      if($val['height'] === 'auto') {
        $calc_last_height = $this->getOffsetY();
      }
    }
  }

  abstract function addContent($field, $left_run, $top_run, $content_width_calc, $content_height, $base_width);

}
