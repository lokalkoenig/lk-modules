<?php


/**
 * Removes a Merkliste
 * 
 * @path /merkliste/%/remove
 * @param int $tid
 */

function lokalkoenig_merkliste_ajax_callback(){
global $user;

  $nid = (int)$_POST["nid"];
  $return =  array();
  $return["delete"] = 0;
  $return["nothing"] = 0;
   
  $node = node_load($nid);
  
  if(!$node) {
    // Error No Node
    drupal_json_output(array('error' => 1, 'reason' => 'Node unknown'));
    drupal_exit();
  } 
  
  if(isset($_POST["delete"])){
    if(!isset($node -> merkliste_id)){
       drupal_json_output(array('error' => 1, 'reason' => 'No Merkliste'));
       drupal_exit();
    }
    else {
      entity_delete('merkliste', $node -> merkliste_id);      
    
      $return = array();
      $return["nid"] = $nid;
      $return["error"] = 0;
      $return["total"] = lokalkoenig_merkliste_usercountml($user -> uid);  
    
      drupal_json_output($return); 
      drupal_exit();  
    }
  }
  
  $possible = _get_merklistenterms();
  
  $items = array();
  
  // Existierende mitnehmen
  if(isset($_POST["items"])){
     foreach($_POST["items"] as $item){
       if(isset($possible[$item])) $items[] = $item; 
     }
  }
  
  $new_one = trim($_POST["new"]);
  
  if($new_one){
     $new_tid = 0;
      
     while(list($key, $val) = each($possible)){
         if($new_one == $val) {
            $new_tid = $key;
             
            if(!in_array($key, $items)) {
              $items[] = $new_tid;  
            }
         }
     }
     
      /// Eventuell Neu erstellen
      if(!$new_tid) {
        $term = new stdClass();
        $term->name = $new_one;
        $term->vid = 13; 
        taxonomy_term_save($term);
        
        $log = new \LK\Log\Verlag('Neue Merkliste ' . $new_one . ' angelegt');
        
        $new_tid = $term -> tid;
        $items[] = $new_tid; 
       }
  }
  
  
  if(count($items) == 0){
      if(@$node -> merkliste_id){
          // Delete Entity
          $return["error"] = 0;
          $return["msg"] = 'Die Kampagne wurde von Ihrer Merkliste gelöscht.';
          $return["mlid"] = $entity -> id; 
          $return["delete"] = 1;
          
      }
      else {
          // Do nothing
           $return["error"] = 0;
           $return["msg"] = 'Die Kampagne wurde nicht gespeichert.';
           $return["nothing"] = 1;
      }
  }
  else {
     // wenn Entity existiert 
     if(@$node -> merkliste_id){
        $e = entity_load('merkliste', array($node -> merkliste_id));
        $entity= $e[$node -> merkliste_id];
      
        $entity->field_merkliste_tags['und'] = array();
        foreach($items as $tid){
          $entity->field_merkliste_tags['und'][]["tid"] = $tid;
        }
      
        entity_save('merkliste', $entity);
        $return["error"] = 0;
        $return["msg"] = 'Die Merklisten wurden gespeichert.';
        $return["mlid"] = $entity -> id; 
     }
     else {
         $entity = entity_create('merkliste', array('type' =>'merkliste'));
         $entity->field_merkliste_tags['und'] = array();
            foreach($items as $tid){
              $entity->field_merkliste_tags['und'][]["tid"] = $tid;
            }
        $entity -> uid = $user -> uid;
        //$entity->field_merkliste_aktiv['und'][0]['value'] = 1;
        $entity->field_merkliste_node['und'][0]['nid'] = $node -> nid;
    
        entity_save('merkliste', $entity);
        $return["mlid"] = $entity -> id; 
        $return["msg"] = 'Die Merklisten wurde gespeichert.';
     }
  }
  
  $return["tags"] = implode(',', $items);
  $return["total"] = lokalkoenig_merkliste_usercountml($user -> uid);  
  $return["select"] = theme('lk_merkliste_terms', array("select" => _get_merklistenterms()));  //lokalkoenig_merkliste_usercountml();
  $return["nid"] = $nid;
  $return["error"] = 0;

  drupal_json_output($return); 
  drupal_exit();
}
