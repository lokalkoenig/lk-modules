<?php
namespace LK\UI;

/**
 * Description of Tabs
 *
 * @author Maikito
 */
class Tabs {

  /**
   * Renders a Tab-Navigation
   *
   * @param array $array
   * @return string
   */
  static public function render($array){
    $markup = '<ul class="nav nav-tabs" role="tablist">';
    foreach($array as $item){
      if(isset($item['active']) && $item['active']){
        $markup .= '<li role="presentation" class="active"><a href="'. $item['url'] .'" role="tab">'. $item['title'] .'</a></li>';
      }
      else {
        $markup .= '<li role="presentation"><a href="'. $item['url'] .'" role="tab">'. $item['title'] .'</a></li>';
      }
    }

    $markup .= '</ul>';
    return $markup;
  }
}
