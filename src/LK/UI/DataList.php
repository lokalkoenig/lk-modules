<?php
namespace LK\UI;

/**
 * Description of DataList
 *
 * @author Maikito
 */
class DataList {

  /**
   * Renders a DL-List
   *
   * @param array $array
   * @return string
   */
  public static function render($array){
    $html = '<dl class="dl-horizontal">';
    while(list($key, $val) = each($array)){
      $html .= '<dt>' . $key . '</dt>';
      $html .= '<dd>' . $val . '</dd>';
    }
    $html .= '</dl>';
    return $html;
  }
}
