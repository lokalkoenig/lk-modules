<?php

namespace LK\UI\Kampagne;

/**
 * Description of Picture
 *
 * @author Maikito
 */
class Picture {
  
  /**
   * Gets back the linked Picture
   * of a node
   * 
   * @param type $nid
   */
  public static function get($nid, $attributes = []){
    $node = \node_load($nid);
    $image = \image_style_url('kampagnen_uebersicht', $node->field_kamp_teaserbild['und'][0]['uri']);
    return l('<img src="' .$image . '" title="'. $node -> title .'" '. \drupal_attributes($attributes) .'/>', 'node/' . $nid, array('html' => true));
  } 
}
