<?php

namespace LK\Solr;
use Solarium\Client as SOLRClient;

/**
 * EXAMPLE USAGE
 * $query = new \LK\Solr\Search();
 * $query -> setLimit(5);
 * $query->setSearchTerm('Test');
 */

/**
 * Description of KampagnenSearch
 *
 * @author Maikito
 */
class Search {
    
  private $client = null;
  private $query = null;
  private $debug = FALSE;

  /**
   * Constructor of the class
   */
  function __construct() {
   
    // Loads the Server from the DRUPAL-SEARCH-API
    $server = search_api_server_load(1);
    $config = [
      'endpoint' => [
          'localhost' => [
              'host' => $server->options['host'],
              'port' => $server->options['port'],
              'path' => $server->options['path'],
          ]
      ]
    ];

    $this -> client = new SOLRClient($config);
    $this -> query = $this -> client->createQuery(\Solarium\Client::QUERY_SELECT);
    $this -> query -> setFields(['item_id']);
    $this ->addParam('fq', 'index_id:default_node_index');
  }



  /**
   * Enables Debug
   */
  function enableDebug(){
    $this->debug=TRUE;
  }

    private function addParam($key, $val){
      $this -> query -> addParam($key, $val);
    }

    /**
     * Sets the limit for the Results
     * 
     * @param Int $limit
     */
    public function setLimit($limit){
        $this -> addParam('rows', $limit);
    }
 
    /**
     * Sets the search term
     * 
     * @param String $term
     */
    public function setSearchTerm($term){
      $this -> term = $term;
      $this -> addParam('qf','tm_field_kamp_suche^1.0');
      $this -> addParam('q','"'. $term .'"');
    }

    
    function addFacet($f){
       $this -> query-> addFilterQuery(array('key' => md5($f), 'query'=>'im_' . $f));
    }

    /**
     * Adds an query from the normal Search results pages
     * 
     * @param Array $query
     */
    public function addFromQuery($query){
        
       if(isset($query['search_api_views_fulltext'])):
         $this -> setSearchTerm($query['search_api_views_fulltext']);
       endif;  
        
       if(isset($query['f'])){
          foreach ($query['f'] as $f):
            $this ->addFacet($f);
          endforeach;
      }        
              
      if(isset($query['sort_by'])):
          $this -> setSort($query['sort_by']);
      endif;
    }
    
    /**
     * Adds an optional Timestamp 
     * as a parameter for the Alert search function
     */
    function addTimestamp($timestamp){
       $date = date('c', $timestamp);
       $this -> query-> addFilterQuery(array('key'=>'time', 'query' => "ds_created:[". $date ."Z TO NOW]"));
    }
    
    /**
     * Excludes a NID
     * 
     * @param type $nid
     */
    function excludeNode($nid){
      $this -> query -> addFilterQuery(array('key'=>'exclude-' . $nid, 'query' => '-item_id:"'. $nid  .'"'));
    }
    
    /**
     * Gets the number of results
     * 
     * @return Int
     */
    public function getCount(){
      $response = $this->callSOLR();
      return $response['response']['numFound'];
    }


    public function autocompleteKeyword($term){

      $params = [
        'spellcheck' => 'true',
        'facet' => 'true',
        'facet.mincount' => 1,
        'start' => 0,
        'facet.prefix' => strtolower($term),
        'facet.limit' => 10,
        'spellcheck.q' => '',
        'qf' => 'tm_field_kamp_suche^40',
        'facet.field' => 'spell',
        'wt' => 'json',
        'json.nl' => 'map',
        'rows' => 0,
      ];
  
      while(list($key, $val) = each($params)){
        $this -> addParam($key, $val);
      }

      $response = $this->callSOLR();

      $results = [];
      $items = $response['facet_counts']['facet_fields']['spell'];
      while(list($key, $val) = each($items)){
        $search = new \LK\Solr\Search();
        $search ->setSearchTerm($key);
        $count = $search ->getCount();
        $results[$key] = $count;
      }

      // Sort for count
      arsort($results);
      return $results;
    }
    
    /**
     * Gets the number of nodes 
     * corresponding to the search
     * 
     * @return Array
     */
    public function getNodes(){
      // get the Response
      $response = $this -> callSOLR();

      $nodes = array();
      foreach ($response['response']['docs'] as $doc):
        $nodes[] = $doc['item_id'];
      endforeach;

      return $nodes;
    }
    
    
    /**
     * Gets back a Result
     * 
     * @param type $nid
     * @param type $count
     * @return array Nodes
     */
    public function moreLikeThis($nid, $count = 5){
      
      $this -> addParam('mlt.minwl', "3");
      $this -> addParam('mlt.fl', "tm_field_kamp_suche");
      $this -> addParam('qt', "mlt");
      $this -> addParam('mlt.boost', true);
      $this -> addParam('q', 'item_id:"'. $nid .'"');
      $this -> setLimit($count);
      
    return $this->getNodes();  
    }
    
    /**
     * Adds an Sort parameter
     * 
     * @param String $name
     * @param String $dir
     */
    public function setSort($name, $dir = 'DESC'){
        
      $sn = $name;
      
      $translate = [
          'field_kamp_beliebtheit' => 'is_field_kamp_beliebtheit',
          'search_api_relevance' => 'score',
          'created' => 'ds_created'
      ];
        
      if(isset($translate[$name])):
        $sn = $translate[$name];
      endif;

      $this -> query->addSort($sn, $dir);
    }

    /**
     * Make a call to SOLR
     *
     * @return array
     */
  private function callSOLR(){
    $response = $this -> client-> execute($this -> query);
    $data = $response -> getData();

    if($this->debug || isset($_GET['debug_solr'])){
      dpm($data);
    }
   
    return $data;
  }
}
