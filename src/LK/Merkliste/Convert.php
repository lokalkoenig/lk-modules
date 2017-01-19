<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Merkliste;

/**
 * Description of Convert
 *
 * @author Maikito
 */
class Convert {
  //put your code here
  
  function letsdoit(){
   
    return ;
    
    $save = [];
    $dbq = db_query('SELECT * FROM lk_merklisten ORDER BY created ASC');
    while($all = $dbq -> fetchObject()){
      $term = taxonomy_term_load($all -> tid);
      $sid = $term->tid . "-" . $all->uid;
      
      if(!isset($save[$sid])){
          $save[$sid]['uid'] = $all->uid;
          $save[$sid]['term_name'] = $term->name;
          $save[$sid]['kampagnen'] = 1;
          $save[$sid]['created'] = $all -> created;
          $save[$sid]['changed'] = $all -> created;
      }
      else {
          $save[$sid]['changed'] = $all -> created;
          $save[$sid]['kampagnen']++;
      }
    }
    
    while(list($key, $val) = each($save)){
      $id = db_insert('lk_merklisten_terms')->fields($val)->execute();
      $explode = explode('-', $key);
      db_query("UPDATE lk_merklisten SET term_id='". $id ."' WHERE tid='". $explode[0] ."'");
    }
  }
  
  
  function letsdoit2(){
    
    
    $dbq = db_query('SELECT id FROM eck_merkliste');
    while($all = $dbq -> fetchObject()){
      $ml = entity_load_single('merkliste', $all->id);
      
      foreach($ml->field_merkliste_tags['und'] as $tid){
         $insert = [
            'uid' => $ml->uid,
            'tid' => $tid['tid'],
            'created' => $ml->created,
            'nid' => $ml->field_merkliste_node['und'][0]['nid']
          ];
         
          db_insert('lk_merklisten')->fields($insert)->execute();
      }
    }
  }
}
