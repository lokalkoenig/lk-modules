<?php

/**
 * Callback for creating a VKU
 * 
 * @path /merkliste/% & /merkliste
 * @param int $tid
 */
function lokalkoenig_merkliste_show_entries($tid = 0){
  
  drupal_set_title('Merkliste');  
  $manager = new \LK\Merkliste\UserMerkliste();
  if(!$tid){
    $kampagnen = $manager ->getUserKampagnen();
    $output = [];
    
    $x = 0;
    foreach ($kampagnen as $nid){
      $output[] = \LK\UI\Kampagne\Teaser::get($nid);
    
      $x++;
      if($x === 10){
        break;
      }
    }
    
    return theme('lk_merkliste_actions', [
        'merkliste' => false,
        'nodes' => implode('', $output),
        'kampagnen' => count($kampagnen),
    ]);
  }
  
  $merkliste = $manager ->loadMerkliste($tid);
  
  if(!$merkliste){
    drupal_goto($manager->getUrl());
  }
  
  $output = [];
  $kampagnen = $merkliste ->getKampagnen();
  
  foreach ($kampagnen as $nid){
    $output[] = \LK\UI\Kampagne\Teaser::get($nid);
  }
  
  lk_set_subtitle($merkliste ->getName());
  return theme('lk_merkliste_actions', [
      'name' => $merkliste ->getName(), 
      'merkliste' => $merkliste, 
      'nodes' => implode('', $output), 
      'merkliste_id' => $merkliste ->getId()
  ]);
}