<?php
namespace LK\VKU\Data;

/**
 * Description of VKU
 *
 * @author Maikito
 */
class VKU {
  use \LK\Stats\Action;
  use \LK\Log\LogTrait;

  var $data = [];
  var $LOG_CATEGORY = 'VKU';

  /**
   * Updates the current VKU
   *
   * @return int
   */
  function update(){
    $this -> set("vku_changed", time());
    return $this -> get("vku_changed");
  }
  
  function set($key, $value = NULL){
    return $this -> setValue($key, $value);
  }

  /**
   * Gets a Setting
   *
   * @param string $key
   * @return mixed
   */
  function getValue($key){
     return $this->data[$key];
  }

  /**
   * Sets a Value
   *
   * @param type $key
   * @param type $value
   */
  function setValue($key, $value){

  }

  /**
   * Gets the current state
   *
   * @return string
   */
  function getStatus(){
    return $this -> get("vku_status");
 }

 /**
  * Gets the Author-ID
  *
  * @return int
  */
 function getAuthor(){
  return $this -> get("uid");
 }

 /**
  * Gets the Object of the Author
  *
  * @return \LK\User
  */
 function getAuthorObject(){
   return \LK\get_user($this->getAuthor());
 }

 /**
   * Logs an Event inside an VKU
   *
   * @param String $type
   * @param String $message
   *
   * @return String Message

   */
  function logEvent($type, $message){
     $log = new \LK\Log\Debug("[". $type ."] " . $message);
     $log ->setVku($this)->save();
  return $message;
  }

  function logVerlagEvent($message, $context = []){
    $log = new \LK\Log\Verlag($message);
    $log ->setVku($this)->save();

    return $message;
  }




}
