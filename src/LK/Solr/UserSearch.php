<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Solr;

/**
 * Description of UserSearch
 *
 * @author Maikito
 */
class UserSearch {
  
  use \LK\Stats\Action;
  
  function __construct() {
    $search = \LK\Solr\SearchQueryParser::get();
    
    if(!isset($search['search_api_views_fulltext'])) {
      return ;
    }
    
    $this->logCurrentSearch($search);
  }
  
  protected function logCurrentSearch($search){
    $current = \LK\current();
    $word = $search['search_api_views_fulltext'];
    $today = date('Ymd');
    $key = $today . $word;
    
    if(isset($_SESSION["lksearch"][$key])){
      return ;
    }
    
    $insert = [];
    $insert["uid"] = $current->getUid();
    $insert["search_string"] = $word;
    $insert["created"] = time();
    $insert["search_text"] = serialize($search);
    $insert["search_count"] = $GLOBALS['pager_total_items'][0];

    \LK\Stats::logUserSearches($current->getUid());

    $id = \db_insert('lk_search_history')->fields($insert)->execute();            
    $_SESSION["lksearch"][$key] = $id;
    
    $this->setAction('search', $id);
  }
}
