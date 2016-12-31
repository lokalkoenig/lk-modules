<?php


function get_nid_in_vku_count($nid, $vku_status = array(), $where = array()){

  $where_first = array();
  
  //$where = array();
  $where_first[] = "n.data_entity_id='". $nid  ."'";
  $where_first[] = "n.data_class='kampagne'";
  
  if($vku_status){
    $where_first[] = "v.vku_status IN ('". implode("','", $vku_status)  ."')";
  }
  
  if($where) {
    foreach($where as $item){
      $where_first[] = $item;
    }
  }


   $dbq = db_query("SELECT 
        count(*) as count
        FROM lk_vku_data n, lk_vku v 
        WHERE 
            n.vku_id=v.vku_id 
           
            AND " . implode(" AND ", $where_first));
  $all = $dbq -> fetchObject();
  
  return $all -> count;
}  

 function vku_node_calculate_size($node){
     $size = 0;
     
     if(!isset($node -> medien)){
        return $size;
     }
     
     foreach($node -> medien as $med){
       $size += $med->field_medium_source['und'][0]['filesize'];
     } 
      
  return $size; 
  }
  

  function vku_active_user_notfinal_count($uid){
    //created ready downloaded
    $dbq = db_query("SELECT count(*) as count FROM lk_vku WHERE uid='". $uid ."' AND vku_status IN ('active', 'ready', 'created', 'downloaded')");
    $result = $dbq -> fetchObject();
    
  return $result -> count;  
  }

   function vku_active_user_count($uid){
    
    $dbq = db_query("SELECT count(*) as count FROM lk_vku WHERE uid='". $uid ."' AND vku_status IN ('ready', 'created')");
    $result = $dbq -> fetchObject();
    
  return $result -> count;  
  }


  function _vku_node_access(){
    return true;
  } 



function lokalkoenig_merkliste_ajax_callback_vku($tid){
global $user;

  $term = taxonomy_term_load($tid);
  $title = 'IHR ANGEBOT';
  
  $post_title = trim($_POST["title"]);
  if($post_title){
    $title = $post_title; 
  }
  
  
  if(!$term){
    drupal_goto(MERKLISTE_URI);
  }
  
  $view = views_get_view_result('merkliste3', 'page', $term -> tid);
  $nids = array();
  
  foreach($view as $res){
    $nid = $res ->field_field_merkliste_node[0]['raw']['nid'];
    $nids[$nid] = $nid;
  }
   
   $can_nids = array();
   while(list($key, $val) = each($nids)){
   
      $check = na_check_user_has_access($user -> uid, $val);
      $node = node_load($val);
      
      if($check["access"]){
          $can_nids[] = $val;
      }
      else {
        drupal_set_message('Die Kampagne "'. $node -> title .'" ist im Moment für Sie nicht verfügbar und wurde deswegen der Verkaufsunterlage nicht hinzugefügt.', 'error');  
      }
   }   
    
   if(count($can_nids) == 0){
      drupal_goto(MERKLISTE_URI . "/" . $term -> tid);
      drupal_exit();
   }  
    
   $vku = new VKUCreator('new', array("vku_title" => $title, 'vku_company' => $term -> name, 'vku_generic' => 0));
   $x = 0;
   foreach($can_nids as $nid){
      $vku -> addKampagne($nid);  
   }
   
   // Erstelle Node-VKU
   drupal_set_message('Eine neue Verkaufsunterlage wurde erstellt.');
   drupal_goto($vku -> vku_url());
}


?>