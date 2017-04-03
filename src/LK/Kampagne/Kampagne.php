<?php
namespace LK\Kampagne;
use LK\Kampagne\Manager\Access as KampagnenAccess;

/**
 * Description of Kampagne
 * Loads the Kampagne and gives Theme and Access-Information about
 * @author Maikito
 */
class Kampagne {
  use \LK\Stats\Action;
  use \LK\Log\LogTrait;

  var $version = 1;
  var $node = null;


  /**
   * Constructor
   * Gets called by node_load and attaches information to the basic-Node
   *
   * @param \stdClass $node
   */
  function __construct(\stdClass $node) {
    $this -> node = &$node;
    $node -> online = $node -> status;
   
    if($node -> type !== 'kampagne'){
      $this->logError('Kampagne NID/' . $node -> nid . " is not a Kampagne");
    }

    if(!isset($this -> node -> loadedmedias)){
      $this -> initializeMedias();

      foreach (module_implements('kampagne_load') as $module) {
        $function = $module . '_kampagne_load';
        $function($node);
      }
    }
  }

  /**
   * Gets the SID of the Kampagne
   *
   * @return string
   */
  function getSID(){
    return $this -> node -> sid;
  }

  /**
   * Gets the Node-ID
   *
   * @return int
   */
  function getNid(){
    return $this -> node -> nid;
  }

  /**
   * Gets the Node
   *
   * @return \stdClass
   */
  function getNode(){
    return $this -> node;
  }
    
  /**
   * User has Access
   * 
   * @param int $uid
   * @return boolean
   */
  function canPurchase(){
    
    if(!$this -> node -> status){
      return false;
    }
   
    return $this -> node -> plzaccess;    
  }

  /**
   * Initialize Kampagne by loading Medias
   */
  private function initializeMedias(){

    $node = $this->getNode();
    $node -> loadedmedias = true;
    $node -> lkstatus = @$node -> field_kamp_status["und"][0]["value"];
    $node -> plzaccess = KampagnenAccess::has($this);
    $node -> kid = $node -> sid = @$node -> field_sid["und"][0]["value"];

    $medien = [];

    $result = db_query('SELECT field_medium_node_nid as nid, entity_id, entity_type '
        . 'FROM {field_data_field_medium_node} '
        . "WHERE entity_type='medium' AND field_medium_node_nid = :nid", array(':nid' => $this -> node -> nid));
    foreach ($result as $record) {
        $medien[] = entity_load_single($record -> entity_type, $record -> entity_id);
    }

    $medien_print = array();
    $medien_online = array();

    foreach($medien as $media){
      $test = \_lk_get_medientyp_print_or_online($media->field_medium_typ['und'][0]['tid']);
      $media -> media_type = $test;

      if($test == 'print'){
          $medien_print[] = $media;
      }
      else {
          $medien_online[] = $media;
      }
    }

    foreach ($medien_online as $medium){
      $medien_print[] = $medium;
    }

    $node -> medien = $medien_print;
  }
   
  /**
   * Removes a Kampagne and its relations
   */
  function remove(){
    if(isset($this -> node -> medien)){
        // Medien
      foreach($this -> node -> medien as $entity){
        entity_delete('medium', $entity -> id);
      }
    }

    // remove PLZ-Sperren        
    $manager = new \LK\Kampagne\SperrenManager();  
    $result = db_query('SELECT field_medium_node_nid as nid, entity_id, entity_type FROM {field_data_field_medium_node} WHERE field_medium_node_nid =:nid', array(':nids' => $this -> node -> nid));
    foreach ($result as $record) {
       if($record -> entity_type === "plz"){
         $manager ->removeSperre($record -> entity_id);
       }
    }
  }   

  /**
   * Get the Lizenzen
   *
   * @return Integer
   */
  function getLizenzenCount(){
    $dbq = db_query("SELECT count(*) as count FROM lk_vku_lizenzen WHERE nid='". $this -> node -> nid ."'");
    $result = $dbq -> fetchObject();
    return $result -> count;
  }
}
