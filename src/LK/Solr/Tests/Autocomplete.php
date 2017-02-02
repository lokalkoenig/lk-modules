<?php
namespace LK\Solr\Tests;
use LK\Tests\TestCase;

/**
 * Description of MLTTest
 *
 * @author Maikito
 */
class Autocomplete extends TestCase {

  var $search_param = 'Mutter';

  function __construct($params) {
    if(isset($params['keys'])){
      $this->search_param = $params['keys'];
    }
  }

  /**
   * Passes form elements
   *
   * @return array
   */
  function getForm(){

    $form['keys'] = [
      '#title' => 'Suchwort',
      '#type' => 'textfield',
      '#default_value' => $this->search_param,
    ];

    $checked = FALSE;
    if(isset($_GET['solr_debug']) && $_GET['solr_debug']){
      $checked = TRUE;
    }

    $form['solr_debug'] = [
      '#title' => 'Debug SOLR',
      '#type' => 'checkbox',
      '#default_value' => $checked,
    ];

    return $form;
  }

  /**
   * Builds the Test-Case
   */
  function build(){
    $this->printLine('Search: ' . $this->search_param, 'See Output');

    $search = new \LK\Solr\Search();
    $response = $search ->autocompleteKeyword($this->search_param);

    while(list($key, $val) = each($response)){
      $this->printLine($val['keyword'], $val['count']);
    }    
  }
}
