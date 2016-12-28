<?php

namespace LK\Solr;

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
    
    private $solr = null;
    private $term = null;
    
    /**
     * Constructor of the class
     */
    function __construct() {
        $this -> solr = apachesolr_drupal_query("select");
        //$this -> solr -> addParam('fq','index_id:default_node_index');
    }   
    
    /**
     * Sets the limit for the Results
     * 
     * @param Int $limit
     */
    public function setLimit($limit){
        $this -> solr -> addParam('rows', $limit);
    }
    
    public static function escape($value, $version = 0) {
        $replacements = array();

        $specials = array('+', '-', '&&', '||', '!', '(', ')', '{', '}', '[', ']', '^', '"', '~', '*', '?', ':', "\\");
        // Solr 4.x introduces regular expressions, making the slash also a special
        // character.
        if ($version >= 4) {
          $specials[] = '/';
        }

        foreach ($specials as $special) {
          $replacements[$special] = "\\$special";
        }

    return strtr($value, $replacements);
    }
    
    
    /**
     * Sets the search term
     * 
     * @param String $term
     */
    public function setSearchTerm($term){
       //$sanitized = \InterNations\Component\Solr\Util::sanitize($term); 
       //$this -> solr ->addParam('q', '"' . $term .  '"');  
        $this -> term = $term;
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
              $this -> solr -> addParam('fq','im_' . $f);
          endforeach;
      }        
              
      if(isset($query['sort_by'])):
          $this -> setSort($this -> query['sort_by']);
      endif;
    }
    
    /**
     * Adds an optional Timestamp 
     * as a parameter for the Alert search function
     */
    function addTimestamp($timestamp){
       $date = date('c', $timestamp);
       $this ->solr ->addParam('fq', "ds_created:[". $date ."Z TO NOW]");
    }
    
    
    /**
     * Gets the number of results
     * 
     * @return Int
     */
    public function getCount(){
        $response = $this->callSOLR();
        return $response -> numFound;
    }
    
    
    /**
     * Gets the number of nodes 
     * corresponding to the search
     * 
     * @return Array
     */
    public function getNodes(){
       $response = $this -> callSOLR();  
       $nodes = array();
       foreach ($response->docs as $doc):
           $nodes[] = $doc-> entity_id;
       endforeach;
    return $nodes;   
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
        
        $this -> solr->setSolrsort($sn, $dir);
    }
    
   /**
    * Calls the SOLR
    * 
    * @return stdClass
    */ 
   protected function callSOLR(){
      //$query->addFilter('bundle', (article OR page));
      //$query->removeFilter('bundle');
      //$query->addParam('fq', "bundle:(article OR page)");
      //$query->addParam('fq', "field_date:[1970-12-31T23:59:59Z TO NOW]");
       
      $resp = $this -> solr->search(utf8_encode($this -> term)); 
      //dpm($resp); 
       
      return $resp -> response;
    }
}