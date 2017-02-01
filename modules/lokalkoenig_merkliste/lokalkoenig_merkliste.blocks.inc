<?php

/**
 * HOOK_block_info
 *
 * @return array
 */
function lokalkoenig_merkliste_block_info(){

  $blocks = [];
  $blocks['lk_merkliste_nav'] = [
    'info' => 'Block: LK-Merkliste Navigation',
  ];

  $blocks['lk_history'] = [
    'info' => 'Block: LK-History Info',
  ];
  
  $blocks['lk_merkliste_navigation'] = [
    'info' => 'Block: New ML-Navigation',
  ];

  return $blocks;
}

/**
 * HOOK_block_view
 *
 * @global /stdClass $user
 * @param string $delta
 * @return array
 */
function lokalkoenig_merkliste_block_view($delta = NULL){
global $user; 

  $block = [];
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
          'url' => ($merkliste ->getUrl()),
          'kampagnen' => $merkliste->getKampagnenCount(),
        ];
      }
      $block["content"] = theme('lk_merkliste_navigation', ['links' => $links]); 
      break;

    case 'lk_merkliste_nav':
      $count = _lk_get_recomend_count($user);
      $count_history = \LK\Merkliste\History\UserManager::getInstance(\LK\current())->getCount();
      $countvku = vku_get_active_id();
      $count_ml = \LK\Merkliste\UserMerkliste::getCount();

      $block = [
        'subject' => 'Merkliste',
        'content' => theme('lk_merkliste_block_nav',[
            'count_history' => $count_history,  
            'count' => $count,  
            'count_ml' => $count_ml,
            'countvku' => $countvku,
          ]
        )
      ];
      break;
  }

  return $block;
}
