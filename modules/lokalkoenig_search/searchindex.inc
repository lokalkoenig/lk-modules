<?php

/*

CREATE TABLE `lk_searchindex` (
  `id` int(11) NOT NULL AUTO_INCREMENT, 
  `word` varchar(255) NOT NULL DEFAULT '',
  `count` int(11) DEFAULT NULL,
  `created` int(11) DEFAULT NULL,
  `updated` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`word`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8

*/

// Searchindex Files

function search_index_log($log){
   // dpm($log);
}

function si_get_fast_count_add($word, $count = 0){

  $word = strtolower($word);
  
  $dbq = db_query("SELECT count FROM lk_searchindex WHERE word='". $word ."'");
  $res = $dbq -> fetchObject();
  
  if($res){
    return $res -> count;
  }
  
  return searchindex_add($word);
}



function searchindex_add($string){

  $string = trim($string);
  $string = strtolower($string);
  
  $string = str_replace("ue", "ü", $string);
  $string = str_replace("ae", "ä", $string);
  $string = str_replace("oe", "ö", $string);
  
  
  if(strlen($string) < 4) return 0;
  
  //$explode = explode(" ", $string);
  
  
  

  $dbq = db_query("SELECT * FROM lk_searchindex WHERE word='". $string ."'");
  $result = $dbq -> fetchObject();
                            
  $count = _searchindex_results($string);
  
  if($count == 0){
      if($result){
        db_query("delete FROM lk_searchindex WHERE id='". $result -> id ."'");
      }
      
      return $count;    
  }
  
  if($result){
      if($result -> count == $count) return ;
      db_query("UPDATE lk_searchindex SET updated='". time() ."', count='".  $count."' WHERE id='". $result -> id ."'");
  }
  else {
    db_query("INSERT INTO lk_searchindex SET created='". time() ."', updated='0', count='".  $count."', word='". $string ."'");
    search_index_log("Add " . $string . " [count: ". $count ."]");
  }
    
  return $count;  
}


function _searchindex_results($word){
  $view = views_get_view('suchev2');
  $view->set_display('page_2');
  
  $search = array('search_api_views_fulltext' => $word);
  
  while(list($key, $val) = each($search)){
    $view -> exposed_input[$key] = $val;
  }
  
  $view->set_items_per_page(1);
  $view->execute();              //
  $response = $view->result;
  
  return $view->total_rows; 
}

function search_index_sources(){
  
   // Reindex
  $dbq = db_query("SELECT word FROM lk_searchindex ORDER BY RAND() LIMIT 100");
  foreach($dbq as $all){
    searchindex_add($all -> word);  
  }
  
  
  return ;
  
  $dbq = db_query("SELECT word FROM search_total ORDER BY RAND() LIMIT 100");
  foreach($dbq as $all){
    searchindex_add($all -> word);  
  }
  
  $dbq = db_query("SELECT keywords FROM search_api_stats ORDER BY RAND() LIMIT 100");
  foreach($dbq as $all){
    searchindex_add($all -> word);  
  }
  
  
  // Indexe
  $dbq = db_query("select name, description  from `taxonomy_term_data` WHERE vid='3' ORDER BY RAND() LIMIT 50");
  foreach($dbq as $all){
    searchindex_add($all -> name);
    
    if($all -> description){
      $explode = explode(" ", $all -> description);
      foreach($explode as $item){
         searchindex_add($item);
      }
    }
  }
  
  
 
  
  $dbq = db_query("SELECT word FROM lk_searchindex ORDER BY RAND() LIMIT 30");
  foreach($dbq as $all){
    
    
    searchindex_add_synonmes($all -> word);  
  }
   

}

function searchindex_add_synonmes($word){
  $string = file_get_contents("http://www.lokalkoenig.de/apachesolr_autocomplete2?query=". urlencode($word) ."&limit=50&_=" . time());
  $bla = json_decode($string);

  search_index_log("Base " . $word);
   foreach($bla as $obj){
       search_index_log("Syn " . $obj -> key); 
       searchindex_add($obj -> key);  
   }
}


function _searchindex(){

  
  $dbq = db_query("SELECT count(*) as count FROM lk_searchindex");
  $count = $dbq -> fetchObject();
 

  //search_index_sources();

  //$dbq = db_query("SELECT count(*) as count FROM lk_searchindex");
  //$count2 = $dbq -> fetchObject();

return $count -> count . " INDEXE";
}


?>