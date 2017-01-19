<?php


function lokalkoenig_merkliste_block_info(){

 $blocks = array();

  $blocks['lk_merkliste_nav'] = array(
    // info: The name of the block.
    'info' => 'Block: LK-Merkliste Navigation',
    // Block caching options (per role, per user, etc.)
  ); 

  $blocks['lk_history'] = array(
    // info: The name of the block.
    'info' => 'Block: LK-History Info',
    // Block caching options (per role, per user, etc.)
  ); 

  
  $blocks['lk_merkliste_navigation'] = array(
    // info: The name of the block.
    'info' => 'Block: New ML-Navigation',
    // Block caching options (per role, per user, etc.)
  ); 

return $blocks;
}


function lokalkoenig_merkliste_block_view($delta = NULL){
global $user; 

  $block = array();
    switch($delta){
      
      case 'lk_merkliste_navigation':
        $manager = new \LK\Merkliste\Manager();
        $terms = $manager ->getTerms();
        
        $links = [];
        while(list($key, $val) = each($terms)){
          $links[] = [
            'title' => $val -> term_name,
            'id' => $key,
            'url' => url('merkliste/' . $key),
            'kampagnen' => $val->kampagnen,
          ];
        }
        
        $block["content"] = theme('lk_merkliste_navigation', ['links' => $links]); 
      break;
      
      
      case 'lk_history':
         if(arg(0) == 'history'){
          if(_lk_get_history_count($user) > 0){
            $block["content"] = theme('lk_merkliste_history');     
          }
         }
      
        break;  
    
      case 'lk_merkliste_nav':
        $block = array(
          'subject' => 'Merkliste',
          'content' => theme('lk_merkliste_block_nav', 
          array(
            'count_ml' => lokalkoenig_merkliste_usercountml($user -> uid),
            'count_lv' => lokalkoenig_merkliste_usercountml($user -> uid)
          )));
      
        break;
     
        
  }

  return $block;
}
