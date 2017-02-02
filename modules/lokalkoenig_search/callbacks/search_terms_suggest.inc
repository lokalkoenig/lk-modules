<?php

use LK\Solr\Search as SolrSearch;

/**
 * Callback for Suche
 * @path /search/callback
 */
function lokalkoenig_search_ajax_cb(){

  $search = new SolrSearch();
  $response = $search ->autocompleteKeyword($_GET['term']);
  
  $items = [];
  while(list($key, $val) = each($response)){
    $items[] = ['keyword' => $key, 'count' => $val];
  }

  drupal_json_output($items);
  exit;
}
