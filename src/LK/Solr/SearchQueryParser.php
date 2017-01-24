<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Solr;

/**
 * Gets back a saveable SearchQuery from the SOLR-Search
 * 
 */
class SearchQueryParser {

    public static function get(){
        
      $search = array();
      if(isset($_GET["f"])){
         $search['f'] = $_GET["f"]; 
      }
      
      if(isset($_GET["search_api_views_fulltext"]) AND !empty($_GET["search_api_views_fulltext"])){
          $search['search_api_views_fulltext'] = $_GET["search_api_views_fulltext"]; 
      }
      
      if(isset($_GET["sort_by"]) AND !empty($_GET["sort_by"])){
          $search['sort_by'] = $_GET["sort_by"]; 
      }
      
       if(isset($_GET["sticky"]) AND !empty($_GET["sticky"])){
          $search['sticky'] = $_GET["sticky"]; 
      }
      
      return $search;          
    }   
    
      
    /**
     * Gets a Label back for the current Search
     * 
     * @param Array $array
     * @return String
     */
    static function toLabel($array){
        
        $html =  trim(strip_tags(self::toMarkup($array)));
        $explode = explode("\n", $html);
        return implode(', ', $explode);
    }
    
    /**
     * Gets back a Markup for the given Search
     * 
     * @param Array $search
     * @return string
     */
    static function toMarkup($search){
       $html = ' <ul class="list-inline">';
       
       // Search word
       if(isset($search["search_api_views_fulltext"])):
           $html .= '<li><u>Suchwort:</u> ' . ucfirst($search["search_api_views_fulltext"]) . '</li>' . "\n";
       endif;
       
       // Terms
       $terms = self::getTerms($search);
       if($terms):
          $html .= '<li><u>Kategorien:</u> '. implode(', ', $terms) .'</li>' . "\n"; 
       endif;
       
       if(isset($search["sort_by"])):
           $html .= '<li><u>Sortiert nach:</u> ';
           $html .= self::getSortLabel($search); 
           $html .= '</li>'. "\n";
       endif;
       
       $html .= '</ul>';
    return $html;   
    }
    
   
    static function getSortLabel($search){
        
        if(!isset($search["sort_by"])):
            return '';
        endif;
        
         switch($search["sort_by"]){
                case 'search_api_relevance':
                    return 'Relevanz';  
                case 'created':
                    return 'Neueste Kampagnen';  
                default:
                    return 'Beliebtheit';
        } 
    }
    
    /**
     * Get the Term names from the Search array
     * 
     * @param Array $search
     * @return Array
     */
    static function getTerms($search){
        $arr = array();
    
        if(isset($search["f"])): 
           foreach($search["f"] as $string){
                $explode = explode(":", $string);
                $term = taxonomy_term_load(trim($explode[1]));
                if($term){
                    $arr[] = $term -> name;
                }
            }  
        endif;
        
    return $arr;    
    }
    
    
    static function buildLink($search){
        return url('suche', array("query" => $search));
    }
}