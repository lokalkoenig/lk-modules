<?php

/*
 * What is an Action
 * Actions are per User, per Day and 
 * 
 * - view_kampagne
 * - create_vku
 * - add_kampagne
 * - 
 * CREATE TABLE `lk_actions` (
  `id` int(11) NOT NULL,
  `action` varchar(255) COLLATE utf8_bin NOT NULL,
  `action_id` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL,
  `action_date` date NOT NULL,
  `verlag_id` int(11) NOT NULL DEFAULT '0',
  `team_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin; 
 */


namespace LK\Stats;

/**
 * Description of Action
 *
 * 
 * @author Maikito
 */
trait Action {
 
  /**
   * Adds an Action
   * 
   * @param type $action
   * @param type $id
   */
  protected function setAction($action, $id = 0){
    
    $uid = 0;
    $vid = 0;
    $tid = 0;
    
    $current = \LK\current();
    if($current){
      $uid = $current ->getUid();
      $vid = $current ->getVerlag();
      $tid = $current ->getTeam();
    }
    
    \db_merge('lk_actions')
    ->key(array('uid' => $uid, 'action' => $action, 'action_id' => $id, 'action_date' => date("Y-m-d")))
    ->fields(array(
      'verlag_uid' => $vid,
      'team_id' => $tid  
    ))
    ->execute();
  }
}
