<?php


function lokalkoenig_addkampagne_branchen_auto($tags_typed = '', $limit = 20){
  $voc = taxonomy_vocabulary_machine_name_load('themenbereich');
  $tree = taxonomy_get_tree($voc -> vid);
  
  $tags_typed = arg(1);
  
  $mytree  = array();
  while(list($key, $val) = each($tree)){
    $mytree[$val -> tid] = $val;
  }
  
  $query = db_select('taxonomy_term_data', 't');  
  $query ->fields('t', array('tid', 'name')); //->condition('t.vid', $voc -> vid);
  
  //$tags_typed = drupal_explode_tags($tags_typed);
  $tag_last = $tags_typed;
  $query->condition('t.name', '%' . db_like($tag_last) . '%', 'LIKE');
  $tags_return = $query->execute()->fetchAllKeyed();
  
  $returns = array();
  while(list($key, $val) = each($tags_return)){
    _lokalkoenig_addkampagne_get_line_items($returns, $key, $mytree);
  }  
                        
  $array = array();
  $array["more"] = false;
  $array["results"] = array();
  $results = array(); 
  
  while(list($key, $val) = each($returns)){
    $array["results"][] = array('id' => $key, 'text' => $val);
    $results[$val . " [tid:". $key ."]"] = $val . " [tid:". $key ."]";
  }
 
  drupal_json_output($results); 
  exit();
}


function _lokalkoenig_addkampagne_get_line_items(&$returns, $key, $mytree){
  
  // Wenn der Depth größer 3
  $depth = _lk_term_depth($key);
  
  // AB 3.ten Level wird 
  if($depth >= 3){
     $returns[$mytree[$key] -> tid] = $mytree[$key] -> name;
      
     $line = _lk_get_line($mytree[$key]->parents[0], $mytree);
     $returns[$mytree[$key] -> tid] .= ' (' . $line . ")";
  }
  
  
 

}

function _lk_get_line($key, $mytree){
  
  $return = '';
  
  if($mytree[$key]->parents[0] != 0){
    $return = _lk_get_line($mytree[$key]->parents[0], $mytree);
  }

return $return . '  > ' . $mytree[$key] -> name;
}


function _lk_term_depth($tid) {
  $parent = db_query("SELECT parent FROM {taxonomy_term_hierarchy} WHERE tid = :tid", array(':tid' => $tid))->fetchField();
  if($parent == 0) {
    return 1;
  }else  {
    return 1+_lk_term_depth($parent);
  }
}

?>