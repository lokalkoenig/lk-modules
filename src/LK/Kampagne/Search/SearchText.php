<?php

namespace LK\Kampagne\Search;

/**
 * Description of SearchText
 *
 * @author Maikito
 */
class SearchText extends \LK\Kampagne\Admin\KampagenTools {

  function __construct(\stdClass $node) {
    parent::__construct($node);
  }

  /**
  * Gets the Searchable String for SOLR
  *
  * @return String
  */
  function getSearchString(){

    $node = $this ->getKampagne()->getNode();
    $content = array();
    $content[] = $node -> title;
    $content[] = $node -> field_sid['und'][0]['value'];
    $content[] = $node -> field_kamp_untertitel['und'][0]['value'];
    $content[] = $node -> field_kamp_teasertext['und'][0]['value'];
    
    $terms = $this->getSmallestChildBranchenTerms();
    foreach($terms as $term) {
      $content[] = $term;
    }

    if(isset($node->field_kamp_anlass['und'])){
      foreach($node->field_kamp_anlass['und'] as $tax){
        $term = taxonomy_term_load($tax["tid"]);
        $content[] = $term -> name;
        $content[] = $term -> description;
      }
    }

    if(isset($node->field_kamp_kommunikationsziel['und'])){
      foreach($node->field_kamp_kommunikationsziel['und'] as $tax){
        $term = taxonomy_term_load($tax["tid"]);
        $content[] = $term -> name;
      }
    }

    if(isset($node -> medien)){
      foreach($node -> medien as $m){
        $content[] = $m -> title;

        if(isset($m->field_medium_beschreibung['und'][0]['value'])){
          $content[] = $m->field_medium_beschreibung['und'][0]['value'];
        }
      }
    }

    return implode("\n", $content);
   }
}
