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
      $manager = new \LK\Merkliste\UserMerkliste();
      $terms = $manager ->getTerms();

      if(!$terms){
        return $block;
      }

      $links = [];
      while(list($key, $val) = each($terms)){
        $merkliste = $manager->loadMerkliste($key);
        $links[] = [
          'title' => $merkliste->getName(),
          'id' => $merkliste->getId(),
          'url' => url($merkliste ->getUrl()),
          'kampagnen' => $merkliste->getKampagnenCount(),
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
        $count = _lk_get_recomend_count($user);
        $count_history = _lk_get_history_count($user);
        $countvku = vku_get_active_id();
        $count_ml = \LK\Merkliste\UserMerkliste::getCount();
        
        $block = array(
          'subject' => 'Merkliste',
          'content' => theme('lk_merkliste_block_nav',[
              'count_history' => $count_history,  
              'count' => $count,  
              'count_ml' => $count_ml,
              'countvku' => $countvku,
            ]
          )
        );
      
        break;
  }

  return $block;
}
