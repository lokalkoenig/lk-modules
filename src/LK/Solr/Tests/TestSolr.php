<?php

namespace LK\Solr\Tests;

use LK\Tests\TestCase;
use LK\Solr\Search;
/**
 * Description of TestSolr
 *
 * @author Maikito
 */
class TestSolr extends TestCase {
  //put your code here

  var $term = 'Sommer'; 

  /**
   * Constructor
   * 
   * @param array $params
   */
  function __construct($params) {
    if(isset($_GET["term"])){
      $this -> term = $_GET["term"];
    }
  }

  /**
   * Passes form elements
   *
   * @return array
   */
  function _getForm(){

    $form['term'] = [
      '#title' => 'Suchwort',
      '#type' => 'textfield',
      '#default_value' => $this->term,
    ];

    return $form;
  }

  /**
   * Build the Test-Output
   */
  function build() {
    $term = $this -> term;
    $search = new Search();
    $search->enableDebug();

    //$search -> setSearchTerm($term);
    //$this -> printLine('Suche nach', $term . " GET param term");

    $search -> addFacet('field_kamp_themenbereiche:3995');
    $search -> addFacet('field_kamp_anlass:8368');
    //$search ->setSearchTerm("Sommer");
    $count = $search ->getCount();
    $this -> printLine('Kampagnen', $count);

    $date = time() - 60 * 60 * 24 * 365 * 2;
    $this -> printLine('', "Aktuelle Suche und Datum (". format_date($date) .")");
    $search ->addTimestamp($date);

    $count2 = $search -> getCount();
    $nodes = $search -> getNodes();
    $this -> printLine('Kampagnen', $count2);
    
    foreach ($nodes as $nid){
      $this -> printLine('Kampagne', \LK\UI\Kampagne\Picture::get($nid, ['height' => 50, 'width' => 50]));
    }
  }
}
