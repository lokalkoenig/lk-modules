<?php


/** Block Informationen */
function lokalkoenig_search_block_info(){
 $blocks = array();

 $blocks['lk_search_block'] = array(
    // info: The name of the block.
    'info' => 'Block: LK-Suche Sortierung',
    // Block caching options (per role, per user, etc.)
  ); #

  $blocks['lk_search_other'] = array(
    // info: The name of the block.
    'info' => 'Block: LK-Suche Andere Suche',
    // Block caching options (per role, per user, etc.)
  ); #

  $blocks['lk_search_empty'] = array(
    // info: The name of the block.
    'info' => 'Block: LK-Suche Keine Ergebnisse',
    // Block caching options (per role, per user, etc.)
  ); #

    

return $blocks;
}



/** Block Ausführung */
function lokalkoenig_search_block_view($delta = NULL){
  $block = array();
  
  if(in_array(arg(0), array('suche','suche-grid','empfehlungen'))){ 
    switch($delta){ 
      case 'lk_search_block':
        
        // Do not show for Agentur
        if(lk_is_agentur()){
          return array();
        }
        
        // Add to Database
        new \LK\Solr\UserSearch();
        
         // Do not show when no results
        if(lk_search_has_no_results()){
          return [];
        }
        
        $block = array(
          'subject' => 'Blubb',
          'content' => theme('lk_search', array()));
        
        return $block;
        break;
        
      case 'lk_search_empty':
          
        if(!lk_search_has_no_results()) {
            return array();
        }
        
        if(isset($_GET["search_api_views_fulltext"])){
           $searchterm = $_GET["search_api_views_fulltext"];
        }
        
      $vars = array('!url' => url(current_path()));
      $text = t(variable_get('lk_search_noresult_text', ''), $vars); //lk_search_noresult_text
      
      include(drupal_get_path('module', "lokalkoenig_user") .'/pages/suchanfrage.inc');
      
      if(lk_is_agentur())  {
         $block = array(
            'subject' => '<none>',
            'content' => theme('lk_search_empty', array('text' => $text, 
                                                    'terms' => $searchterm)));
        return $block;  
      }
      
      $block = array(
        'subject' => '<none>',
        'content' => theme('lk_search_empty', array('text' => $text, 
                                                    'terms' => $searchterm,
                                                    'form' => drupal_get_form('_lk_suchanfrage_form', $searchterm))));
        break;
      
        
      case 'lk_search_other':
        $block = array(
          'subject' => 'Related',
          'content' => _lk_generate_search_related_block());
        break;
    }
  }

  return $block;
}


/**
 * 
 * @todo Disables, as we need a replacement
 * @global type $user
 * @return boolean
 */
function _lk_generate_search_related_block(){

  return ;

  if(lk_is_agentur()) {
    return ;
  } 
  
  $view = views_get_page_view();
  if(!$view){
    return ;
  }
  
  $url = $_GET;
  if(isset($url["q"])) {
    unset($url["q"]);
  }        
  
  if(!isset($url["f"]) || count($_GET["f"]) < 2) {
    return ;
  }
  
  // shuffle
  $vars = $url["f"];
  shuffle($vars);
  unset($vars[0]);
  
  $url["f"] = $vars;
  
  $search = new \LK\Solr\Search();
  $search ->addFromQuery($url);

  
  $nodes_exclude = array();
  foreach($view -> result as $entity){
     if(is_int($entity -> entity)){
       $search ->excludeNode($entity -> entity); 
     }
     else {
      $search ->excludeNode($entity -> entity -> nid);
     } 
  }
  
  $search ->setLimit(4);
 
  // Generate the Tags    
  $tags = array();
  $tags_display = array();
  while(list($key, $val) = each($vars)){
     $explode  = explode(":", $val);
     if(isset($explode[1])){
        if($term = taxonomy_term_load($explode[1])){
          $tags[] = $explode[1];
          $tags_display[] = l($term -> name, arg(0), array("query" => $url, "attributes" => array("class" => array("btn")))); 
        }
     }
  }

  $nodes = $search ->getNodes();
  
  $output = [];
  foreach($nodes as $nid){
    $node = node_load($nid);
    if(!$node){
      continue;
    }
    
    $node_view = node_view($node, 'teaser');
    $output[] = render($node_view);
  }
  
  if(!$output){
    return false;
  }
 
  $theme = array();
  $theme["total_items"] = $search ->getCount();
  $theme["tags_display"] = $tags_display;
  $theme["viewsout"] = implode('', $output);
  $theme["url"] = \LK\Solr\SearchQueryParser::buildLink($url);

  return theme('lk_search_other', $theme);
}

