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
      
        // Add to Database
        lk_save_current_search();
      
        $block = array(
          'subject' => 'Blubb',
          'content' => theme('lk_search', array()));
        
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


function _lk_generate_search_related_block(){
global $user;
  
  if(lk_is_agentur()) return ;
   
  $view = views_get_page_view();
  
  //dpm($view);

 

  // 
  if(!isset($_GET["f"])) return ;
  
  // Nur anzeigen wenn zwei Facetts ausgewählt wurden:
  if(count($_GET["f"]) < 2){
     return ;
  }

  $vars = $_GET["f"];
  shuffle($vars);
  unset($vars[0]);

  $url = $_GET;
  $url["f"] = $vars;
  unset($url["uri"]);
  
  $tags = array();
  $tags_display = array();
  while(list($key, $val) = each($vars)){
     $explode  = explode(":", $val);
     if(isset($explode[1])){
        if($term = taxonomy_term_load($explode[1])){
          $tags[] = $explode[1];
          
          if(isset($url["q"])) unset($url["q"]);
          
          $tags_display[] = l($term -> name, 'suche', array("query" => $url, "attributes" => array("class" => array("btn")))); 
        }
     }
  }

  $nodes_exclude = array();

  $view = views_get_page_view();
  foreach($view -> result as $entity){
     
     if(is_int($entity -> entity)){
        $nodes_exclude[] = $entity -> entity;
     }
     else {
        $nodes_exclude[] = $entity -> entity -> nid;
     } 
  }
  
  

  $view = views_get_view('related_kampagnen');
  $view->get_total_rows = TRUE;
  $view->set_display('attachment_1');
  $view->set_arguments(array(implode(",", $tags), implode("+", $nodes_exclude), $user -> uid));
  $view->pre_execute();
  $view->execute();
  $viewsout = $view->render();;
  
  if(!$viewsout) return ;
  
  $total_items = $view->query->pager->get_total_items(); 
  
  if($total_items == 0) return false;
  
  $theme = array();
  $theme["total_items"] = $total_items;
  $theme["tags_display"] = $tags_display;
  $theme["viewsout"] = $viewsout;
  $theme["url"] = $url;

  return theme('lk_search_other', $theme);
}

