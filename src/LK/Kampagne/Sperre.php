<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Kampagne;

/**
 * Description of Sperre
 *
 * @author Maikito
 */
class Sperre {
    
    use \LK\Log\LogTrait;
    
    var $manager = null;
    var $entity = null;
    
    function __construct(SperrenManager $manager, $id = 0) {
        $this->manager = $manager;
        
        if($id){
            $this -> entity = entity_load_single('plz', $id);
            if(!$this -> entity){
                throw new \Exception("Can not find PLZ " . $id);
            }
        }
        else {
             $this -> entity = entity_create('plz', array('type' => "plz"));
        }
    }
    
   /**
    * Sets the Ausgaben, therefor the PLZ
    * 
    * @param array $ausgaben
    */ 
   function setAusgaben($ausgaben){
    
    $plz = array();
    foreach($ausgaben as $id){
        $ausgabe = \LK\get_ausgabe($id);
        $plz_ausgabe = $ausgabe -> getPlz();

        foreach($plz_ausgabe as $plz_id){
            if(!in_array($plz_id, $plz)){
                $plz[] = $plz_id;
            }  
        }
    }
    
    $this->entity->field_plz_sperre['und'] = array();
    foreach($plz as $p){
        $this->entity->field_plz_sperre['und'][] = array('tid' => $p);
    }
    
   }
   
   function setDuration($until_date){
       $this -> entity->field_plz_sperre_bis['und'][0]["value"] = $until_date;
   }
   
   /**
    * Sets the NID
    * 
    * @param int $nid
    */
   function setNid($nid){
        $this->entity->field_medium_node['und'][0]['nid'] = $nid;
   }
   
   function getNid(){
       return $this->entity->field_medium_node['und'][0]['nid'];
   }
   
   /**
    * Sets the UID
    * 
    * @param int $uid
    */
   function setUser($uid){
       $this -> entity -> uid = $uid;
   }
   
   
   /**
    * Gets the PLZ-ID
    * 
    * @return int|boolean
    */
   function getId(){
       if($this -> entity -> id){
           return $this -> entity -> id;
       }
   return false;    
   }
   
   
   
   function remove(){
       $entity_id = $this ->getId();
       $nid = $this ->getNid();
       
       $this->logKampagne('PLZ Sperre ('. $entity_id .') gelöscht', $nid);     
       
       entity_delete('plz', $entity_id);
       db_query("DELETE FROM lk_vku_plz_sperre WHERE plz_sperre_id='". $entity_id ."'"); 
       db_query("DELETE FROM lk_vku_plz_sperre_ausgaben WHERE plz_sperre_id='". $entity_id ."'"); 
       
       $this -> entity = new \stdClass();
       $this -> manager ->rebuildSperren($nid);
   }
   
   
   /**
    * Saves the changes on the Entity
    */
   function saveChanges(){
       $nid = $this ->getNid();
       
       entity_save('plz', $this ->entity);
       $this -> manager -> rebuildAusgabenAccess($nid);
   }
}
