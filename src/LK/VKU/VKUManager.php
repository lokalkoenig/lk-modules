<?php

namespace LK\VKU;﻿
use VKUCreator;

class VKUManager {
  
  
  /**
   * Loads a VKU from the VKUCreator
   * 
   * @param int $id ID of the VKU
   * @param boolean $check_permissions Weather to check permissions
   * @return boolean|\VKUCreator
   */
  public static function getVKU($id, $check_permissions = false){
    
    $vku = new VKUCreator($id);
    
    if(!$vku ->is()){
      return false;
    }
    
    if($check_permissions && !$vku ->hasAccess()){
      return false;
    }
    
  return $vku;
  }
  
  /**
   * Gets the Active VKU-ID of the given Account
   * 
   * @param int $uid 
   * @return boolean|int
   */
  public static function getActiveVku($uid){
    
    $dbq = db_query("SELECT vku_id FROM lk_vku WHERE uid='". $uid ."' AND vku_status='active' ORDER BY vku_changed DESC LIMIT 1");
    $record = $dbq->fetchObject();
    
    if($record){
      return $record -> vku_id;
    }
    
    return 0;  
  }
  
}
