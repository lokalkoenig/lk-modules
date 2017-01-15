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
        $this -> solr -> addParam('fq','index_id:default_node_index');
        $this -> solr -> addParam('fl','item_id');
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
        $this -> term = $term;
        $this -> solr -> addParam('qf','tm_field_kamp_suche^1.0');
        $this -> solr -> addParam('q','"'. $term .'"');
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
          $this -> setSort($query['sort_by']);
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
     * Excludes a NID
     * 
     * @param type $nid
     */
    function excludeNode($nid){
       $this ->solr ->addParam('fq', '-item_id:"'. $nid .'"');
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
         if(isset($doc-> entity_id)){
           $nodes[] = $doc-> entity_id;
         }  
         elseif(isset($doc-> item_id)) {
           $nodes[] = $doc-> item_id;
         }
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
      
      
      //utf8_encode($this -> term)
      $resp = $this -> solr->search(); 
       
      /**
       * SAMPLE Query
       * webapp=/solr path=/select params={facet.missing=false
       * &f.im_field_kamp_anlass.facet.limit=50
       * &facet=true
       * &sort=score+desc&
       * facet.mincount=1&
       * facet.limit=10
       * &qf=tm_field_kamp_suche^1.0&f.is_field_kamp_preisnivau.facet.limit=50&f.im_field_kamp_format.facet.limit=50&f.im_field_kamp_kommunikationsziel.facet.limit=50&json.nl=map&wt=json&rows=10&fl=item_id,score&start=0&facet.sort=count&q="sommer"&facet.field=im_field_kamp_kommunikationsziel&facet.field=is_field_kamp_preisnivau&facet.field=im_field_kamp_format&facet.field=im_field_kamp_anlass&facet.field=im_field_kamp_themenbereiche&f.im_field_kamp_themenbereiche.facet.limit=50&fq=*:*+AND+-(is_author:"11")&fq=index_id:default_node_index} hits=32 status=0 QTime=13
       */
      
      return $resp -> response;
    }
}