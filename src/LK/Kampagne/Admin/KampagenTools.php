<?php

namespace LK\Kampagne\Admin;

use \LK\Kampagne\Kampagne;

/**
 * Description of KampagenTools
 *
 * @author Maikito
 */
class KampagenTools {
  
  protected $kampagne;
  
  /**
   * Constructs an administrative Object
   * 
   * @param \stdClass $node
   * @throws \Exception
   */
  public function __construct(\stdClass $node) {
    if($node->type === 'kampagne') {
      $this->kampagne = new Kampagne($node);
    }
    else {
      throw new \Exception('The Current Node is not a Kampagne');
    }
  }

  /**
   * Gets a Kampagne
   *
   * @return \LK\Kampagne\Kampagne
   */
  public function getKampagne(){

    return $this->kampagne;
  }

  /**
   * Gets the Kampagnen-Branchen
   * lines
   *
   * @return array
   */
  public function getKampagnenBranchen() {
    $node = $this->getKampagne()->getNode();
    $branchen = [];

    if(isset($node->field_kamp_themenbereiche['und'])) {
      $branchen = $node->field_kamp_themenbereiche['und'];
    }
    
    $config = [
      'vid' => 3,
      'exclude_tid' => NULL,
      'root_term' => 0,
      'entity_count_for_node_type' => NULL,
    ];
    
    $values = [];
    $selection = array();
    foreach($branchen as $tax){
      $term = \taxonomy_term_load($tax["tid"]);
      $selection[] = $tax["tid"];  
      $values[] = $term -> name . ' [tid:'. $term -> tid  .']';
    }
    
    $lines = _hierarchical_select_dropbox_reconstruct_lineages_save_lineage_enabled('hs_taxonomy', $selection, $config);
    
    return $lines; 
  }

  /**
   * Gets the Terms of the deepest Branchen children
   *
   * @return array
   */
  public function getSmallestChildBranchenTerms() {
    $lines = $this->getKampagnenBranchen();

    $terms = [];
    foreach($lines as $line) {
      $last = end($line);
      $terms[$last['value']] = $last['label'];
    }

    return $terms;
  }
}
