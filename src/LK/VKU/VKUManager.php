<?php

namespace LK\VKU;

class VKUManager {
  
  /**
   * Loads a VKU from the VKUCreator
   * 
   * @param int $id ID of the VKU
   * @param boolean $check_permissions Weather to check permissions
   * @return boolean|\VKUCreator
   */
  public static function getVKU($id, $check_permissions = false){
    
    $vku = new \VKUCreator($id);
    
    if(!$vku ->is()){
      return false;
    }
    
    if($check_permissions && !$vku ->hasAccess()){
      return false;
    }
    
  return $vku;
  }

  /**
   * Gets the count of VKU which relates to the Node
   * 
   * @param int $nid
   * @param array $vku_status
   * @return int
   */
  public static function getNidInVKUCount($nid, $vku_status = []){

    $where_first = array();
    $where_first[] = "n.data_entity_id='". $nid  ."'";
    $where_first[] = "n.data_class='kampagne'";

    if($vku_status){
      $where_first[] = "v.vku_status IN ('". implode("','", $vku_status)  ."')";
    }

    $dbq = db_query("SELECT count(*) as count
      FROM lk_vku_data n, lk_vku v
      WHERE n.vku_id=v.vku_id AND " . implode(" AND ", $where_first));
    $all = $dbq -> fetchObject();

    return $all -> count;
  }

  /**
   * Gets the not final VKU count
   *
   * @param int $uid
   * @return int
   */
  public static function getNotfinalCount($uid){
 
    $dbq = db_query("SELECT count(*) as count FROM lk_vku WHERE uid='". $uid ."' AND vku_status IN ('active', 'ready', 'created', 'downloaded')");
    $result = $dbq -> fetchObject();

    return $result -> count;
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
  
  /**
  * Returns back settings for the current VKU
  * Can be used by PPT and PDF output
  * 
  * @param VKUCreator $vku
  * @return array
  */
  public static function getVKU_RenderSettings(\LK\User $account){
    
    if($account ->isModerator()){
      $verlag = \LK\get_user(LK_TEST_VERLAG_UID);
    }
    
    $verlag = $account ->getVerlagObject();
    
    $array = array(
        'font' => 'lato',
        'logo_position' => 'left',
        'contact_layout' => 'default',
        'hide_size_online' => 'no',
        'vku_hintergrundfarbe' => 'FFFFFF',
        'title_bg_color' => '646464',
        'title_vg_color' => 'FFFFFF',
        'logo_oben' => '',
        'logos_unten' => array()
    );
    
    if(!$verlag instanceof \LK\Verlag){
      return $array;
    }
    
    // Logo top
    $logo_oben = $verlag -> getVerlagSetting("verlag_logo", false, 'uri');
    if($logo_oben){
       $array["logo_oben"] = $logo_oben;
    }
    
    // Logo position
    $array["logo_position"] = $verlag -> getVerlagSetting("verlag_logo_position", 'left', 'value');
    
    // Logos unten
    $logos = array();
    if(isset($verlag->profile['verlag']->field_verlag_marken_logos['und'])){
        foreach($verlag->profile['verlag']->field_verlag_marken_logos['und'] as $logo){
          $logos[] = $logo["uri"];
        }
    }
    
    $array["logos_unten"] = $logos;
   
    // HG-Farbe VKU
    if($color = $verlag -> getVerlagSetting("vku_hintergrundfarbe", false, 'jquery_colorpicker')){
      $array["vku_hintergrundfarbe"] = $color; 
    }
    
    // HG-Farbe Titel
    if($color = $verlag -> getVerlagSetting("vku_hintergrundfarbe_titel", false, 'jquery_colorpicker')){
      $array["title_bg_color"] = $color; 
    }

    // VG-Farbe Titel    
    if($color = $verlag -> getVerlagSetting("vku_vordergrundfarbe_titel", false, 'jquery_colorpicker')){
      $array["title_vg_color"] = $color; 
    }
    
    // Font
    $array["font"] = $verlag -> getVerlagSetting("verlag_font", 'lato', 'value');
    
    // Contact Template
    $array["contact_layout"] = $verlag -> getVerlagSetting("verlag_kontakt_vorlage", 'default', 'value');
    
  return $array;    
  }  
}
