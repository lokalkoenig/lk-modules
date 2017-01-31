<?php

/**
 * Ausgaben-Wrapper 
 * @version 1.0
 * @since 2015-11-21 
 */

namespace LK;

class Ausgabe {
    
  var $data = null;

  function __construct($id) {
      $ausgabe = entity_load_single('ausgabe', $id);
      if(!$ausgabe){
          return false;
      }

      $this -> data = $ausgabe;
      return $this;
  }

  function getData(){
      return $this -> data;
  }

  function getId(){
      return $this -> data -> id;
  }

  function getTitle(){

      $title = $this -> data -> field_ortsbezeichnung['und'][0]['value'] . " [" . $this -> getShortTitle() ."]";
      return $title;
  }

  function getCity(){
      return $this -> data -> field_ortsbezeichnung['und'][0]['value'];
  }

  function getUserCount(){
      $id = $this ->getId();
      $dbq = db_query("SELECT count(*) as count FROM field_data_field_ausgabe WHERE deleted='0' AND bundle='mitarbeiter' AND field_ausgabe_target_id='". $id."'");
      $all = $dbq -> fetchObject();

  return $all -> count;
  }

  function getTitleFormatted(){
       $short = $this -> getShortTitle();
       $city = $this -> getCity();

      return '<small class="label label-primary" title="'. $city .'">'  . $short . '</small> ';
  }


  function getPlz(){

      $plz = array();
      foreach($this -> data -> field_plz_sperre["und"] as $item){
          $plz[] = $item["tid"];
      }

      return $plz;
  }

  /**
   * Gets back the formatted PLZ
   * 
   * @return string
   */
  function getPlzFormatted(){
    return \plz_simplyfy($this -> data -> field_plz_sperre["und"]);
  }

  /**
   * Gets back the Short-Title
   *
   * @return string
   */
  function getShortTitle(){
    return $this -> data ->field_kurzbezeichnung['und'][0]['value'];
  }

  /**
   * Gets back the UID
   *
   * @return int
   */
  function getVerlag(){
    return (int)$this -> data ->field_verlag['und'][0]['uid'];
  }

  /**
   * Gets back a Verlag Object
   *
   * @return \LK\Verlag
   */
  function getVerlagObject(){
    $id = $this ->getVerlag();
    return \LK\get_user($id);
  }
}
